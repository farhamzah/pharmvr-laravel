import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';

/// Centralized error handling utilities for network and session errors.
/// These helpers ensure consistent, secure UX across all screens.
class PharmErrorHandler {
  /// Show a floating SnackBar with an error message.
  /// Uses generic messages for security; never exposes raw server details.
  static void showError(BuildContext context, String message) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).removeCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error_outline, color: Colors.white, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white),
              ),
            ),
          ],
        ),
        backgroundColor: PharmColors.error,
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 4),
      ),
    );
  }

  /// Show a success snackbar.
  static void showSuccess(BuildContext context, String message) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).removeCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_outline, color: Colors.white, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white),
              ),
            ),
          ],
        ),
        backgroundColor: PharmColors.success,
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  /// Sanitize raw error messages for user display.
  /// Never expose stack traces, server IPs, or technical details.
  static String sanitizeError(dynamic error) {
    final raw = error.toString().toLowerCase();

    if (raw.contains('socketexception') ||
        raw.contains('connection refused') ||
        raw.contains('network is unreachable') ||
        raw.contains('failed host lookup')) {
      return 'Unable to connect to the server. Please check your internet connection and try again.';
    }
    if (raw.contains('timeout') || raw.contains('timedout')) {
      return 'The request timed out. Please try again.';
    }
    if (raw.contains('401') || raw.contains('unauthenticated')) {
      return 'Your session has expired. Please log in again.';
    }
    if (raw.contains('403') || raw.contains('forbidden')) {
      return 'You do not have permission to perform this action.';
    }
    if (raw.contains('404')) {
      return 'The requested resource was not found.';
    }
    if (raw.contains('422') || raw.contains('validation')) {
      return 'Please check your input and try again.';
    }
    if (raw.contains('500') || raw.contains('internal server')) {
      return 'Something went wrong on our end. Please try again later.';
    }

    // Fallback: strip "Exception: " prefix if present
    final msg = error.toString().replaceFirst(RegExp(r'^Exception:\s*'), '');
    if (msg.length > 120) {
      return 'An unexpected error occurred. Please try again.';
    }
    return msg;
  }

  /// Show a session expired dialog with redirect to login.
  static void showSessionExpired(BuildContext context) {
    if (!context.mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: PharmColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        icon: const Icon(Icons.lock_clock, color: Colors.amber, size: 48),
        title: Text('Session Expired', style: PharmTextStyles.h3),
        content: Text(
          'Your login session has expired for security reasons. Please sign in again to continue.',
          style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              // Navigate to login using the root navigator context
              Navigator.of(context).pushNamedAndRemoveUntil('/auth/login', (_) => false);
            },
            child: const Text('SIGN IN', style: TextStyle(color: PharmColors.primary, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
