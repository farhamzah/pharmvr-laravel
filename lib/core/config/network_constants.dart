import 'package:flutter/foundation.dart';

class NetworkConstants {
  // Base URLs for different environments
  static const String emulatorBaseUrl = 'http://10.0.2.2:8000/api/v1';
  static const String localWifiBaseUrl = 'http://localhost:8000/api/v1'; 
  static const String productionBaseUrl = 'http://admin.pharmvr.cloud/api/v1';

  static String get baseUrl {
    if (kIsWeb) {
      // For web development, use the actual domain if available, or localhost
      // Actually, if it's production web, it should be the production URL
      if (!kDebugMode) return 'http://admin.pharmvr.cloud/api/v1';
      return 'http://localhost:8000/api/v1';
    }
    
    // In debug mode, use the standard emulator gateway
    // In debug mode, prefer the local Wifi IP if it's set and we're not on web
    if (kDebugMode) {
      // PRO-TIP: Change this to true if testing on a real device
      const bool usePhysicalDevice = false; 
      return usePhysicalDevice ? localWifiBaseUrl : emulatorBaseUrl;
    }
    
    return productionBaseUrl;
  }

  /// Sanitizes URLs from the backend that might contain 'localhost' or local IPs
  /// and converts them to be accessible from the emulator/physical device.
  static String sanitizeUrl(String? url) {
    if (url == null || url.isEmpty) return '';
    if (!kDebugMode) return url;
    if (kIsWeb) return url;

    // Only sanitize if it starts with http (most local dev URLs)
    // and contains a local identifier, OR if it's a relative path that needs the base URL
    // Actually, backend usually returns full URLs.
    
    // Explicitly ignore external domains that we know are safe
    if (url.startsWith('https://') && 
       (url.contains('youtube.com') || url.contains('ytimg.com') || url.contains('google.com'))) {
      return url;
    }

    // Map localhost, 127.0.0.1, or physical host IPs back to the standard emulator gateway
    final localPatterns = [
      '//localhost',
      '//127.0.0.1',
      '//10.100.0.97',
    ];

    String sanitized = url;
    for (final pattern in localPatterns) {
      if (sanitized.contains(pattern)) {
        // Replace with the standard emulator gateway
        final updated = sanitized.replaceFirst(
          RegExp(r'//(localhost|127\.0\.0\.1|10\.100\.0\.97)'), 
          '//10.0.2.2'
        );
        
        if (updated != sanitized) {
          debugPrint('URL Sanitized: $url -> $updated');
          sanitized = updated;
          break;
        }
      }
    }

    return sanitized;
  }

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
