import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_connection_state.dart';

class VrConnectionActions extends StatelessWidget {
  final VrConnectionStatus status;
  final VoidCallback onStartPairing;
  final VoidCallback onCancelPairing;
  final VoidCallback onRetry;
  final VoidCallback onContinue;

  const VrConnectionActions({
    super.key,
    required this.status,
    required this.onStartPairing,
    required this.onCancelPairing,
    required this.onRetry,
    required this.onContinue,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: PharmSpacing.allLg,
      child: Column(
        children: [
          _buildPrimaryAction(),
          const SizedBox(height: PharmSpacing.md),
          _buildSecondaryAction(),
        ],
      ),
    );
  }

  Widget _buildPrimaryAction() {
    switch (status) {
      case VrConnectionStatus.idle:
        return SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: onStartPairing,
            style: ElevatedButton.styleFrom(
              backgroundColor: PharmColors.primaryDark,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              shadowColor: PharmColors.accentGlow,
              elevation: 4,
            ),
            child: Text(
              'Mulai Koneksi', // "Start Connection"
              style: PharmTextStyles.button.copyWith(
                color: PharmColors.background,
                fontSize: 16,
              ),
            ),
          ),
        );
        
      case VrConnectionStatus.pairing:
        return SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: onCancelPairing,
            style: ElevatedButton.styleFrom(
              backgroundColor: PharmColors.surfaceLight,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(color: PharmColors.divider),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: PharmColors.textSecondary,
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  'Batalkan', // "Cancel"
                  style: PharmTextStyles.button.copyWith(
                    color: PharmColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
        );
        
      case VrConnectionStatus.connected:
        return SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: onContinue,
            style: ElevatedButton.styleFrom(
              backgroundColor: PharmColors.success,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              shadowColor: PharmColors.success.withOpacity(0.3),
              elevation: 4,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.panorama_rounded, color: PharmColors.background, size: 22),
                const SizedBox(width: 8),
                Text(
                  'Mulai Pelatihan CPOB', // "Start GMP Training"
                  style: PharmTextStyles.button.copyWith(
                    color: PharmColors.background,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
          ),
        );
        
      case VrConnectionStatus.failed:
        return SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: onRetry,
            style: ElevatedButton.styleFrom(
              backgroundColor: PharmColors.primaryDark,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: Text(
              'Coba Lagi', // "Retry"
              style: PharmTextStyles.button.copyWith(
                color: PharmColors.background,
                fontSize: 16,
              ),
            ),
          ),
        );
    }
  }

  Widget _buildSecondaryAction() {
    if (status == VrConnectionStatus.connected) {
      return TextButton(
        onPressed: onCancelPairing, // Reuse cancel to disconnect
        child: Text(
          'Putuskan Koneksi', // "Disconnect"
          style: PharmTextStyles.button.copyWith(
            color: PharmColors.textSecondary,
          ),
        ),
      );
    }

    if (status == VrConnectionStatus.failed || status == VrConnectionStatus.pairing) {
      return TextButton.icon(
        onPressed: () {
          // Future: open support/troubleshooting modal
        },
        icon: Icon(Icons.help_outline_rounded, size: 18, color: PharmColors.textTertiary),
        label: Text(
          'Butuh bantuan?', // "Need Help?"
          style: PharmTextStyles.bodyMedium.copyWith(
            color: PharmColors.textTertiary,
            decoration: TextDecoration.underline,
          ),
        ),
      );
    }
    
    return const SizedBox(height: 48); // Fixed spacing to prevent jumping
  }
}
