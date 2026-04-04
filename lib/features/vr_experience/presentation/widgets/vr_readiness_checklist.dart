import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_training_session.dart';
import '../components/vr_core_components.dart';

class VrReadinessChecklist extends StatelessWidget {
  final VrTrainingSession session;

  const VrReadinessChecklist({
    super.key,
    required this.session,
  });

  @override
  Widget build(BuildContext context) {
    // Only display checklists during the launch phase
    if (session.phase != VrSessionPhase.launchReady) return const SizedBox.shrink();

    return Padding(
      padding: PharmSpacing.horizontalLg,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                'Status Kesiapan', // "Readiness Status"
                style: PharmTextStyles.h4.copyWith(
                  color: PharmColors.textPrimary,
                ),
              ),
              const Spacer(),
              if (session.isReadyToLaunch)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: PharmColors.success.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    'SIAP MELUNCUR', // "READY TO LAUNCH"
                    style: PharmTextStyles.overline.copyWith(
                      color: PharmColors.success,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: PharmSpacing.md),
          
          Container(
            decoration: BoxDecoration(
              color: PharmColors.surfaceLight,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: session.isReadyToLaunch 
                  ? PharmColors.primary.withOpacity(0.5) 
                  : PharmColors.cardBorder,
              ),
            ),
            child: Column(
              children: [
                VrChecklistItem(
                  title: 'Pre-Test Diselesaikan', // "Pre-Test Completed"
                  isCompleted: session.isPreTestPassed,
                  actionLabel: session.isPreTestPassed ? null : 'Ambil Tes', // "Take Test"
                  onAction: () {},
                ),
                const Divider(color: PharmColors.divider, height: 1),
                
                VrChecklistItem(
                  title: 'Perangkat VR Terhubung', // "VR Device Connected"
                  isCompleted: session.isDeviceConnected,
                  actionLabel: session.isDeviceConnected ? null : 'Hubungkan', // "Connect"
                  onAction: () {},
                ),
                const Divider(color: PharmColors.divider, height: 1),
                
                VrChecklistItem(
                  title: 'Panduan Orientasi Dibaca', // "Orientation Guide Read"
                  isCompleted: session.isUserReady,
                  actionLabel: session.isUserReady ? null : 'Tinjau', // "Review"
                  onAction: () {},
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Reused VrChecklistItem implementation from vr_core_components.dart
}
