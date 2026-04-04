import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

class VrProgressTimeline extends StatelessWidget {
  final int currentStep;
  final int totalSteps;
  final String activeStepLabel;
  final bool isCompleted;

  const VrProgressTimeline({
    super.key,
    required this.currentStep,
    required this.totalSteps,
    required this.activeStepLabel,
    this.isCompleted = false,
  });

  @override
  Widget build(BuildContext context) {
    // Prevent divide by zero
    final double progress = totalSteps > 0 
      ? (currentStep / totalSteps).clamp(0.0, 1.0) 
      : 0.0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              isCompleted ? 'Selesai' : 'Langkah $currentStep dari $totalSteps',
              style: PharmTextStyles.caption.copyWith(
                color: PharmColors.textSecondary,
              ),
            ),
            Text(
              '${(progress * 100).toInt()}%',
              style: PharmTextStyles.caption.copyWith(
                color: isCompleted ? PharmColors.success : PharmColors.primaryLight,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
             value: isCompleted ? 1.0 : progress,
             minHeight: 8,
             backgroundColor: PharmColors.surface,
             valueColor: AlwaysStoppedAnimation<Color>(
               isCompleted ? PharmColors.success : PharmColors.primary,
             ),
          ),
        ),
        const SizedBox(height: PharmSpacing.md),
        
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: PharmColors.background.withOpacity(0.5),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: isCompleted 
                ? PharmColors.success.withOpacity(0.3)
                : PharmColors.primary.withOpacity(0.1),
            )
          ),
          child: Row(
            children: [
              Icon(
                isCompleted ? Icons.check_circle_rounded : Icons.play_arrow_rounded, 
                color: isCompleted ? PharmColors.success : PharmColors.primaryLight, 
                size: 16
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  isCompleted ? 'Semua tahapan terselesaikan' : activeStepLabel,
                  style: PharmTextStyles.bodyMedium.copyWith(
                    color: isCompleted ? PharmColors.success : PharmColors.primaryLight,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class VrOutcomePanel extends StatelessWidget {
  final bool isSuccess;
  final String title;
  final String description;
  final Widget? trailingBadge;

  const VrOutcomePanel({
    super.key,
    required this.isSuccess,
    required this.title,
    required this.description,
    this.trailingBadge,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSuccess ? PharmColors.warning : PharmColors.error;
    final icon = isSuccess ? Icons.workspace_premium_rounded : Icons.cancel_rounded;

    return Container(
      padding: PharmSpacing.allLg,
      decoration: BoxDecoration(
        color: color.withOpacity(0.05),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(width: PharmSpacing.md),
              Expanded(
                child: Text(
                  title,
                  style: PharmTextStyles.h4.copyWith(color: color),
                ),
              ),
              if (trailingBadge != null) trailingBadge!,
            ],
          ),
          const SizedBox(height: PharmSpacing.md),
          Text(
            description,
            style: PharmTextStyles.bodyMedium.copyWith(
              color: PharmColors.textSecondary,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }
}
