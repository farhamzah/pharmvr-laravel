import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_glass_card.dart';

class TrainingProgressScreen extends StatelessWidget {
  const TrainingProgressScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Training Analytics'),
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: BackButton(onPressed: () => context.pop()),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              PharmGlassCard(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Total Progress', style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textSecondary)),
                        const SizedBox(height: 8),
                        Text('68%', style: PharmTextStyles.h1),
                      ],
                    ),
                    const SizedBox(
                      width: 60,
                      height: 60,
                      child: CircularProgressIndicator(
                        value: 0.68,
                        backgroundColor: PharmColors.background,
                        color: PharmColors.success,
                        strokeWidth: 8,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              Text('Completed Modules', style: PharmTextStyles.h3),
              const SizedBox(height: 16),
              ...List.generate(4, (index) => Container(
                margin: const EdgeInsets.only(bottom: 12),
                child: PharmGlassCard(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle, color: PharmColors.success),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('GMP Basics Level ${index+1}', style: PharmTextStyles.bodyLarge),
                            Text('Score: ${90 + index}%', style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.primary)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              )),
            ],
          ),
        ),
      ),
    );
  }
}
