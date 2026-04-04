/// Assessment type: pre-test (before VR) or post-test (after VR).
enum AssessmentType { 
  pre_test, 
  post_test;

  static AssessmentType fromString(String value) {
    return AssessmentType.values.firstWhere(
      (e) => e.name == value,
      orElse: () => AssessmentType.pre_test,
    );
  }
}

/// Metadata for an assessment linked to a VR training module.
class Assessment {
  final int id;
  final String type;
  final String title;
  final String description;
  final int totalQuestions;
  final int durationMinutes;
  final String estimatedDuration;
  final int passingScore;
  final String moduleTitle;
  final String moduleSlug;
  final AssessmentAttemptInfo attemptInfo;
  final bool isEligible;
  final String? eligibilityMessage;
  final bool canStart;
  final String recommendedAction;

  const Assessment({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.totalQuestions,
    required this.durationMinutes,
    required this.estimatedDuration,
    required this.passingScore,
    required this.moduleTitle,
    required this.moduleSlug,
    required this.attemptInfo,
    required this.isEligible,
    this.eligibilityMessage,
    required this.canStart,
    required this.recommendedAction,
  });

  int get durationSeconds => durationMinutes * 60;
  int get timeLimitSeconds => durationSeconds;

  factory Assessment.fromJson(Map<String, dynamic> json) {
    final moduleSummary = json['module_summary'] as Map<String, dynamic>? ?? {};
    final rawDuration = json['estimated_duration'] as String? ?? '0 menit';
    final durMin = int.tryParse(rawDuration.split(' ').first) ?? 0;

    return Assessment(
      id: json['id'] as int,
      type: json['type'] as String,
      title: json['title'] as String,
      description: json['description'] as String? ?? '',
      totalQuestions: json['total_questions'] as int? ?? 0,
      durationMinutes: durMin,
      estimatedDuration: rawDuration,
      passingScore: json['passing_score'] as int? ?? 70,
      moduleTitle: moduleSummary['title'] as String? ?? '',
      moduleSlug: moduleSummary['slug'] as String? ?? '',
      attemptInfo: AssessmentAttemptInfo.fromJson(json['attempt_info'] as Map<String, dynamic>? ?? {}),
      isEligible: json['is_eligible'] as bool? ?? false,
      eligibilityMessage: json['eligibility_message'] as String?,
      canStart: json['can_start'] as bool? ?? false,
      recommendedAction: json['recommended_action'] as String? ?? '',
    );
  }
}

class AssessmentAttemptInfo {
  final bool hasPreviousAttempt;
  final int? latestScore;
  final int highestScore;
  final String status;
  final bool hasPassed;
  final bool vrCompleted;

  const AssessmentAttemptInfo({
    required this.hasPreviousAttempt,
    this.latestScore,
    required this.highestScore,
    required this.status,
    required this.hasPassed,
    required this.vrCompleted,
  });

  factory AssessmentAttemptInfo.fromJson(Map<String, dynamic> json) {
    return AssessmentAttemptInfo(
      hasPreviousAttempt: json['has_previous_attempt'] as bool? ?? false,
      latestScore: json['latest_score'] as int?,
      highestScore: json['highest_score'] as int? ?? 0,
      status: json['status'] as String? ?? 'not_started',
      hasPassed: json['has_passed'] as bool? ?? false,
      vrCompleted: json['vr_completed'] as bool? ?? false,
    );
  }
}

/// A single question with multiple-choice options.
class Question {
  final int id;
  final int questionNumber;
  final String questionText;
  final String? imageUrl;
  final String? explanation;
  final int? selectedOptionId;
  final List<AssessmentOption> options;

  const Question({
    required this.id,
    required this.questionNumber,
    required this.questionText,
    this.imageUrl,
    this.explanation,
    this.selectedOptionId,
    required this.options,
  });

  factory Question.fromJson(Map<String, dynamic> json) {
    var optionsList = <AssessmentOption>[];
    if (json['options'] != null) {
      optionsList = (json['options'] as List)
          .map((i) => AssessmentOption.fromJson(i as Map<String, dynamic>))
          .toList();
    }
    return Question(
      id: json['id'] as int,
      questionNumber: json['question_number'] as int? ?? 0,
      questionText: json['question_text'] as String? ?? '',
      imageUrl: json['image_url'] as String?,
      explanation: json['explanation'] as String?,
      selectedOptionId: json['selected_option_id'] as int?,
      options: optionsList,
    );
  }
}

class AssessmentOption {
  final int id;
  final String label;
  final String text;
  final bool? isCorrect; // Only populated in review/result

  const AssessmentOption({
    required this.id,
    required this.label,
    required this.text,
    this.isCorrect,
  });

