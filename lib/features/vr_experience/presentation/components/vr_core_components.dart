import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

enum VrBadgeState { idle, active, success, error, warning }

class VrStatusBadge extends StatelessWidget {
  final VrBadgeState state;
  final String text;
  final IconData? icon;

  const VrStatusBadge({
    super.key,
    required this.state,
    required this.text,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    Color getBgColor() {
      switch (state) {
        case VrBadgeState.idle: return PharmColors.surface;
        case VrBadgeState.active: return PharmColors.primary.withOpacity(0.15);
        case VrBadgeState.success: return PharmColors.success.withOpacity(0.15);
        case VrBadgeState.error: return PharmColors.error.withOpacity(0.15);
        case VrBadgeState.warning: return PharmColors.warning.withOpacity(0.15);
      }
    }

    Color getTextColor() {
      switch (state) {
        case VrBadgeState.idle: return PharmColors.textSecondary;
        case VrBadgeState.active: return PharmColors.primaryLight;
        case VrBadgeState.success: return PharmColors.success;
        case VrBadgeState.error: return PharmColors.error;
        case VrBadgeState.warning: return PharmColors.warning;
      }
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: getBgColor(),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: getTextColor().withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 12, color: getTextColor()),
            const SizedBox(width: 4),
          ],
          Text(
            text.toUpperCase(),
            style: PharmTextStyles.overline.copyWith(
              color: getTextColor(),
              letterSpacing: 1.5,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class VrChecklistItem extends StatelessWidget {
  final String title;
  final bool isCompleted;
  final String? actionLabel;
  final VoidCallback? onAction;

  const VrChecklistItem({
    super.key,
    required this.title,
    required this.isCompleted,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.md, vertical: 12),
      child: Row(
        children: [
          Icon(
            isCompleted ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
            color: isCompleted ? PharmColors.success : PharmColors.textTertiary,
            size: 20,
          ),
          const SizedBox(width: PharmSpacing.md),
          Text(
            title,
            style: PharmTextStyles.bodyMedium.copyWith(
              color: isCompleted ? PharmColors.textPrimary : PharmColors.textSecondary,
              decoration: isCompleted ? TextDecoration.lineThrough : null,
              decorationColor: PharmColors.textTertiary,
            ),
          ),
          const Spacer(),
          if (!isCompleted && actionLabel != null)
            SizedBox(
              height: 28,
              child: TextButton(
                onPressed: onAction,
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  backgroundColor: PharmColors.surface,
                ),
                child: Text(
                  actionLabel!,
                  style: PharmTextStyles.label.copyWith(
                    color: PharmColors.primaryLight,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
