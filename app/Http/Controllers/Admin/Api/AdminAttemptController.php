<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\QuestionBankOption;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminAttemptController extends Controller
{
    private const STATUSES = ['in_progress', 'completed', 'failed', 'abandoned'];
    private const TYPES = ['pretest', 'posttest'];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'passed' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        $query = $this->attemptQuery($filters)
            ->orderByDesc('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id');

        $attempts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Attempts retrieved.',
            'data' => $attempts->getCollection()
                ->map(fn (AssessmentAttempt $attempt) => $this->attemptSummary($attempt))
                ->values(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
                'last_page' => $attempts->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(AssessmentAttempt $attempt): JsonResponse
    {
        $attempt->load([
            'user:id,name,email',
            'assessment.trainingModule.scenes',
            'answers.question.options',
            'answers.option',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attempt retrieved.',
            'data' => $this->attemptDetail($attempt),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $baseQuery = $this->attemptQuery($filters);
        $totalAttempts = (clone $baseQuery)->count();
        $completedQuery = (clone $baseQuery)->where(function ($query) {
            $query->whereNotNull('completed_at')
                ->orWhereIn('status', ['completed', 'failed']);
        });
        $completedAttempts = (clone $completedQuery)->count();
        $passedAttempts = (clone $completedQuery)->where('passed', true)->count();
        $failedAttempts = (clone $completedQuery)->where(function ($query) {
            $query->where('status', 'failed')
                ->orWhere('passed', false);
        })->count();

        return response()->json([
            'success' => true,
            'message' => 'Scores summary retrieved.',
            'data' => [
                'total_attempts' => $totalAttempts,
                'completed_attempts' => $completedAttempts,
                'passed_attempts' => $passedAttempts,
                'failed_attempts' => $failedAttempts,
                'average_score' => $this->averageScore($baseQuery),
                'average_pretest_score' => $this->averageScore($this->typedAttemptQuery($filters, AssessmentType::PRETEST->value)),
                'average_posttest_score' => $this->averageScore($this->typedAttemptQuery($filters, AssessmentType::POSTTEST->value)),
                'completion_rate' => $this->percentage($completedAttempts, $totalAttempts),
                'pass_rate' => $this->percentage($passedAttempts, $completedAttempts),
            ],
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function attemptQuery(array $filters = []): Builder
    {
        return AssessmentAttempt::query()
            ->with(['user:id,name,email', 'assessment.trainingModule.scenes'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->whereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhereHas('trainingModule', fn (Builder $moduleQuery) => $moduleQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")));
                });
            })
            ->when($filters['user_id'] ?? null, fn (Builder $query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['assessment_id'] ?? null, fn (Builder $query, int $assessmentId) => $query->where('assessment_id', $assessmentId))
            ->when($filters['module_id'] ?? null, function (Builder $query, int $moduleId) {
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('module_id', $moduleId));
            })
            ->when($filters['scene_id'] ?? null, function (Builder $query, int $sceneId) {
                $moduleId = Scene::whereKey($sceneId)->value('training_module_id');
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('module_id', $moduleId));
            })
            ->when($filters['type'] ?? null, function (Builder $query, string $type) {
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('type', $type));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when(array_key_exists('passed', $filters), function (Builder $query) use ($filters) {
                $query->where('passed', in_array((string) $filters['passed'], ['true', '1'], true));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('started_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('started_at', '<=', $date));
    }

    private function typedAttemptQuery(array $filters, string $type): Builder
    {
        $filters['type'] = $type;

        return $this->attemptQuery($filters);
    }

    private function attemptSummary(AssessmentAttempt $attempt): array
    {
        $assessment = $attempt->assessment;
        $module = $assessment?->trainingModule;
        $scene = $this->sceneForAttempt($attempt);

        return [
            'id' => $attempt->id,
            'user_id' => $attempt->user_id,
            'user_name' => $attempt->user?->name,
            'user_email' => $attempt->user?->email,
            'assessment_id' => $attempt->assessment_id,
            'assessment_title' => $assessment?->title,
            'assessment_type' => $this->assessmentType($attempt),
            'module_id' => $module?->id,
            'module_title' => $module?->title,
            'module_slug' => $module?->slug,
            'scene_id' => $scene?->id,
            'scene_slug' => $scene?->slug,
            'scene_title' => $scene?->title,
            'score' => $attempt->score,
            'passing_score' => $assessment?->passing_score,
            'passed' => (bool) $attempt->passed,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at?->toISOString(),
            'completed_at' => $attempt->completed_at?->toISOString(),
        ];
    }

    private function attemptDetail(AssessmentAttempt $attempt): array
    {
        $assessment = $attempt->assessment;
        $module = $assessment?->trainingModule;
        $scene = $this->sceneForAttempt($attempt);

        return [
            'id' => $attempt->id,
            'user' => $attempt->user ? [
                'id' => $attempt->user->id,
                'name' => $attempt->user->name,
                'email' => $attempt->user->email,
            ] : null,
            'assessment' => $assessment ? [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'type' => $this->assessmentType($attempt),
                'passing_score' => $assessment->passing_score,
            ] : null,
            'module' => $module ? [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
            ] : null,
            'scene' => $scene ? [
                'id' => $scene->id,
                'title' => $scene->title,
                'slug' => $scene->slug,
            ] : null,
            'score' => $attempt->score,
            'passing_score' => $assessment?->passing_score,
            'passed' => (bool) $attempt->passed,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at?->toISOString(),
            'completed_at' => $attempt->completed_at?->toISOString(),
            'answers' => $attempt->answers
                ->map(fn ($answer) => $this->answerResource($answer))
                ->values(),
        ];
    }

    private function answerResource($answer): array
    {
        $question = $answer->question;
        $selectedOption = $answer->option;
        $correctOption = $question?->relationLoaded('options')
            ? $question->options->firstWhere('is_correct', true)
            : ($question ? $question->options()->where('is_correct', true)->first() : null);

        return [
            'question_id' => $answer->question_id,
            'question_text' => $question?->question_text,
            'selected_answer' => $selectedOption?->option_key,
            'selected_answer_text' => $selectedOption?->option_text,
            'correct_answer' => $correctOption?->option_key,
            'correct_answer_text' => $correctOption?->option_text,
            'is_correct' => $selectedOption instanceof QuestionBankOption
                && $correctOption instanceof QuestionBankOption
                && $selectedOption->id === $correctOption->id,
            'score' => ($selectedOption instanceof QuestionBankOption
                && $correctOption instanceof QuestionBankOption
                && $selectedOption->id === $correctOption->id) ? 1 : 0,
            'explanation' => $question?->explanation,
        ];
    }

    private function sceneForAttempt(AssessmentAttempt $attempt): ?Scene
    {
        $module = $attempt->assessment?->trainingModule;

        if (!$module) {
            return null;
        }

        return $module->relationLoaded('scenes')
            ? $module->scenes->sortBy('order_index')->first()
            : $module->scenes()->first();
    }

    private function assessmentType(AssessmentAttempt $attempt): ?string
    {
        $type = $attempt->assessment?->type;

        return $type instanceof AssessmentType ? $type->value : ($type ? (string) $type : null);
    }

    private function averageScore(Builder $query): float
    {
        return round((float) ((clone $query)->whereNotNull('score')->avg('score') ?? 0), 1);
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'data' => null,
            'meta' => null,
            'errors' => $errors,
        ], 422);
    }
}
