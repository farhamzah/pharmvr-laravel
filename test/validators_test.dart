import 'package:flutter_test/flutter_test.dart';
import 'package:pharmvrpro/core/utils/validators.dart';

void main() {
  group('ValidatorBuilder Tests', () {
    test('validateEmail should return error for empty string', () {
      expect(ValidatorBuilder.validateEmail(''), 'Email is required');
      expect(ValidatorBuilder.validateEmail(null), 'Email is required');
    });

    test('validateEmail should return error for invalid format', () {
      expect(ValidatorBuilder.validateEmail('invalid-email'), 'Please enter a valid email address');
      expect(ValidatorBuilder.validateEmail('test@test'), 'Please enter a valid email address');
    });

    test('validateEmail should return null for valid email', () {
      expect(ValidatorBuilder.validateEmail('test@example.com'), null);
      expect(ValidatorBuilder.validateEmail('john.doe@company.org'), null);
    });

    test('validatePassword should enforce minimum length constraints', () {
      expect(ValidatorBuilder.validatePassword(''), 'Password is required');
      expect(ValidatorBuilder.validatePassword('1234567'), 'Password must be at least 8 characters long');
      expect(ValidatorBuilder.validatePassword('12345678'), null);
    });

    test('validateRequired should work generally', () {
      expect(ValidatorBuilder.validateRequired('', 'Name'), 'Name is required');
      expect(ValidatorBuilder.validateRequired('John', 'Name'), null);
    });
  });
}
