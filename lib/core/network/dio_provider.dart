import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/secure_storage_service.dart';
import '../config/network_constants.dart';
import 'api_exception.dart';
import '../../features/auth/presentation/providers/auth_provider.dart';

final secureStorageProvider = Provider<SecureStorageService>((ref) {
  return SecureStorageService();
});

final dioProvider = Provider<Dio>((ref) {
  final secureStorage = ref.watch(secureStorageProvider);
  
  final dio = Dio(
    BaseOptions(
      baseUrl: NetworkConstants.baseUrl,
      connectTimeout: NetworkConstants.connectionTimeout,
      receiveTimeout: NetworkConstants.receiveTimeout,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Try getting token from memory first (authProvider)
        final memToken = ref.read(authProvider).token;
        String? token = memToken;
        
        // Fallback to secure storage if memToken is null
        if (token == null) {
          token = await secureStorage.getToken();
        }

        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }

        if (kDebugMode) {
          debugPrint('HTTP Request: [${options.method}] ${options.uri}');
          if (options.data != null) debugPrint('Data: ${options.data}');
        }
        
        return handler.next(options);
      },
      onResponse: (response, handler) {
        if (kDebugMode) {
          debugPrint('HTTP Response: [${response.statusCode}] ${response.requestOptions.uri}');
        }
        return handler.next(response);
      },
      onError: (e, handler) {
        if (kDebugMode) {
          debugPrint('HTTP Error: [${e.response?.statusCode}] ${e.requestOptions.uri}');
          debugPrint('Message: ${e.message}');
          if (e.response?.data != null) debugPrint('Data: ${e.response?.data}');
        }

        final exception = _handleDioError(e, ref);
        return handler.next(
          DioException(
            requestOptions: e.requestOptions,
            response: e.response,
            type: e.type,
            error: exception,
          ),
        );
      },
    ),
  );

  return dio;
});

ApiException _handleDioError(DioException e, Ref ref) {
  if (e.type == DioExceptionType.connectionTimeout || 
      e.type == DioExceptionType.sendTimeout || 
      e.type == DioExceptionType.receiveTimeout) {
    return NetworkException(message: 'Connection timed out. Please check your internet.');
  }

  if (e.type == DioExceptionType.connectionError) {
    return NetworkException();
  }

  final response = e.response;
  if (response != null) {
    final statusCode = response.statusCode;
    final data = response.data;

    if (statusCode == 401) {
      // Trigger logout if we get an unauthorized error while authenticated
      try {
        if (ref.read(authProvider).isAuthenticated) {
          ref.read(authProvider.notifier).logout();
        }
      } catch (_) {}
      return UnauthorizedException(originalError: e);
    }

    if (statusCode == 422 && data is Map) {
      final errors = data['errors'] as Map<String, dynamic>? ?? {};
      String message = data['message'] ?? 'Validation failed';
      
      // Extract first validation error if available
      if (errors.isNotEmpty) {
        final firstError = errors.values.first;
        if (firstError is List && firstError.isNotEmpty) {
          message = firstError.first.toString();
        }
      }
      
      return ValidationException(
        message: message,
        errors: errors,
        originalError: e,
      );
    }

    if (statusCode != null && statusCode >= 500) {
      return ServerException(originalError: e, statusCode: statusCode);
    }

    if (data is Map && data.containsKey('message')) {
      return ApiException(
        message: data['message'].toString(),
        statusCode: statusCode,
        originalError: e,
      );
    }
  }

  return ApiException(
    message: 'An unexpected error occurred.',
    originalError: e,
  );
}
