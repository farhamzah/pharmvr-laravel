import 'package:flutter/foundation.dart';

class NetworkConstants {
  // Base URLs for different environments
  static const String emulatorBaseUrl = 'http://10.0.2.2:8000/api/v1';
  static const String localWifiBaseUrl = 'http://192.168.1.3:8000/api/v1'; 
  static const String productionBaseUrl = 'https://admin.pharmvr.cloud/api/v1';

  static String get baseUrl {
    if (kIsWeb) {
      // For web production builds, strictly use the SSL-secured API endpoint
      if (!kDebugMode) return 'https://admin.pharmvr.cloud/api/v1';
      return 'http://localhost:8000/api/v1';
    }
    
    // For mobile builds, use production URL as default, with debug overrides for emulator/local testing
    if (kDebugMode) {
      // PRO-TIP: Change this to true if testing on a real device
      const bool usePhysicalDevice = false; 
      return usePhysicalDevice ? localWifiBaseUrl : emulatorBaseUrl;
    }
    
    return productionBaseUrl;
  }

  /// Sanitizes URLs from the backend that might contain 'localhost' or local IPs
  /// and converts them to be accessible from the emulator/physical device or production web.
  static String sanitizeUrl(String? url) {
    if (url == null || url.isEmpty) return '';

    // Step 1: Prepend base storage URL for relative paths if needed
    // This is a safety layer for paths that don't start with http/assets
    String sanitized = url;
    if (!url.startsWith('http')) {
      final currentBase = baseUrl.replaceAll('/api/v1', '');
      sanitized = '$currentBase/${url.startsWith('/') ? url.substring(1) : url}';
    }

    // Step 2: Ensure the URL points to the correct host based on the environment
    // This handles data that was saved with 10.0.2.2/127.0.0.1 during local testing.
    final localPatterns = [
      '//localhost',
      '//127.0.0.1',
      '//10.0.2.2',
      '//10.100.0.97',
      '//192.168.1.3',
    ];

    final isProduction = !kDebugMode;
    
    for (final pattern in localPatterns) {
      if (sanitized.contains(pattern)) {
        if (isProduction) {
          // In production (Web/Live), always point to the real domain
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(localhost|127\.0\.0\.1|10\.0\.2\.2|10\.100\.0\.97|192\.168\.1\.3)(:\d+)?'), 
            '//admin.pharmvr.cloud'
          );
        } else if (kIsWeb) {
          // In local web debug, ensure we point to localhost of the dev machine
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(127\.0\.0\.1|10\.0\.2\.2|10\.100\.0\.97|192\.168\.1\.3)(:\d+)?'), 
            '//localhost'
          );
        } else {
          // In local mobile debug, point to the emulator gateway
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(localhost|127\.0\.0\.1|10\.100\.0\.97|192\.168\.1\.3)(:\d+)?'), 
            '//10.0.2.2'
          );
        }
        break; 
      }
    }

    // Step 3: Web-specific CORS bypass
    // On Web, direct storage access often fails with CORS because Laravel's static file server 
    // doesn't send the headers. We use our /api/v1/media proxy instead.
    if (kIsWeb) {
      if (sanitized.contains('/storage/') && !sanitized.contains('/api/v1/media/')) {
        sanitized = sanitized.replaceFirst('/storage/', '/api/v1/media/');
      } else if (sanitized.contains('/assets/') && !sanitized.contains('/api/v1/media/')) {
        sanitized = sanitized.replaceFirst('/assets/', '/api/v1/media/assets/');
      }
    }

    return sanitized;
  }

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
