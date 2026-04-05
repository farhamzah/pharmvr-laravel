import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';

class AiStateBadge extends StatelessWidget {
  final String? responseMode;

  const AiStateBadge({super.key, this.responseMode});

  @override
  Widget build(BuildContext context) {
    if (responseMode == null) return const SizedBox.shrink();

    Color bgColor;
    Color textColor;
    String label;
    IconData icon;

    switch (responseMode) {
      case 'grounded':
        bgColor = PharmColors.primary.withOpacity(0.1);
        textColor = PharmColors.primary;
        label = 'GROUNDED ANSWER';
        icon = Icons.verified_user_outlined;
        break;
      case 'restricted':
        bgColor = Colors.amber.withOpacity(0.1);
        textColor = Colors.amber;
        label = 'RESTRICTED TOPIC';
        icon = Icons.shield_outlined;
        break;
      case 'neutral':
      default:
        bgColor = Colors.white.withOpacity(0.05);
        textColor = Colors.white54;
        label = 'INSUFFICIENT CONTEXT';
        icon = Icons.help_outline;
        break;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: textColor.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: textColor, size: 12),
          const SizedBox(width: 6),
          Text(
            label,
            style: PharmTextStyles.overline.copyWith(
              color: textColor,
              fontSize: 8,
              letterSpacing: 1.0,
            ),
          ),
        ],
      ),
    );
  }
}
