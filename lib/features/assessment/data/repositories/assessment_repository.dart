import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../domain/models/assessment_models.dart';

class AssessmentRepository {
  final Dio _dio;

  AssessmentRepository(this._dio);

  /// Get assessment intro/metadata
  Future<Assessment> getAssessmentIntro(String moduleSlug, String type) async {
    final response = await _dio.get('/assessments/$moduleSlug/$type');
    return Assessment.fromJson(response.data['data']);
  }

  /// Start a new assessment attempt
  Future<Map<String, dynamic>> startAttempt(int assessmentId) async {
    final response = await _dio.post('/assessments/$assessmentId/start');
    return response.data['data'];
  }

  /// Get questions for an attempt
  Future<List<Question>> getQuestions(int attemptId) async {
    final response = await _dio.get('/assessment-attempts/$attemptId');
    final List questionsJson = response.data['data']['questions'];
    return questionsJson.map((json) => Question.fromJson(json)).toList();
  }

  /// Submit assessment results
  Future<AssessmentResult> submitAttempt(int attemptId, Map<int, int> answers) async {
    // Backend expects questionId -> optionId mapping
    final formattedAnswers = answers.entries.map((e) => {
      'question_id': e.key,
      'option_id': e.value,
    }).toList();

    final response = await _dio.post(
      '/assessment-attempts/$attemptId/submit',
      data: {'answers': formattedAnswers},
    );
    return AssessmentResult.fromJson(response.data['data']);
  }

  /// Get results for a previous attempt
  Future<AssessmentResult> getResults(int attemptId) async {
    final response = await _dio.get('/assessment-attempts/$attemptId/result');
    return AssessmentResult.fromJson(response.data['data']);
  }

  /// Get VR launch readiness for a module
  Future<LaunchReadiness> getLaunchReadiness(String moduleSlug) async {
    final response = await _dio.get('/vr/modules/$moduleSlug/launch-readiness');
    return LaunchReadiness.fromJson(response.data['data']);
  }
}

final assessmentRepositoryProvider = Provider<AssessmentRepository>((ref) {
  return AssessmentRepository(ref.watch(dioProvider));
});
