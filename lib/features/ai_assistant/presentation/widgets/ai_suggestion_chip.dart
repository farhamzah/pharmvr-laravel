import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';

class AiSuggestionChip extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const AiSuggestionChip({
    super.key,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          decoration: BoxDecoration(
            color: PharmColors.surfaceLight,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: PharmColors.primary.withValues(alpha: 0.2),
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: PharmColors.primary.withValues(alpha: 0.05),
                blurRadius: 4,
                spreadRadius: 0,
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.auto_awesome_outlined,
                size: 14,
                color: PharmColors.primary,
              ),
              const SizedBox(width: 8),
              Text(
                label,
                style: PharmTextStyles.label.copyWith(
                  color: PharmColors.primary,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
