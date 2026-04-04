<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\Assessment;
use App\Enums\AssessmentType;
use App\Enums\AssessmentStatus;
use App\Http\Requests\Admin\UpdateAssessmentRequest;
use App\Http\Requests\Admin\StoreAssessmentRequest;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of assessments by module.
     */
    public function index()
    {
        $modules = TrainingModule::with('assessments')->get();
        return view('admin.assessments.index', compact('modules'));
    }

    /**
     * Display the specified module assessments.
     */
    public function show(TrainingModule $module)
    {
        $module->load(['assessments', 'questionBankItems.options']);
        
        $pretest = $module->assessments()->where('type', AssessmentType::PRETEST)->first();
        $posttest = $module->assessments()->where('type', AssessmentType::POSTTEST)->first();

        return view('admin.assessments.module_detail', compact('module', 'pretest', 'posttest'));
    }

    /**
     * Update the assessment settings.
     */
    public function update(UpdateAssessmentRequest $request, Assessment $assessment)
    {
        $assessment->update($request->validated());

        return redirect()->back()->with('success', 'Assessment configuration synchronized.');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(Assessment $assessment)
    {
        $newStatus = $assessment->status === AssessmentStatus::ACTIVE 
            ? AssessmentStatus::INACTIVE 
            : AssessmentStatus::ACTIVE;

        // Note: Validation exists in FormRequest if updating via form, 
        // but for a simple toggle, we might want to check again here or use a dedicated request.
        if ($newStatus === AssessmentStatus::ACTIVE) {
            $count = $assessment->trainingModule->questionBankItems()
                ->where('is_active', true)
                ->where(function($q) use ($assessment) {
                    $q->where('usage_scope', $assessment->type->value)
                      ->orWhere('usage_scope', 'both');
                })
                ->count();

            if ($count < $assessment->number_of_questions_to_take) {
                return redirect()->back()->with('warning', "Insufficient questions ({$count}) to activate. Required: {$assessment->number_of_questions_to_take}");
            }
        }

        $assessment->status = $newStatus;
        $assessment->save();

        return redirect()->back()->with('success', "Assessment marked as {$newStatus->value}.");
    }

    /**
     * Initialize missing assessments for all modules.
     */
    public function initializeAll()
    {
        $modules = TrainingModule::all();
        $count = 0;

        foreach ($modules as $module) {
            foreach (AssessmentType::cases() as $type) {
                $exists = Assessment::where('module_id', $module->id)
                    ->where('type', $type)
                    ->exists();

                if (!$exists) {
                    Assessment::create([
                        'module_id' => $module->id,
                        'type' => $type,
                        'title' => ucfirst($type->value) . ' Assessment - ' . $module->title,
                        'status' => AssessmentStatus::INACTIVE,
                        'number_of_questions_to_take' => 10,
                        'passing_score' => 70,
                        'randomize_questions' => true,
                        'randomize_options' => true,
                    ]);
                    $count++;
                }
            }
        }

        return back()->with('success', "Protocol synchronization complete. $count new nodes deployed.");
    }
}
