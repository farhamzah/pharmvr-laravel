<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Http\Requests\Admin\StoreQuestionBankItemRequest;
use App\Http\Requests\Admin\UpdateQuestionBankItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    /**
     * List questions by module.
     */
    public function index(TrainingModule $module)
    {
        $questions = $module->questionBankItems()->with('options')->get();
        return view('admin.assessments.questions_index', compact('module', 'questions'));
    }

    /**
     * Store a new question.
     */
    public function store(StoreQuestionBankItemRequest $request, TrainingModule $module)
    {
        return DB::transaction(function () use ($request, $module) {
            $question = $module->questionBankItems()->create([
                'question_text' => $request->question_text,
                'usage_scope' => $request->usage_scope,
                'difficulty' => $request->difficulty,
                'explanation' => $request->explanation,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => Auth::id(),
            ]);

            foreach ($request->options as $key => $text) {
                $question->options()->create([
                    'option_key' => $key,
                    'option_text' => $text,
                    'is_correct' => $request->correct_option === $key,
                    'sort_order' => ord($key) - 64, // A=1, B=2...
                ]);
            }

            return redirect()->back()->with('success', 'Neural asset encoded successfully.');
        });
    }

    /**
     * Update question.
     */
    public function update(UpdateQuestionBankItemRequest $request, TrainingModule $module, QuestionBankItem $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $question->update($request->only(['question_text', 'usage_scope', 'difficulty', 'explanation', 'is_active']));

            if ($request->has('options')) {
                $question->options()->delete();
                foreach ($request->options as $key => $text) {
                    $question->options()->create([
                        'option_key' => $key,
                        'option_text' => $text,
                        'is_correct' => $request->correct_option === $key,
                        'sort_order' => ord($key) - 64,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Neural asset recalibrated.');
        });
    }

    /**
     * Delete question.
     */
    public function destroy(TrainingModule $module, QuestionBankItem $question)
    {
        $question->delete();
        return redirect()->back()->with('success', 'Neural asset purged.');
    }
}
