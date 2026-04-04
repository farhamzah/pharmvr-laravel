import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_training_session.dart';

class VrSessionHero extends StatelessWidget {
  final VrTrainingSession session;

  const VrSessionHero({
    super.key,
    required this.session,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: Theme.of(context).scaffoldBackgroundColor,
      padding: const EdgeInsets.fromLTRB(PharmSpacing.lg, PharmSpacing.xxl, PharmSpacing.lg, PharmSpacing.xl),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          _buildCinematicGraphic(context),
          const SizedBox(height: PharmSpacing.xl),
          
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: PharmColors.primary.withOpacity(0.15),
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: PharmColors.primary.withOpacity(0.3)),
            ),
            child: Text(
              _getBadgeText().toUpperCase(),
              style: PharmTextStyles.overline.copyWith(
                color: PharmColors.primary,
                letterSpacing: 2.0,
              ),
            ),
          ),
          const SizedBox(height: PharmSpacing.sm),
          
          Text(
            session.moduleTitle,
            style: PharmTextStyles.h2.copyWith(
              color: Theme.of(context).textTheme.displaySmall?.color,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildCinematicGraphic(BuildContext context) {
    IconData iconData;
    Color glowColor;
    double glowSize;

    switch (session.phase) {
      case VrSessionPhase.launchReady:
      case VrSessionPhase.failed:
        iconData = Icons.rocket_launch_rounded;
        glowColor = PharmColors.primary;
        glowSize = 40.0;
        break;
      case VrSessionPhase.inProgress:
        iconData = Icons.threesixty_rounded;
        glowColor = PharmColors.success;
        glowSize = 80.0; // Huge pulse
        break;
      case VrSessionPhase.completed:
        iconData = Icons.verified_rounded;
        glowColor = PharmColors.warning; // Gold/Trophy look
        glowSize = 60.0;
        break;
      case VrSessionPhase.interrupted:
        iconData = Icons.warning_amber_rounded;
        glowColor = PharmColors.error;
        glowSize = 40.0;
        break;
    }

    return Stack(
      alignment: Alignment.center,
      children: [
        // Cinematic Background Glow
        Container(
          width: 140,
          height: 140,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: glowColor.withOpacity(0.15),
                blurRadius: glowSize,
                spreadRadius: glowSize / 3,
              ),
            ],
          ),
        ),
        
        // Inner Emblem
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Theme.of(context).colorScheme.surface,
            border: Border.all(
              color: glowColor.withOpacity(0.5),
              width: session.phase == VrSessionPhase.inProgress ? 2 : 1,
            ),
          ),
          child: Center(
            child: Icon(
              iconData,
              size: 32,
              color: glowColor,
            ),
          ),
        ),
      ],
    );
  }

  String _getBadgeText() {
    switch (session.phase) {
      case VrSessionPhase.launchReady: return 'Misi Pelatihan'; // Training Mission
      case VrSessionPhase.inProgress: return 'Sesi Aktif'; // Active Session
      case VrSessionPhase.completed: return 'Misi Selesai'; // Mission Accomplished
      case VrSessionPhase.interrupted: return 'Sesi Terhenti'; // Session Interrupted
      case VrSessionPhase.failed: return 'Gagal Memulai'; // Launch Failed
    }
  }
}
