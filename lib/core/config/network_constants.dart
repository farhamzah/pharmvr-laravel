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

    // Step 1: Force HTTPS for production domain to avoid Mixed Content blocks
    if (isProduction && sanitized.contains('pharmvr.cloud') && sanitized.startsWith('http://')) {
      sanitized = sanitized.replaceFirst('http://', 'https://');
    }

    // Step 2: Prepend base storage URL for relative paths if needed
    if (!sanitized.startsWith('http')) {
      final currentBase = baseUrl.replaceAll('/api/v1', '');
      sanitized = '$currentBase/${sanitized.startsWith('/') ? sanitized.substring(1) : sanitized}';
    }

    // Step 3: Ensure the URL points to the correct host based on the environment
    final localPatterns = [
      '//localhost',
      '//127.0.0.1',
      '//10.0.2.2',
      '//10.100.0.97',
      '//192.168.1.3',
      '//202.10.42.226', // VPS IP - Essential for production asset sanitization
    ];

    for (final pattern in localPatterns) {
      if (sanitized.contains(pattern)) {
        if (isProduction) {
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|localhost|127\.0\.0\.1)(:\d+)?'), 
            '//admin.pharmvr.cloud'
          );
          // Re-verify HTTPS after replacement
          if (sanitized.startsWith('http://')) {
            sanitized = sanitized.replaceFirst('http://', 'https://');
          }
        } else if (kIsWeb) {
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(127\.0\.0\.1|10\.0\.2\.2|10\.100\.0\.97|192\.168\.1\.3)(:\d+)?'), 
            '//localhost'
          );
        } else {
          sanitized = sanitized.replaceFirst(
            RegExp(r'//(localhost|127\.0\.0\.1|10\.100\.0\.97|192\.168\.1\.3)(:\d+)?'), 
            '//10.0.2.2'
          );
        }
        break; 
      }
    }

    // Step 4: Web-specific CORS bypass
    // On Web, direct storage/asset access often fails with CORS. 
    // We route remote assets through our backend media proxy.
    // CRITICAL: We MUST exclude local assets (icons, manifest) from being proxied.
    if (kIsWeb) {
      final isLocalAsset = sanitized.startsWith('assets/') || 
                           sanitized.startsWith('packages/') || 
                           sanitized.contains('AssetManifest');
      
      if (!isLocalAsset) {
        if (sanitized.contains('/storage/') && !sanitized.contains('/api/v1/media/')) {
          sanitized = sanitized.replaceFirst('/storage/', '/api/v1/media/');
        } else if (sanitized.contains('/assets/') && !sanitized.contains('/api/v1/media/')) {
          // Only proxy if it looks like a remote asset (has http or domain)
          if (sanitized.contains('http') || sanitized.contains('pharmvr.cloud')) {
            sanitized = sanitized.replaceFirst('/assets/', '/api/v1/media/assets/');
          }
        }
      }
    }

    return sanitized;
  }

  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
