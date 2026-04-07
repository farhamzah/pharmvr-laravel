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

    String sanitized = url;

    // 1. If it's already an absolute URL (from Backend AssetUrlService):
    if (sanitized.startsWith('http')) {
      // Ensure production cloud URLs use HTTPS
      if (!kDebugMode && sanitized.contains('pharmvr.cloud') && sanitized.startsWith('http://')) {
        sanitized = sanitized.replaceFirst('http://', 'https://');
      }
      return sanitized;
    }

    // 2. Relative Path Fallback (if any):
    // Construct absolute URL based on the current base
    final currentBase = baseUrl.replaceAll('/api/v1', '');
    sanitized = '$currentBase/${sanitized.startsWith('/') ? sanitized.substring(1) : sanitized}';
    
    return sanitized;
  }

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
