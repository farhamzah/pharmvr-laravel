import 'package:flutter/foundation.dart';

class NetworkConstants {
  // Base URLs for different environments
  static const String emulatorBaseUrl = 'http://10.0.2.2:8000/api/v1';
  static const String localWifiBaseUrl = 'http://192.168.1.3:8000/api/v1'; 
  static const String productionBaseUrl = 'https://admin.pharmvr.cloud/api/v1';

  static String get baseUrl {
    if (kIsWeb) {
      // Dynamic detection for Web environment
      // If we are running on the production domain, use the secured production API
      final String currentHost = Uri.base.host;
      if (currentHost.contains('pharmvr.cloud')) {
        return 'https://admin.pharmvr.cloud/api/v1';
      }
      
      // Default for local development or other web hosts
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

    final isProduction = !kDebugMode;
    String sanitized = url;

    // 1. If it's already a full absolute URL, check if it needs HTTPS fixing
    if (sanitized.startsWith('http')) {
      if (isProduction && sanitized.contains('pharmvr.cloud') && sanitized.startsWith('http://')) {
        sanitized = sanitized.replaceFirst('http://', 'https://');
      }
      
      // If it's a legacy localhost URL from an old DB record, extract and re-resolve
      if (sanitized.contains('localhost') || sanitized.contains('127.0.0.1') || sanitized.contains('10.0.2.2')) {
         final path = sanitized.split('/storage/').last;
         final currentBase = baseUrl.replaceAll('/api/v1', '');
         sanitized = '$currentBase/storage/$path';
      }

      return sanitized;
    }

    // 2. Prepend base URL for relative paths
    final currentBase = baseUrl.replaceAll('/api/v1', '');
    sanitized = '$currentBase/${sanitized.startsWith('/') ? sanitized.substring(1) : sanitized}';
    
    // 3. Web-specific: Ensure dynamic storage access goes through Media Proxy
    if (kIsWeb && sanitized.contains('/storage/')) {
       sanitized = sanitized.replaceFirst('/storage/', '/api/v1/media/');
    }

    return sanitized;
  }

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
