import 'package:flutter/material.dart';
import '../../theme/pharm_colors.dart';
import '../../theme/pharm_text_styles.dart';
import '../pharm_primary_button.dart';

/// A premium, reusable widget for all empty states.
class PharmEmptyState extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String message;
  final String? primaryActionText;
  final IconData? primaryActionIcon;
  final VoidCallback? onPrimaryAction;

  const PharmEmptyState({
    super.key,
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.message,
    this.primaryActionText,
    this.primaryActionIcon,
    this.onPrimaryAction,
  });

  /// 1. Generic No Data
  factory PharmEmptyState.noData({
    String title = 'No Data Found',
    String message = 'There is currently no information to display here.',
  }) {
    return PharmEmptyState(
      icon: Icons.inbox_outlined,
      iconColor: PharmColors.textTertiary,
      title: title,
      message: message,
    );
  }

  /// 2. No Conversations (AI Assistant)
  factory PharmEmptyState.noConversation({
    VoidCallback? onStartChat,
  }) {
    return PharmEmptyState(
      icon: Icons.auto_awesome_outlined,
      iconColor: PharmColors.info,
      title: 'PharmVR AI Assistant',
      message: 'Your intelligent companion for GMP, CPOB, and cleanroom standard operating procedures. Ask anything to begin.',
      primaryActionText: 'Start New Chat',
      primaryActionIcon: Icons.add_comment_outlined,
      onPrimaryAction: onStartChat,
    );
  }

  /// 3. No News
  factory PharmEmptyState.noNews() {
    return PharmEmptyState(
      icon: Icons.newspaper_outlined,
      iconColor: PharmColors.primaryLight,
      title: 'You\'re All Caught Up',
      message: 'Check back later for the latest industry updates, PharmVR platform announcements, and new module releases.',
    );
  }

  /// 4. No Training / No Modules
  factory PharmEmptyState.noTraining({
    VoidCallback? onExplore,
  }) {
    return PharmEmptyState(
      icon: Icons.vrpano_outlined,
      iconColor: PharmColors.primary,
      title: 'Your Journey Starts Here',
      message: 'You haven\'t started any VR training modules yet. Explore the education hub to find your first immersive experience.',
      primaryActionText: 'Explore Modules',
      primaryActionIcon: Icons.search,
      onPrimaryAction: onExplore,
    );
  }

  /// 5. No Assessments
  factory PharmEmptyState.noAssessments({
    VoidCallback? onGoToTraining,
  }) {
    return PharmEmptyState(
      icon: Icons.fact_check_outlined,
      iconColor: PharmColors.warning,
      title: 'No Pending Assessments',
      message: 'Pre-tests and Post-tests will appear here when you unlock or complete specific VR training sessions.',
      primaryActionText: 'Go to Training',
      primaryActionIcon: Icons.play_arrow,
      onPrimaryAction: onGoToTraining,
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
            // Glowing Icon
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: iconColor.withOpacity(0.08),
                border: Border.all(color: iconColor.withOpacity(0.15), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: iconColor.withOpacity(0.15),
                    blurRadius: 30,
                    spreadRadius: 5,
                  )
                ],
              ),
              child: Icon(icon, size: 42, color: iconColor),
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

            // Optional CTA
            if (onPrimaryAction != null && primaryActionText != null) ...[
              const SizedBox(height: 32),
              SizedBox(
                width: 220,
                child: PharmPrimaryButton(
                  text: primaryActionText!,
                  icon: primaryActionIcon,
                  onPressed: onPrimaryAction,
                  isOutlined: true, // Empty states usually use floating/outline CTAs
                ),
              ),
            ]
          ],
        ),
      ),
    );
  }
}
