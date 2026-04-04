import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';
import 'package:pharmvrpro/core/config/network_constants.dart';
import 'package:pharmvrpro/features/dashboard/data/repositories/home_repository.dart';
import 'package:pharmvrpro/features/education/data/repositories/education_repository.dart';
import 'package:pharmvrpro/features/news/data/repositories/news_repository.dart';
import 'package:pharmvrpro/features/auth/data/data_sources/auth_data_source.dart';
import 'package:pharmvrpro/core/network/dio_provider.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'dart:io';

void main() {
  late Dio dio;
  late HomeRepository homeRepository;
  late EducationRepository educationRepository;
  late NewsRepository newsRepository;
  late AuthDataSource authDataSource;

  // Use a fixed test user
  const testEmail = 'test_e2e_content@example.com';
  const testPassword = 'password123';
  const testName = 'Content Tester';

  setUpAll(() async {
    HttpOverrides.global = null;
    
    // Setup Dio for local machine execution (not emulator)
    dio = Dio(
      BaseOptions(
        baseUrl: 'http://127.0.0.1:8000/api/v1',
        connectTimeout: NetworkConstants.connectionTimeout,
        receiveTimeout: NetworkConstants.receiveTimeout,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );
    
    homeRepository = HomeRepository(dio);
    educationRepository = EducationRepository(dio);
    newsRepository = NewsRepository(dio);
    authDataSource = AuthDataSource(dio);

    print('\n--- E2E CONTENT TESTING SETUP ---');
    print('Base URL: ${dio.options.baseUrl}');

    try {
      // 1. Try to Login first
      print('Attempting login for $testEmail...');
      final response = await authDataSource.login(testEmail, testPassword);
      final token = response['data']['token'];
      print('Login successful. Token: ${token.substring(0, 10)}...');
      
      // Inject token manually for tests
      dio.options.headers['Authorization'] = 'Bearer $token';
    } catch (e) {
      print('Login failed, attempting registration...');
      try {
        final regResponse = await authDataSource.register(
          email: testEmail,
          password: testPassword,
          passwordConfirmation: testPassword,
          name: testName,
        );
        final token = regResponse['data']['token'];
        print('Registration successful. Token: ${token.substring(0, 10)}...');
        dio.options.headers['Authorization'] = 'Bearer $token';
      } catch (e2) {
        print('Setup failed: $e2');
        rethrow;
      }
    }
  });

  group('Home Hub E2E', () {
    test('Fetch Home Data returns valid structure', () async {
      print('\nTesting Home Data...');
      final homeData = await homeRepository.getHomeData();
      
      expect(homeData.userGreeting, isNotEmpty);
      expect(homeData.userGreeting['full_name'], equals(testName));
      expect(homeData.vrStatusHeader, isNotNull);
      expect(homeData.progressSummary, isNotEmpty);
      expect(homeData.progressSummary.containsKey('progress_percentage'), isTrue);
      
      print('Home Data Success: Greeting = ${homeData.userGreeting['full_name']}');
      print('VR Status: ${homeData.vrStatusHeader.connectionStatus}');
    });
  });

  group('Edukasi E2E', () {
    test('Fetch all modules returns a list', () async {
      print('\nTesting Edukasi List (All)...');
      final modules = await educationRepository.getModules();
      
      expect(modules, isA<List>());
      print('Retrieved ${modules.length} learning modules.');
      
      if (modules.isNotEmpty) {
        final first = modules.first;
        expect(first.title, isNotEmpty);
        expect(first.slug, isNotEmpty);
        print('First Module: ${first.title} (${first.type})');
      }
    });

    test('Filter modules by type works', () async {
      print('\nTesting Edukasi Filter (video)...');
      final videos = await educationRepository.getModules(type: 'video');
      
      for (var v in videos) {
        expect(v.type, equals('video'));
      }
      print('Filtered Video Count: ${videos.length}');
    });

    test('Fetch module detail works', () async {
      final modules = await educationRepository.getModules();
      if (modules.isEmpty) {
        print('Skipping detail test: No modules found.');
        return;
      }

      final slug = modules.first.slug;
      print('\nTesting Edukasi Detail for slug: $slug...');
      final detail = await educationRepository.getModuleDetail(slug);
      
      expect(detail.module.slug, equals(slug));
      expect(detail.relatedContent, isNotNull);
      print('Detail Success: ${detail.module.title}');
    });
  });

  group('News E2E', () {
    test('Fetch news list returns valid articles', () async {
      print('\nTesting News List...');
      final news = await newsRepository.getNews();
      
      expect(news, isA<List>());
      print('Retrieved ${news.length} news articles.');
      
      if (news.isNotEmpty) {
        final first = news.first;
        expect(first.title, isNotEmpty);
        expect(first.publishedAt, isNotNull);
        print('First News: ${first.title} by ${first.author}');
      }
    });

    test('Fetch news detail works', () async {
      final news = await newsRepository.getNews();
      if (news.isEmpty) {
        print('Skipping news detail test: No news found.');
        return;
      }

      final slug = news.first.slug;
      print('\nTesting News Detail for slug: $slug...');
      final detail = await newsRepository.getNewsDetail(slug);
      
      expect(detail.article.slug, equals(slug));
      expect(detail.relatedNews, isNotNull);
      print('News Detail Success: ${detail.article.title}');
    });
  });
}
