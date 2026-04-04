import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/assessment_repository.dart';
import '../../domain/models/assessment_models.dart';

/// State for a live assessment session.
class AssessmentState {
  final Assessment? assessment;
  final int? attemptId;
  final List<Question> questions;
  final int currentQuestionIndex;
  final Map<int, int> selectedAnswers; // questionId → optionId
  final int elapsedSeconds;
  final bool isLoading;
  final String? error;
  final bool isSubmitted;
  final AssessmentResult? result;

  const AssessmentState({
    this.assessment,
    this.attemptId,
    this.questions = const [],
    this.currentQuestionIndex = 0,
    this.selectedAnswers = const {},
    this.elapsedSeconds = 0,
    this.isLoading = false,
    this.error,
    this.isSubmitted = false,
    this.result,
  });

  int get totalQuestions => questions.length;
  int get answeredCount => selectedAnswers.length;
  bool get allAnswered => answeredCount == totalQuestions;
  bool get isLastQuestion => currentQuestionIndex == totalQuestions - 1;
  bool get isFirstQuestion => currentQuestionIndex == 0;
  Question? get currentQuestion => questions.isNotEmpty ? questions[currentQuestionIndex] : null;
  int? get currentSelectedOption => currentQuestion != null ? selectedAnswers[currentQuestion!.id] : null;
  double get progress => totalQuestions > 0 ? (currentQuestionIndex + 1) / totalQuestions : 0;

  AssessmentState copyWith({
    Assessment? assessment,
    int? attemptId,
    List<Question>? questions,
    int? currentQuestionIndex,
    Map<int, int>? selectedAnswers,
    int? elapsedSeconds,
    bool? isLoading,
    String? error,
    bool? isSubmitted,
    AssessmentResult? result,
  }) {
    return AssessmentState(
      assessment: assessment ?? this.assessment,
      attemptId: attemptId ?? this.attemptId,
      questions: questions ?? this.questions,
      currentQuestionIndex: currentQuestionIndex ?? this.currentQuestionIndex,
      selectedAnswers: selectedAnswers ?? this.selectedAnswers,
      elapsedSeconds: elapsedSeconds ?? this.elapsedSeconds,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      isSubmitted: isSubmitted ?? this.isSubmitted,
      result: result ?? this.result,
    );
  }
}

/// Manages a live assessment session.
class AssessmentNotifier extends Notifier<AssessmentState> {
  late final AssessmentRepository _repository;

  @override
  AssessmentState build() {
    _repository = ref.watch(assessmentRepositoryProvider);
    return const AssessmentState();
  }

  /// Start a real assessment session
  Future<void> startSession(Assessment assessment) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final attemptData = await _repository.startAttempt(assessment.id);
      final attemptId = attemptData['id'] as int;
      final questions = await _repository.getQuestions(attemptId);
      
      state = state.copyWith(
        assessment: assessment,
        attemptId: attemptId,
        questions: questions,
        isLoading: false,
        isSubmitted: false,
        currentQuestionIndex: 0,
        selectedAnswers: {},
        elapsedSeconds: 0,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  /// Select an answer for the current question.
  void selectAnswer(int optionId) {
    if (state.isSubmitted || state.currentQuestion == null) return;
    final updated = Map<int, int>.from(state.selectedAnswers);
    updated[state.currentQuestion!.id] = optionId;
    state = state.copyWith(selectedAnswers: updated);
  }

  /// Navigate to the next question.
  void nextQuestion() {
    if (state.isLastQuestion || state.isSubmitted) return;
    state = state.copyWith(currentQuestionIndex: state.currentQuestionIndex + 1);
  }

  /// Navigate to the previous question.
  void previousQuestion() {
    if (state.isFirstQuestion || state.isSubmitted) return;
    state = state.copyWith(currentQuestionIndex: state.currentQuestionIndex - 1);
  }

  /// Jump to a specific question index.
  void goToQuestion(int index) {
    if (index < 0 || index >= state.totalQuestions || state.isSubmitted) return;
    state = state.copyWith(currentQuestionIndex: index);
  }

  /// Submit and grade the assessment.
  Future<void> submitAssessment() async {
    if (state.isSubmitted || state.attemptId == null) return;
    
    state = state.copyWith(isLoading: true, error: null);
    try {
      final result = await _repository.submitAttempt(state.attemptId!, state.selectedAnswers);
      state = state.copyWith(
        isSubmitted: true, 
        result: result,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      // Re-throw to let the UI handle it if needed
      rethrow;
    }
  }

  /// Load results for a specific attempt (recovery)
  Future<void> loadResult(int attemptId) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final result = await _repository.getResults(attemptId);
      state = state.copyWith(
        result: result,
        isLoading: false,
        isSubmitted: true,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  /// Update elapsed time (called by timer).
  void tick() {
    if (state.isSubmitted) return;
    state = state.copyWith(elapsedSeconds: state.elapsedSeconds + 1);
  }
}

final assessmentProvider = NotifierProvider<AssessmentNotifier, AssessmentState>(() {
  return AssessmentNotifier();
});

/// Future provider to fetch assessment metadata for the intro screen
final assessmentIntroProvider = FutureProvider.family<Assessment, ({String moduleSlug, String type})>((ref, arg) {
  final repo = ref.watch(assessmentRepositoryProvider);
  return repo.getAssessmentIntro(arg.moduleSlug, arg.type);
});

/// Future provider to fetch VR launch readiness
final launchReadinessProvider = FutureProvider.family<LaunchReadiness, String>((ref, moduleSlug) {
  final repo = ref.watch(assessmentRepositoryProvider);
  return repo.getLaunchReadiness(moduleSlug);
});
