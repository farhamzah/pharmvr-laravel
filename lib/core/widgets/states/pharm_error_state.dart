import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_text_styles.dart';
import '../pharm_primary_button.dart';

/// A premium, reusable widget for all error and exception states.
class PharmErrorState extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String message;
  final VoidCallback? onRetry;
  final String retryText;

  const PharmErrorState({
    super.key,
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.message,
    this.onRetry,
    this.retryText = 'Try Again',
  });

  /// 1. Generic Error
  factory PharmErrorState.generic({
    String message = 'An unexpected error occurred while loading this content.',
    VoidCallback? onRetry,
  }) {
    return PharmErrorState(
      icon: Icons.error_outline,
      iconColor: PharmColors.error,
      title: 'Something Went Wrong',
      message: message,
      onRetry: onRetry,
    );
  }

  /// 2. Network / Server Error
  factory PharmErrorState.network({
    VoidCallback? onRetry,
  }) {
    return PharmErrorState(
      icon: Icons.cloud_off_outlined,
      iconColor: PharmColors.warning,
      title: 'Connection Failed',
      message: 'We couldn\'t connect to the PharmVR servers. Please verify your connection and try again.',
      onRetry: onRetry,
      retryText: 'Retry Connection',
    );
  }

  /// 3. Offline State (Explicitly no internet)
  factory PharmErrorState.offline({
    VoidCallback? onRetry,
  }) {
    return PharmErrorState(
      icon: Icons.wifi_off_outlined,
      iconColor: PharmColors.textSecondary,
      title: 'You are Offline',
      message: 'This feature requires an active internet connection to sync your training progress.',
      onRetry: onRetry,
      retryText: 'Check Connection',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Error Icon inside a subtle tinted box
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: iconColor.withOpacity(0.08),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: iconColor.withOpacity(0.2)),
              ),
              child: Icon(icon, size: 48, color: iconColor),
            ),
            const SizedBox(height: 32),

            // Text content
            Text(
              title,
              style: PharmTextStyles.h3.copyWith(color: PharmColors.textPrimary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            Text(
              message,
              style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary, height: 1.5),
              textAlign: TextAlign.center,
            ),

            // Retry Button
            if (onRetry != null) ...[
              const SizedBox(height: 32),
              SizedBox(
                width: 200,
                child: PharmPrimaryButton(
                  text: retryText,
                  icon: Icons.refresh,
                  onPressed: onRetry,
                  isOutlined: true,
                ),
              ),
            ]
          ],
        ),
      ),
    );
  }
}
