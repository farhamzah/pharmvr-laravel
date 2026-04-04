import 'package:flutter/material.dart';
import '../../domain/models/learning_module.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';

class EducationContentDescription extends StatelessWidget {
  final LearningModule module;

  const EducationContentDescription({
    super.key,
    required this.module,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: PharmSpacing.allLg,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 4,
                height: 18,
                decoration: BoxDecoration(
                  color: PharmColors.primary,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 10),
              Text(
                'Ikhtisar Materi', 
                style: PharmTextStyles.h3.copyWith(
                  color: PharmColors.textPrimary,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: PharmColors.surface.withOpacity(0.3),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: PharmColors.cardBorder.withOpacity(0.5)),
            ),
            child: Text(
              module.description,
              style: PharmTextStyles.bodyMedium.copyWith(
                color: PharmColors.textSecondary.withOpacity(0.9),
                height: 1.8,
                letterSpacing: 0.2,
              ),
            ),
          ),
          const SizedBox(height: PharmSpacing.xl),

          if (module.learningPath != null && module.learningPath!['objectives'] != null) ...[
            Text(
              'Tujuan Pembelajaran',
              style: PharmTextStyles.h3.copyWith(
                color: PharmColors.textPrimary,
              ),
            ),
            const SizedBox(height: PharmSpacing.md),
            ...List<String>.from(module.learningPath!['objectives'] ?? []).map((objective) {
              return Padding(
                padding: const EdgeInsets.only(bottom: PharmSpacing.md),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.only(top: 4.0),
                      child: Icon(
                        Icons.check_circle,
                        size: 16,
                        color: PharmColors.success,
                      ),
                    ),
                    const SizedBox(width: PharmSpacing.sm),
                    Expanded(
                      child: Text(
                        objective,
                        style: PharmTextStyles.bodyMedium.copyWith(
                          color: PharmColors.textPrimary,
                          height: 1.5,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
            const SizedBox(height: PharmSpacing.xl),
          ]
        ],
      ),
    );
  }
}
