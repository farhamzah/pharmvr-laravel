import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_connection_state.dart';

class VrInstructionsBlock extends StatelessWidget {
  final VrConnectionStatus status;

  const VrInstructionsBlock({
    super.key,
    required this.status,
  });

  @override
  Widget build(BuildContext context) {
    if (status == VrConnectionStatus.connected) {
      // Hide detailed instructions once successfully connected
      return const SizedBox.shrink();
    }

    return Padding(
      padding: PharmSpacing.allLg,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Langkah Koneksi', // "Connection Steps"
            style: PharmTextStyles.h3.copyWith(
              color: PharmColors.textPrimary,
            ),
          ),
          const SizedBox(height: PharmSpacing.md),
          
          _buildStepRow(
            stepNumber: '1',
            title: 'Nyalakan Headset VR Anda', // "Turn on your VR Headset"
            subtitle: 'Pastikan baterai terisi penuh dan terhubung ke WiFi yang sama dengan ponsel Anda.', // "Ensure it's fully charged and connected to the same WiFi as your phone."
          ),
          const SizedBox(height: PharmSpacing.md),
          
          _buildStepRow(
            stepNumber: '2',
            title: 'Buka Aplikasi PharmVR', // "Open the PharmVR App"
            subtitle: 'Buka aplikasi di headset Anda dan masuk menggunakan Akun Perusahaan Anda.', // "Launch the app on your headset and log in using your Corporate Account."
          ),
          const SizedBox(height: PharmSpacing.md),
          
          _buildStepRow(
            stepNumber: '3',
            title: 'Mulai Sinkronisasi', // "Start Syncing"
            subtitle: 'Tekan "Mulai Koneksi" di bawah untuk mendeteksi perangkat aktif Anda.', // "Press 'Start Connection' below to detect your active devices."
            isLast: true,
          ),
          
          // Placeholder for Future QR code sync
          if (status == VrConnectionStatus.idle) ...[
            const SizedBox(height: PharmSpacing.xl),
            Center(
              child: TextButton.icon(
                onPressed: () {
                  // Future: open camera to scan QR from VR Lens
                },
                icon: const Icon(Icons.qr_code_scanner_rounded, color: PharmColors.primaryLight),
                label: Text(
                  'Gunakan Kode QR', // "Use QR Code"
                  style: PharmTextStyles.button.copyWith(
                    color: PharmColors.primaryLight,
                  ),
                ),
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                    side: BorderSide(color: PharmColors.primary.withOpacity(0.3)),
                  ),
                ),
              ),
            ),
          ]
        ],
      ),
    );
  }

  Widget _buildStepRow({
    required String stepNumber,
    required String title,
    required String subtitle,
    bool isLast = false,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Step indicator column (Number + line)
        Column(
          children: [
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: PharmColors.surfaceLight,
                shape: BoxShape.circle,
                border: Border.all(
                  color: PharmColors.cardBorder,
                ),
              ),
              child: Center(
                child: Text(
                  stepNumber,
                  style: PharmTextStyles.label.copyWith(
                    color: PharmColors.primaryLight,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
            if (!isLast)
              Container(
                width: 1,
                height: 48, // approximate height for 2 lines of subtitle
                color: PharmColors.divider,
              ),
          ],
        ),
        const SizedBox(width: PharmSpacing.md),
        
        // Content column
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(top: 4.0), // Align with number center visually
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: PharmTextStyles.subtitle.copyWith(
                    color: PharmColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: PharmTextStyles.bodySmall.copyWith(
                    color: PharmColors.textSecondary,
                    height: 1.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
