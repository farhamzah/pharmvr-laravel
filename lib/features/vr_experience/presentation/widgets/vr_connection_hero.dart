import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_connection_state.dart';

class VrConnectionHero extends StatelessWidget {
  final VrConnectionStatus status;

  const VrConnectionHero({
    super.key,
    required this.status,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: Theme.of(context).scaffoldBackgroundColor,
      padding: const EdgeInsets.fromLTRB(PharmSpacing.lg, PharmSpacing.xxl, PharmSpacing.lg, PharmSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // ── Hero VR Graphic Strategy ──
          // When connected = Primary Glowing
          // When Failed = Error Red
          // When Pairing = Pulsing gradient
          // When Idle = Muted slate blue
          _buildVrGraphic(context),
          const SizedBox(height: PharmSpacing.xl),
          
          Text(
            _getHeroTitle(),
            style: PharmTextStyles.h2.copyWith(
              color: Theme.of(context).textTheme.displaySmall?.color,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: PharmSpacing.sm),
          
          Text(
            _getHeroSubtitle(),
            style: PharmTextStyles.bodyMedium.copyWith(
              color: Theme.of(context).textTheme.bodySmall?.color,
              height: 1.5,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildVrGraphic(BuildContext context) {
    Color ringColor;
    Color iconColor;
    
    switch (status) {
      case VrConnectionStatus.idle:
        ringColor = Theme.of(context).dividerColor;
        iconColor = Theme.of(context).textTheme.labelSmall?.color ?? PharmColors.textSecondary;
        break;
      case VrConnectionStatus.pairing:
        ringColor = PharmColors.info; // Pulsing simulated via color choice here
        iconColor = PharmColors.info;
        break;
      case VrConnectionStatus.connected:
        ringColor = PharmColors.primary.withOpacity(0.3);
        iconColor = PharmColors.primary;
        break;
      case VrConnectionStatus.failed:
        ringColor = PharmColors.error.withOpacity(0.3);
        iconColor = PharmColors.error;
        break;
    }

    return Stack(
      alignment: Alignment.center,
      children: [
        // Outer glow
        if (status == VrConnectionStatus.connected)
          Container(
            width: 140,
            height: 140,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: PharmColors.primary.withOpacity(0.15),
                  blurRadius: 40,
                  spreadRadius: 10,
                ),
              ],
            ),
          ),
          
        // Main Ring
        Container(
          width: 120,
          height: 120,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(
              color: ringColor,
              width: status == VrConnectionStatus.pairing ? 2 : 1,
            ),
            gradient: RadialGradient(
              colors: [
                Theme.of(context).colorScheme.surface.withOpacity(0.5),
                Theme.of(context).colorScheme.surface,
              ],
            ),
          ),
          child: Center(
            child: Icon(
              Icons.panorama_rounded, // Best built-in VR icon
              size: 52,
              color: iconColor,
            ),
          ),
        ),
      ],
    );
  }

  String _getHeroTitle() {
    switch (status) {
      case VrConnectionStatus.idle:
        return 'Experience PharmVR';
      case VrConnectionStatus.pairing:
        return 'Menghubungkan...'; // Connecting...
      case VrConnectionStatus.connected:
        return 'VR Terhubung'; // VR Connected
      case VrConnectionStatus.failed:
        return 'Koneksi Gagal'; // Connection Failed
    }
  }

  String _getHeroSubtitle() {
    switch (status) {
      case VrConnectionStatus.idle:
        return 'Hubungkan headset VR Anda untuk memulai simulasi pelatihan CPOB interaktif.';
      case VrConnectionStatus.pairing:
        return 'Mencari perangkat VR di jaringan Anda atau melalui kode pairing.';
      case VrConnectionStatus.connected:
        return 'Headset Anda siap. Sesi pembelajaran Anda akan disinkronkan otomatis.';
      case VrConnectionStatus.failed:
        return 'Tidak dapat menemukan headset VR. Pastikan perangkat Anda menyala dan terhubung ke internet.';
    }
  }
}