  factory AssessmentOption.fromJson(Map<String, dynamic> json) {
    return AssessmentOption(
      id: json['id'] as int,
      label: json['option_label'] as String? ?? '',
      text: json['option_text'] as String? ?? '',
      isCorrect: json['is_correct'] as bool?,
    );
  }
}

/// Result of a completed assessment.
class AssessmentResult {
  final int attemptId;
  final int assessmentId;
  final String assessmentType;
  final int score;
  final String percentage;
  final bool passed;
  final String status;
  final int attemptNumber;
  final DateTime? completedAt;
  final int totalQuestions;
  final int correctCount;
  final int incorrectCount;
  final String recommendationAction;
  final String journeyRelevance;
  final int timeTakenSeconds;

  const AssessmentResult({
    required this.attemptId,
    required this.assessmentId,
    required this.assessmentType,
    required this.score,
    required this.percentage,
    required this.passed,
    required this.status,
    required this.attemptNumber,
    this.completedAt,
    required this.totalQuestions,
    required this.correctCount,
    required this.incorrectCount,
    required this.recommendationAction,
    required this.journeyRelevance,
    this.timeTakenSeconds = 0,
  });

  String get type => assessmentType;
  int get correctAnswers => correctCount;

  factory AssessmentResult.fromJson(Map<String, dynamic> json) {
    final summary = json['summary'] as Map<String, dynamic>? ?? {};
    final recommendation = json['recommendation'] as Map<String, dynamic>? ?? {};
    
    return AssessmentResult(
      attemptId: json['id'] as int,
      assessmentId: json['assessment_id'] as int,
      assessmentType: json['assessment_type'] as String? ?? '',
      score: json['score'] as int? ?? 0,
      percentage: json['percentage'] as String? ?? '0%',
      passed: json['passed'] as bool? ?? false,
      status: json['status'] as String? ?? '',
      attemptNumber: json['attempt_number'] as int? ?? 1,
      completedAt: json['completed_at'] != null 
          ? DateTime.parse(json['completed_at'] as String) 
          : null,
      totalQuestions: summary['total_questions'] as int? ?? 0,
      correctCount: summary['correct_count'] as int? ?? 0,
      incorrectCount: summary['incorrect_count'] as int? ?? 0,
      recommendationAction: recommendation['action'] as String? ?? '',
      journeyRelevance: json['journey_relevance'] as String? ?? '',
      timeTakenSeconds: summary['time_taken_seconds'] as int? ?? 0,
    );
  }
}
/// Launch readiness for a VR module.
class LaunchReadiness {
  final String moduleTitle;
  final String moduleSlug;
  final bool preTestCompleted;
  final bool preTestPassed;
  final bool quest3Paired;
  final bool quest3Connected;
  final bool eligibleToLaunch;
  final List<LaunchChecklistItem> checklist;
  final List<String> blockingReasons;
  final String recommendedAction;
  final String recommendedRoute;

  const LaunchReadiness({
    required this.moduleTitle,
    required this.moduleSlug,
    required this.preTestCompleted,
    required this.preTestPassed,
    required this.quest3Paired,
    required this.quest3Connected,
    required this.eligibleToLaunch,
    required this.checklist,
    required this.blockingReasons,
    required this.recommendedAction,
    required this.recommendedRoute,
  });

  factory LaunchReadiness.fromJson(Map<String, dynamic> json) {
    final module = json['module'] as Map<String, dynamic>? ?? {};
    final checklistRaw = json['checklist'] as List? ?? [];
    
    return LaunchReadiness(
      moduleTitle: module['title'] as String? ?? '',
      moduleSlug: module['slug'] as String? ?? '',
      preTestCompleted: json['pre_test_completed'] as bool? ?? false,
      preTestPassed: json['pre_test_passed'] as bool? ?? false,
      quest3Paired: json['quest3_paired'] as bool? ?? false,
      quest3Connected: json['quest3_connected'] as bool? ?? false,
      eligibleToLaunch: json['eligible_to_launch'] as bool? ?? false,
      checklist: checklistRaw.map((e) => LaunchChecklistItem.fromJson(e as Map<String, dynamic>)).toList(),
      blockingReasons: List<String>.from(json['blocking_reasons'] ?? []),
      recommendedAction: json['recommended_next_action'] as String? ?? '',
      recommendedRoute: json['recommended_next_route'] as String? ?? '',
    );
  }
}

class LaunchChecklistItem {
  final String label;
  final bool status;

  const LaunchChecklistItem({required this.label, required this.status});

  factory LaunchChecklistItem.fromJson(Map<String, dynamic> json) {
    return LaunchChecklistItem(
      label: json['label'] as String? ?? '',
      status: json['status'] as bool? ?? false,
    );
  }
}
