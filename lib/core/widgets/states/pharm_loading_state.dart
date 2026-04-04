import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_text_styles.dart';

/// A premium full-screen or section-level loading state.
/// For inline spinners, continue using `PharmLoadingIndicator`.
class PharmLoadingState extends StatelessWidget {
  final String title;
  final String subtitle;

  const PharmLoadingState({
    super.key,
    this.title = 'Loading...',
    this.subtitle = 'Please wait while we prepare your data.',
  });

  /// Connecting to VR Headset
  factory PharmLoadingState.vrConnecting() {
    return const PharmLoadingState(
      title: 'Connecting to Headset',
      subtitle: 'Establishing a secure local connection to your VR device...',
    );
  }

  /// Analyzing Assessment
  factory PharmLoadingState.assessment() {
    return const PharmLoadingState(
      title: 'Analyzing Answers',
      subtitle: 'Calculating your score and preparing your training summary...',
    );
  }

  /// AI Processing
  factory PharmLoadingState.aiThinking() {
    return const PharmLoadingState(
      title: 'AI is Thinking...',
      subtitle: 'Retrieving relevant GMP guidelines and training context...',
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
            // Outer glowing ring with spinner
            Stack(
              alignment: Alignment.center,
              children: [
                // Inner Icon
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: PharmColors.primary.withOpacity(0.1),
                  ),
                  child: Icon(Icons.sync, color: PharmColors.primary.withOpacity(0.5), size: 24),
                ),
                // Outer Spinner
                const SizedBox(
                  width: 80,
                  height: 80,
                  child: CircularProgressIndicator(
                    color: PharmColors.primary,
                    strokeWidth: 2,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 32),

            Text(
              title,
              style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: PharmTextStyles.caption.copyWith(color: PharmColors.textSecondary),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
