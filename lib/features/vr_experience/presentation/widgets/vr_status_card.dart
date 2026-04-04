import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_connection_state.dart';

class VrStatusCard extends StatelessWidget {
  final VrDeviceSession session;

  const VrStatusCard({
    super.key,
    required this.session,
  });

  @override
  Widget build(BuildContext context) {
    if (session.isIdle) {
      // Don't show the detailed status box when completely idle/fresh
      return const SizedBox.shrink();
    }

    return Container(
      margin: PharmSpacing.horizontalLg,
      decoration: BoxDecoration(
        color: PharmColors.surfaceLight,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: _getBorderColor(),
          width: session.isConnected ? 1.5 : 1, // Draw the eye to connected state
        ),
      ),
      child: Column(
        children: [
          _buildHeaderRow(),
          
          if (session.isConnected && session.deviceName != null) ...[
            const Divider(color: PharmColors.divider, height: 1),
            _buildDeviceRow(),
          ],
          
          if (session.isFailed && session.errorMessage != null) ...[
            const Divider(color: PharmColors.divider, height: 1),
            _buildErrorRow(),
          ]
        ],
      ),
    );
  }

  Widget _buildHeaderRow() {
    return Padding(
      padding: PharmSpacing.allMd,
      child: Row(
        children: [
          // Dynamic status dot
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: _getStatusColor(),
              boxShadow: session.isConnected || session.isPairing
                ? [
                    BoxShadow(
                      color: _getStatusColor().withOpacity(0.4),
                      blurRadius: 6,
                      spreadRadius: 2,
                    ),
                  ]
                : null,
            ),
          ),
          const SizedBox(width: PharmSpacing.sm),
          
          Text(
            'STATUS KONEKSI', // "Connection Status"
            style: PharmTextStyles.label.copyWith(
              color: PharmColors.textSecondary,
              letterSpacing: 1.2,
            ),
          ),
          const Spacer(),
          
          Text(
            _getStatusLabel(),
            style: PharmTextStyles.subtitle.copyWith(
              color: _getStatusColor(),
              fontWeight: session.isConnected ? FontWeight.bold : FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDeviceRow() {
    return Padding(
      padding: PharmSpacing.allMd,
      child: Row(
        children: [
          Icon(Icons.headset_mic_rounded, size: 20, color: PharmColors.textSecondary), // Headset icon
          const SizedBox(width: PharmSpacing.sm),
          
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Perangkat', // "Device"
                  style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary),
                ),
                Text(
                  session.deviceName!,
                  style: PharmTextStyles.bodyBold.copyWith(color: PharmColors.textPrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          
          if (session.activeShortCode != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: PharmColors.surface,
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                session.activeShortCode!,
                style: PharmTextStyles.overline.copyWith(
                  color: PharmColors.textSecondary,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildErrorRow() {
    return Container(
      padding: PharmSpacing.allMd,
      decoration: BoxDecoration(
        color: PharmColors.error.withOpacity(0.05),
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(15), 
          bottomRight: Radius.circular(15),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.warning_amber_rounded, size: 18, color: PharmColors.error),
          const SizedBox(width: PharmSpacing.sm),
          Expanded(
            child: Text(
              session.errorMessage!,
              style: PharmTextStyles.bodySmall.copyWith(
                color: PharmColors.error,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Theme Mapping Helpers ──

  Color _getStatusColor() {
    switch (session.status) {
      case VrConnectionStatus.idle:
        return PharmColors.textSecondary;
      case VrConnectionStatus.pairing:
        return PharmColors.warning; // amber loading
      case VrConnectionStatus.connected:
        return PharmColors.success; // green ready
      case VrConnectionStatus.failed:
        return PharmColors.error;   // red fail
    }
  }

  Color _getBorderColor() {
    if (session.isConnected) return PharmColors.success.withOpacity(0.5);
    if (session.isFailed) return PharmColors.error.withOpacity(0.5);
    return PharmColors.cardBorder;
  }

  String _getStatusLabel() {
    switch (session.status) {
      case VrConnectionStatus.idle:
        return 'Terputus'; // Disconnected
      case VrConnectionStatus.pairing:
        return 'Mencari...'; // Searching/Pairing...
      case VrConnectionStatus.connected:
        return 'Tersambung'; // Connected
      case VrConnectionStatus.failed:
        return 'Gagal'; // Failed
    }
  }
}
