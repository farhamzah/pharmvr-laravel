import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';

/// Reusable answer option card with selected/unselected states.
class AssessmentOptionCard extends StatelessWidget {
  final String label;
  final String text;
  final bool isSelected;
  final bool isCorrect;
  final bool isWrong;
  final bool showResult;
  final VoidCallback? onTap;

  const AssessmentOptionCard({
    super.key,
    required this.label,
    required this.text,
    this.isSelected = false,
    this.isCorrect = false,
    this.isWrong = false,
    this.showResult = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    Color borderColor = Colors.white.withOpacity(0.08);
    Color bgColor = PharmColors.surface.withOpacity(0.6);
    Color labelColor = PharmColors.textSecondary;
    Color textColor = PharmColors.textSecondary;

    if (showResult && isCorrect) {
      borderColor = PharmColors.success.withOpacity(0.6);
      bgColor = PharmColors.success.withOpacity(0.08);
      labelColor = PharmColors.success;
      textColor = PharmColors.textPrimary;
    } else if (showResult && isWrong) {
      borderColor = PharmColors.error.withOpacity(0.6);
      bgColor = PharmColors.error.withOpacity(0.08);
      labelColor = PharmColors.error;
      textColor = PharmColors.textPrimary;
    } else if (isSelected) {
      borderColor = PharmColors.primary.withOpacity(0.6);
      bgColor = PharmColors.primary.withOpacity(0.08);
      labelColor = PharmColors.primary;
      textColor = PharmColors.textPrimary;
    }

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: borderColor, width: 1.5),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: labelColor.withOpacity(0.15),
                border: Border.all(color: labelColor.withOpacity(0.4)),
              ),
              child: Center(
                child: showResult && isCorrect
                    ? Icon(Icons.check, size: 16, color: labelColor)
                    : showResult && isWrong
                        ? Icon(Icons.close, size: 16, color: labelColor)
                        : Text(
                            label,
                            style: PharmTextStyles.label.copyWith(
                              color: labelColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.only(top: 5),
                child: Text(
                  text,
                  style: PharmTextStyles.bodyMedium.copyWith(
                    color: textColor,
                    height: 1.4,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
