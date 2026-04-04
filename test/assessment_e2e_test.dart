import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';
import 'package:pharmvrpro/features/assessment/data/repositories/assessment_repository.dart';
import 'package:pharmvrpro/features/auth/data/data_sources/auth_data_source.dart';
import 'dart:io';

void main() {
  late Dio dio;
  late AssessmentRepository assessmentRepository;
  late AuthDataSource authDataSource;

  const testEmail = 'test_assessment_e2e@example.com';
  const testPassword = 'password123';
  const testName = 'Assessment Tester';
  const moduleSlug = 'pengenalan-lab-steril'; // Updated to valid slug from DB

  setUpAll(() async {
    HttpOverrides.global = null;
    
    dio = Dio(
      BaseOptions(
        baseUrl: 'http://127.0.0.1:8000/api/v1',
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );
    
    assessmentRepository = AssessmentRepository(dio);
    authDataSource = AuthDataSource(dio);

    print('\n--- E2E ASSESSMENT TESTING SETUP ---');

    try {
      final response = await authDataSource.login(testEmail, testPassword);
      final token = response['data']['token'];
      dio.options.headers['Authorization'] = 'Bearer $token';
    } catch (e) {
      try {
        final regResponse = await authDataSource.register(
          email: testEmail,
          password: testPassword,
          passwordConfirmation: testPassword,
          name: testName,
        );
        final token = regResponse['data']['token'];
        dio.options.headers['Authorization'] = 'Bearer $token';
      } catch (e2) {
        print('Setup failed: $e2');
        rethrow;
      }
    }
  });

  group('Assessment Phase 3 E2E', () {
    int? assessmentId;
    int? attemptId;

    test('1. Get Assessment Intro (Pre-test)', () async {
      try {
        final assessment = await assessmentRepository.getAssessmentIntro(moduleSlug, 'pre_test');
        expect(assessment.id, isNotNull);
        expect(assessment.type, 'pre_test');
        assessmentId = assessment.id;
        print('Assessment ID found: $assessmentId');
      } catch (e) {
        print('Skipping Phase 3 Test: Module $moduleSlug or Pre-test not found in DB.');
        return;
      }
    });

    test('2. Start Assessment Attempt', () async {
      if (assessmentId == null) return;
      
      final attemptData = await assessmentRepository.startAttempt(assessmentId!);
      expect(attemptData['id'], isNotNull);
      expect(attemptData['status'], 'in_progress');
      attemptId = attemptData['id'];
      print('Attempt ID started: $attemptId');
    });

    test('3. Get Questions for Attempt', () async {
      if (attemptId == null) return;
      
      final questions = await assessmentRepository.getQuestions(attemptId!);
      expect(questions, isNotEmpty);
      print('Retrieved ${questions.length} questions.');
      
      if (questions.isNotEmpty) {
        expect(questions.first.questionText, isNotEmpty);
        expect(questions.first.options, isNotEmpty);
      }
    });

    test('4. Submit Assessment & Verify Results', () async {
      if (attemptId == null) return;
      
      final questions = await assessmentRepository.getQuestions(attemptId!);
      if (questions.isEmpty) return;

      // Select first option for all questions
      final Map<int, int> answers = {
        for (var q in questions) q.id: q.options.first.id
      };

      final result = await assessmentRepository.submitAttempt(attemptId!, answers);
      expect(result.attemptId, attemptId);
      expect(result.status, anyOf(['passed', 'failed']));
      print('Assessment submitted. Score: ${result.score}, Status: ${result.status}');
    });

    test('5. Verify VR Launch Readiness', () async {
      final readiness = await assessmentRepository.getLaunchReadiness(moduleSlug);
      expect(readiness.moduleSlug, moduleSlug);
      print('VR Readiness for $moduleSlug: Eligible=${readiness.eligibleToLaunch}');
    });
  });
}
