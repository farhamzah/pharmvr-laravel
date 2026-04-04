import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_training_session.dart';

class VrActiveStatusCard extends StatelessWidget {
  final VrTrainingSession session;

  const VrActiveStatusCard({
    super.key,
    required this.session,
  });

  @override
  Widget build(BuildContext context) {
    if (session.phase != VrSessionPhase.inProgress) return const SizedBox.shrink();

    return Padding(
      padding: PharmSpacing.horizontalLg,
      child: Container(
        padding: PharmSpacing.allLg,
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: PharmColors.success.withOpacity(0.5)),
          boxShadow: [
            BoxShadow(
              color: PharmColors.success.withOpacity(0.1),
              blurRadius: 20,
              spreadRadius: 2,
            ),
          ],
        ),
        child: Column(
          children: [
            // Headset Sync Status
            Row(
              children: [
                const Icon(Icons.sync_rounded, color: PharmColors.success, size: 20),
                const SizedBox(width: PharmSpacing.sm),
                Text(
                  'SINKRONISASI AKTIF', // "ACTIVE SYNC"
                  style: PharmTextStyles.label.copyWith(
                    color: PharmColors.success,
                    letterSpacing: 1.2,
                  ),
                ),
                const Spacer(),
                Text(
                  _formatTime(session.timeElapsedSeconds ?? 0),
                  style: PharmTextStyles.h4.copyWith(
                    color: Theme.of(context).textTheme.displaySmall?.color,
                    fontFeatures: const [FontFeature.tabularFigures()],
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: PharmSpacing.lg),
            
            // Progress Track
            if (session.currentStepIndex != null && session.totalSteps != null) ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Langkah ${session.currentStepIndex} dari ${session.totalSteps}',
                    style: PharmTextStyles.caption.copyWith(
                      color: Theme.of(context).textTheme.bodySmall?.color,
                    ),
                  ),
                  Text(
                    '${((session.currentStepIndex! / session.totalSteps!) * 100).toInt()}%',
                    style: PharmTextStyles.caption.copyWith(
                      color: PharmColors.success,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              
              // Custom Progress Bar
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: session.currentStepIndex! / session.totalSteps!,
                  minHeight: 8,
                  backgroundColor: Theme.of(context).dividerColor.withOpacity(0.2),
                  valueColor: const AlwaysStoppedAnimation<Color>(PharmColors.success),
                ),
              ),
              const SizedBox(height: PharmSpacing.md),
              
              // Current Step Label
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Theme.of(context).brightness == Brightness.dark ? PharmColors.background.withOpacity(0.5) : PharmColors.primary.withOpacity(0.05),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.play_arrow_rounded, color: PharmColors.primaryLight, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        session.currentStepName ?? 'Melakukan Simulasi...',
                        style: PharmTextStyles.bodyMedium.copyWith(
                          color: PharmColors.primary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            
            const SizedBox(height: PharmSpacing.xl),
            
            // AI Assistance Shortcut during VR
            Container(
              padding: PharmSpacing.allMd,
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: PharmColors.primary.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.auto_awesome, color: PharmColors.primary, size: 18),
                  ),
                  const SizedBox(width: PharmSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Butuh Bantuan AI?', // "Need AI Help?"
                          style: PharmTextStyles.bodyBold.copyWith(
                            color: PharmColors.textPrimary,
                          ),
                        ),
                        Text(
                          'Tanyakan SOP langkah ini.', // "Ask about SOP for this step."
                          style: PharmTextStyles.caption.copyWith(
                            color: PharmColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(Icons.chevron_right_rounded, color: Theme.of(context).textTheme.labelSmall?.color),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(int totalSeconds) {
    int minutes = totalSeconds ~/ 60;
    int seconds = totalSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }
}
