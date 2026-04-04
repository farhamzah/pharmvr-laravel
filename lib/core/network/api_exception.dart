import 'package:dio/dio.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic originalError;

  ApiException({
    required this.message,
    this.statusCode,
    this.originalError,
  });

  @override
  String toString() => message;
}

class UnauthorizedException extends ApiException {
  UnauthorizedException({String? message, dynamic originalError})
      : super(
          message: message ?? 'Session expired. Please login again.',
          statusCode: 401,
          originalError: originalError,
        );
}

class ValidationException extends ApiException {
  final Map<String, dynamic> errors;

  ValidationException({
    required String message,
    required this.errors,
    dynamic originalError,
  }) : super(
          message: message,
          statusCode: 422,
          originalError: originalError,
        );
}

class ServerException extends ApiException {
  ServerException({String? message, int? statusCode, dynamic originalError})
      : super(
          message: message ?? 'Internal server error occurred.',
          statusCode: statusCode ?? 500,
          originalError: originalError,
        );
}

class NetworkException extends ApiException {
  NetworkException({String? message, dynamic originalError})
      : super(
          message: message ?? 'No internet connection.',
          originalError: originalError,
        );
}
