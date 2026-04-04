import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';
import 'package:pharmvrpro/features/auth/data/data_sources/auth_data_source.dart';
import 'package:pharmvrpro/features/profile/data/data_sources/profile_data_source.dart';

void main() {
  final dio = Dio(
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

  final authDataSource = AuthDataSource(dio);
  final profileDataSource = ProfileDataSource(dio);

  final testEmail = 'tester_${DateTime.now().millisecondsSinceEpoch}@example.com';
  final testPassword = 'Password123!';
  final testName = 'E2E Tester';
  String? token;

  group('Auth & Profile E2E Integration', () {
    test('1. Register new user', () async {
      try {
        final response = await authDataSource.register(
          name: testName,
          email: testEmail,
          password: testPassword,
          passwordConfirmation: testPassword,
        );
        expect(response['data'], isNotNull);
        token = response['data']['token'];
        dio.options.headers['Authorization'] = 'Bearer $token';
      } catch (e) {
        print('Test 1 Failed: $e');
        if (e is DioException) print('Response: ${e.response?.data}');
        rethrow;
      }
    });

    test('2. Login with valid credentials', () async {
      try {
        final response = await authDataSource.login(testEmail, testPassword);
        expect(response['data']['token'], isNotNull);
      } catch (e) {
        print('Test 2 Failed: $e');
        rethrow;
      }
    });

    test('3. Login with invalid credentials', () async {
      try {
        await authDataSource.login(testEmail, 'WrongPassword');
        fail('Should have failed');
      } on DioException catch (e) {
        expect(e.response?.statusCode, 422);
      }
    });

    test('4. Forgot password request', () async {
      try {
        await authDataSource.forgotPassword(testEmail);
      } catch (e) {
        print('Test 4 Failed: $e');
        rethrow;
      }
    });

    test('5. Session restore via auth/me', () async {
      try {
        final response = await authDataSource.getMe();
        expect(response['data']['email'], testEmail);
      } catch (e) {
        print('Test 4 Failed: $e');
        rethrow;
      }
    });

    test('6. View profile', () async {
      try {
        final response = await profileDataSource.getProfile();
        expect(response['data']['email'], testEmail);
      } catch (e) {
        print('Test 5 Failed: $e');
        rethrow;
      }
    });

    test('7. Update profile', () async {
      try {
        final updatedName = 'Updated Tester';
        final response = await profileDataSource.updateProfile({
          'full_name': updatedName,
          'email': testEmail,
        });
        expect(response['data']['name'], updatedName);
      } on DioException catch (e) {
        print('Test 6 Failed: ${e.response?.statusCode} - ${e.response?.data}');
        rethrow;
      }
    });

    test('8. Change password', () async {
      try {
        await profileDataSource.changePassword(
          currentPassword: testPassword,
          newPassword: 'NewPassword123!',
          newPasswordConfirmation: 'NewPassword123!',
        );
        // Verify login with new password
        final response = await authDataSource.login(testEmail, 'NewPassword123!');
        expect(response['data']['token'], isNotNull);
      } on DioException catch (e) {
        print('Test 7 Failed: ${e.response?.statusCode} - ${e.response?.data}');
        rethrow;
      }
    });

    test('9. Logout', () async {
      try {
        await authDataSource.logout();
        try {
          await authDataSource.getMe();
          fail('Should have been unauthorized');
        } on DioException catch (e) {
          expect(e.response?.statusCode, 401);
        }
      } catch (e) {
        print('Test 8 Failed: $e');
        rethrow;
      }
    });
  });
}
