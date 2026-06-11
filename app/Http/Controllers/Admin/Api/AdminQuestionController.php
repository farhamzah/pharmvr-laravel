<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\QuestionUsageScope;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminQuestionController extends Controller
{
    private const SCOPES = ['pretest', 'posttest', 'both'];
    private const STATUSES = ['active', 'inactive'];
    private const QUESTION_TYPES = ['multiple_choice'];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'type' => ['nullable', Rule::in(['pretest', 'posttest', 'both', 'multiple_choice'])],
            'difficulty' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        $assessment = isset($filters['assessment_id']) ? Assessment::find($filters['assessment_id']) : null;

        $questions = QuestionBankItem::query()
            ->with(['options', 'trainingModule.scenes'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('question_text', 'like', "%{$search}%")
                        ->orWhere('explanation', 'like', "%{$search}%")
                        ->orWhereHas('trainingModule', fn ($moduleQuery) => $moduleQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->when($assessment, function ($query, Assessment $assessment) {
                $query->where('module_id', $assessment->module_id)
                    ->where(function ($nested) use ($assessment) {
                        $nested->where('usage_scope', $assessment->type->value)
                            ->orWhere('usage_scope', QuestionUsageScope::BOTH->value);
                    });
            })
            ->when($filters['module_id'] ?? null, fn ($query, int $moduleId) => $query->where('module_id', $moduleId))
            ->when($filters['scene_id'] ?? null, function ($query, int $sceneId) {
                $moduleId = Scene::whereKey($sceneId)->value('training_module_id');
                $query->where('module_id', $moduleId);
            })
            ->when(($filters['type'] ?? null) && ($filters['type'] !== 'multiple_choice'), fn ($query, string $type) => $query->where('usage_scope', $type))
            ->when($filters['difficulty'] ?? null, fn ($query, string $difficulty) => $query->where('difficulty', $difficulty))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Questions retrieved.',
            'data' => $questions->getCollection()
                ->map(fn (QuestionBankItem $question) => $this->questionResource($question, $assessment))
                ->values(),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->canWrite($request)) {
            return $this->forbidden('Only super_admin and admin users can create questions.');
        }

        $validator = $this->questionValidator($request->all(), true);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $assessment = Assessment::findOrFail($data['assessment_id']);

        $question = DB::transaction(function () use ($data, $assessment, $request) {
            $question = QuestionBankItem::create([
                'module_id' => $assessment->module_id,
                'question_text' => $data['question_text'],
                'usage_scope' => $data['usage_scope'] ?? $assessment->type->value,
                'difficulty' => $data['difficulty'] ?? null,
                'explanation' => $data['explanation'] ?? null,
                'is_active' => ($data['status'] ?? 'active') === 'active',
                'created_by' => $request->user()?->id,
            ]);

            $this->replaceOptions($question, $data['options'], $data['correct_answer']);

            return $question->load(['options', 'trainingModule.scenes']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Question created.',
            'data' => $this->questionResource($question, $assessment, true),
            'meta' => (object) [],
            'errors' => null,
        ], 201);
    }

    public function show(QuestionBankItem $question): JsonResponse
    {
        $question->load(['options', 'trainingModule.scenes']);

        return response()->json([
            'success' => true,
            'message' => 'Question retrieved.',
            'data' => $this->questionResource($question, null, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, QuestionBankItem $question): JsonResponse
    {
        if (!$this->canWrite($request)) {
            return $this->forbidden('Only super_admin and admin users can update questions.');
        }

        $validator = $this->questionValidator($request->all(), false);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $question) {
            if (isset($data['assessment_id'])) {
                $assessment = Assessment::findOrFail($data['assessment_id']);
                $question->module_id = $assessment->module_id;
                $question->usage_scope = $data['usage_scope'] ?? $assessment->type->value;
            }

            foreach (['question_text', 'usage_scope', 'difficulty', 'explanation'] as $field) {
                if (array_key_exists($field, $data)) {
                    $question->{$field} = $data[$field];
                }
            }

            if (array_key_exists('status', $data)) {
                $question->is_active = $data['status'] === 'active';
            }

            $question->save();

            if (array_key_exists('options', $data)) {
                $this->replaceOptions($question, $data['options'], $data['correct_answer'] ?? $this->correctAnswerFor($question));
            }
        });

        $question->refresh()->load(['options', 'trainingModule.scenes']);

        return response()->json([
            'success' => true,
            'message' => 'Question updated.',
            'data' => $this->questionResource($question, null, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function questionValidator(array $payload, bool $isCreate)
    {
        $rules = [
            'assessment_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:assessments,id'],
            'question_text' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'type' => ['sometimes', Rule::in(self::QUESTION_TYPES)],
            'usage_scope' => ['sometimes', Rule::in(self::SCOPES)],
            'difficulty' => ['sometimes', 'nullable', 'string', 'max:80'],
            'explanation' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'options' => [$isCreate ? 'required' : 'sometimes', 'array', 'min:2', 'max:6'],
            'options.*.key' => ['required_with:options', 'string', 'max:8'],
            'options.*.text' => ['required_with:options', 'string'],
            'correct_answer' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:8'],
            'topic' => ['sometimes', 'nullable', 'string', 'max:120'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];

        $validator = Validator::make($payload, $rules);

        $validator->after(function ($validator) use ($payload, $isCreate) {
            if (isset($payload['options'])) {
                $keys = collect($payload['options'])->pluck('key')->filter()->map(fn ($key) => (string) $key)->values();

                if ($keys->count() !== $keys->unique()->count()) {
                    $validator->errors()->add('options', 'Option keys must be unique.');
                }

                if (($payload['correct_answer'] ?? null) && !$keys->contains((string) $payload['correct_answer'])) {
                    $validator->errors()->add('correct_answer', 'Correct answer must match one of the option keys.');
                }
            } elseif ($isCreate) {
                $validator->errors()->add('options', 'Options are required.');
            }
        });

        return $validator;
    }

    private function replaceOptions(QuestionBankItem $question, array $options, string $correctAnswer): void
    {
        $question->options()->delete();

        foreach (array_values($options) as $index => $option) {
            QuestionBankOption::create([
                'question_bank_item_id' => $question->id,
                'option_key' => (string) $option['key'],
                'option_text' => (string) $option['text'],
                'is_correct' => (string) $option['key'] === $correctAnswer,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function questionResource(QuestionBankItem $question, ?Assessment $assessment = null, bool $includeDetail = false): array
    {
        $module = $question->trainingModule;
        $scene = $module?->relationLoaded('scenes')
            ? $module->scenes->sortBy('order_index')->first()
            : $module?->scenes()->first();
        $assessment ??= $this->matchingAssessment($question);
        $usageScope = $question->usage_scope instanceof QuestionUsageScope ? $question->usage_scope->value : (string) $question->usage_scope;
        $options = $question->options->sortBy('sort_order')->values();

        $resource = [
            'id' => $question->id,
            'assessment_id' => $assessment?->id,
            'assessment_title' => $assessment?->title,
            'module_id' => $question->module_id,
            'module_title' => $module?->title,
            'module_slug' => $module?->slug,
            'scene_id' => $scene?->id,
            'scene_slug' => $scene?->slug,
            'question_text' => $question->question_text,
            'type' => 'multiple_choice',
            'usage_scope' => $usageScope,
            'options' => $options->map(fn (QuestionBankOption $option) => [
                'key' => $option->option_key,
                'text' => $option->option_text,
            ])->values(),
            'correct_answer' => $this->correctAnswerFor($question),
            'explanation' => $question->explanation,
            'difficulty' => $question->difficulty,
            'status' => $question->is_active ? 'active' : 'inactive',
            'topic' => null,
            'tag' => null,
            'created_at' => $question->created_at?->toISOString(),
            'updated_at' => $question->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['model_notes'] = [
                'assessment_id' => 'Derived from module_id and usage_scope; question_bank_items do not store assessment_id directly.',
                'topic' => 'Not stored in current question_bank_items schema.',
                'tag' => 'Not stored in current question_bank_items schema.',
            ];
        }

        return $resource;
    }

    private function matchingAssessment(QuestionBankItem $question): ?Assessment
    {
        $scope = $question->usage_scope instanceof QuestionUsageScope ? $question->usage_scope->value : (string) $question->usage_scope;

        return Assessment::query()
            ->where('module_id', $question->module_id)
            ->when($scope !== QuestionUsageScope::BOTH->value, fn ($query) => $query->where('type', $scope))
            ->orderBy('id')
            ->first();
    }

    private function correctAnswerFor(QuestionBankItem $question): ?string
    {
        $correct = $question->relationLoaded('options')
            ? $question->options->firstWhere('is_correct', true)
            : $question->options()->where('is_correct', true)->first();

        return $correct?->option_key;
    }

    private function canWrite(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
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
