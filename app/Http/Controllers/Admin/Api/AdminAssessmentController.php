<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Services\AdminAuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminAssessmentController extends Controller
{
    private const TYPES = ['pretest', 'posttest'];
    private const STATUSES = ['active', 'inactive'];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $assessments = Assessment::query()
            ->with('trainingModule.scenes')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('trainingModule', fn ($moduleQuery) => $moduleQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['module_id'] ?? null, fn ($query, int $moduleId) => $query->where('module_id', $moduleId))
            ->when($filters['scene_id'] ?? null, function ($query, int $sceneId) {
                $moduleId = Scene::whereKey($sceneId)->value('training_module_id');
                $query->where('module_id', $moduleId);
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->orderBy('title')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Assessments retrieved.',
            'data' => $assessments->getCollection()
                ->map(fn (Assessment $assessment) => $this->assessmentResource($assessment))
                ->values(),
            'meta' => [
                'current_page' => $assessments->currentPage(),
                'per_page' => $assessments->perPage(),
                'total' => $assessments->total(),
                'last_page' => $assessments->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(Assessment $assessment): JsonResponse
    {
        $assessment->load('trainingModule.scenes');

        return response()->json([
            'success' => true,
            'message' => 'Assessment retrieved.',
            'data' => $this->assessmentResource($assessment, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, Assessment $assessment): JsonResponse
    {
        if (!$this->canWrite($request)) {
            return $this->forbidden('Only super_admin and admin users can update assessments.');
        }

        $assessment->loadMissing('trainingModule:id,title,slug');
        $before = $this->auditSnapshot($assessment);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', Rule::in(self::TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'passing_score' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'number_of_questions_to_take' => ['sometimes', 'integer', 'min:1', 'max:500'],
            'time_limit_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
            'module_id' => ['sometimes', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['sometimes', 'nullable', 'integer', 'exists:scenes,id'],
        ]);

        $validator->after(function ($validator) use ($assessment, $request) {
            $moduleId = (int) ($request->input('module_id') ?? $assessment->module_id);
            $type = (string) ($request->input('type') ?? $assessment->type->value);

            if (
                Assessment::where('module_id', $moduleId)
                    ->where('type', $type)
                    ->whereKeyNot($assessment->id)
                    ->exists()
            ) {
                $validator->errors()->add('type', 'An assessment with this module and type already exists.');
            }
        });

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('scene_id', $data) && $data['scene_id'] !== null) {
            $data['module_id'] = Scene::whereKey($data['scene_id'])->value('training_module_id');
        }

        foreach (['title', 'description', 'type', 'status', 'passing_score', 'number_of_questions_to_take', 'time_limit_minutes', 'module_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $assessment->{$field} = $data[$field];
            }
        }

        $assessment->save();
        $assessment->refresh()->load('trainingModule.scenes');

        app(AdminAuditLogService::class)->record(
            $request,
            $request->user(),
            'assessment.updated',
            'assessment',
            $assessment->id,
            $assessment->title,
            $before,
            $this->auditSnapshot($assessment)
        );

        return response()->json([
            'success' => true,
            'message' => 'Assessment updated.',
            'data' => $this->assessmentResource($assessment, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function assessmentResource(Assessment $assessment, bool $includeDetail = false): array
    {
        $module = $assessment->trainingModule;
        $scene = $module?->relationLoaded('scenes')
            ? $module->scenes->sortBy('order_index')->first()
            : $module?->scenes()->first();
        $type = $assessment->type instanceof AssessmentType ? $assessment->type->value : (string) $assessment->type;
        $status = $assessment->status instanceof AssessmentStatus ? $assessment->status->value : (string) $assessment->status;
        $questionCount = QuestionBankItem::query()
            ->where('module_id', $assessment->module_id)
            ->where(function ($query) use ($type) {
                $query->where('usage_scope', $type)
                    ->orWhere('usage_scope', 'both');
            })
            ->count();

        $resource = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'description' => $assessment->description,
            'type' => $type,
            'module_id' => $assessment->module_id,
            'module_title' => $module?->title,
            'module_slug' => $module?->slug,
            'scene_id' => $scene?->id,
            'scene_slug' => $scene?->slug,
            'passing_score' => $assessment->passing_score,
            'question_count' => $questionCount,
            'number_of_questions_to_take' => $assessment->number_of_questions_to_take,
            'time_limit_minutes' => $assessment->time_limit_minutes,
            'status' => $status,
            'randomize_questions' => (bool) $assessment->randomize_questions,
            'randomize_options' => (bool) $assessment->randomize_options,
            'created_at' => $assessment->created_at?->toISOString(),
            'updated_at' => $assessment->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['questions'] = QuestionBankItem::query()
                ->with('options')
                ->where('module_id', $assessment->module_id)
                ->where(function ($query) use ($type) {
                    $query->where('usage_scope', $type)
                        ->orWhere('usage_scope', 'both');
                })
                ->orderBy('id')
                ->limit(50)
                ->get()
                ->map(fn (QuestionBankItem $question) => [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'usage_scope' => $question->usage_scope?->value ?? $question->usage_scope,
                    'difficulty' => $question->difficulty,
                    'status' => $question->is_active ? 'active' : 'inactive',
                ])
                ->values();
        }

        return $resource;
    }

    private function canWrite(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
    }

    private function auditSnapshot(Assessment $assessment): array
    {
        $type = $assessment->type instanceof AssessmentType ? $assessment->type->value : (string) $assessment->type;
        $status = $assessment->status instanceof AssessmentStatus ? $assessment->status->value : (string) $assessment->status;

        return [
            'title' => $assessment->title,
            'description' => $assessment->description,
            'type' => $type,
            'status' => $status,
            'passing_score' => $assessment->passing_score,
            'number_of_questions_to_take' => $assessment->number_of_questions_to_take,
            'time_limit_minutes' => $assessment->time_limit_minutes,
            'module_id' => $assessment->module_id,
            'module_slug' => $assessment->trainingModule?->slug,
        ];
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'errors' => null,
        ], 403);
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
