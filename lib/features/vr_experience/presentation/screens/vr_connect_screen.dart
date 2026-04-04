import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/core/theme/pharm_spacing.dart';
import 'package:pharmvrpro/features/vr_experience/presentation/providers/vr_connection_provider.dart';

class VrConnectScreen extends ConsumerStatefulWidget {
  final String? moduleSlug;
  final String? moduleTitle;
  const VrConnectScreen({super.key, this.moduleSlug, this.moduleTitle});

  @override
  ConsumerState<VrConnectScreen> createState() => _VrConnectScreenState();
}

class _VrConnectScreenState extends ConsumerState<VrConnectScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  Timer? _timeoutTimer;
  bool _isTimedOut = false;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    // Generate QR session on screen open
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _startSession();
    });
  }

  void _startSession() {
    setState(() {
      _isTimedOut = false;
    });
    
    final vrState = ref.read(vrConnectionProvider);
    if (vrState.pairingCode == null) {
      ref.read(vrConnectionProvider.notifier).initiatePairing(widget.moduleSlug ?? 'cleanroom-gowning');
    }
    
    _timeoutTimer?.cancel();
    _timeoutTimer = Timer(const Duration(seconds: 45), () {
      if (mounted && ref.read(vrConnectionProvider).status != VrConnectionStatus.ready) {
        setState(() => _isTimedOut = true);
      }
    });
  }

  @override
  void dispose() {
    _pulseController.dispose();
    _timeoutTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final vrState = ref.watch(vrConnectionProvider);

    // Listen for pairing success
    ref.listen(vrConnectionProvider, (previous, next) {
      if (previous?.status != VrConnectionStatus.ready &&
          next.status == VrConnectionStatus.ready) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white, size: 18),
                const SizedBox(width: 8),
                Expanded(child: Text('${next.headsetName ?? 'Meta Quest 3'} Terhubung!')),
              ],
            ),
            backgroundColor: PharmColors.success,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
        // Navigate to VR Launch
        Future.delayed(const Duration(milliseconds: 800), () {
          if (mounted) context.go('/vr/launch');
        });
      }
    });

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Stack(
        children: [
          // Background glow
          Positioned(
            top: -80,
            right: -60,
            child: AnimatedBuilder(
              animation: _pulseController,
              builder: (_, __) => Container(
                width: 250,
                height: 250,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      PharmColors.primary.withOpacity(0.06 * _pulseController.value),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                // App bar
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  child: Row(
                    children: [
                      IconButton(
                        icon: Icon(Icons.arrow_back_ios, color: Theme.of(context).textTheme.labelSmall?.color, size: 20),
                        onPressed: () => context.pop(),
                      ),
                      const Spacer(),
                      // Status pill
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: _statusColor(vrState.status).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: _statusColor(vrState.status).withOpacity(0.25)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              vrState.status == VrConnectionStatus.ready ? Icons.headset : Icons.qr_code_scanner,
                              color: _statusColor(vrState.status),
                              size: 14,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              _statusText(vrState.status),
                              style: PharmTextStyles.caption.copyWith(
                                color: _statusColor(vrState.status),
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                    ],
                  ),
                ),

                // Content
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 28),
                    child: Column(
                      children: [
                        const SizedBox(height: 16),
                        Text(
                          'KONEKSI VR',
                          style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 3),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Hubungkan Meta Quest 3',
                          style: PharmTextStyles.h2.copyWith(color: Theme.of(context).textTheme.displaySmall?.color),
                          textAlign: TextAlign.center,
                        ),

                        if (widget.moduleTitle != null) ...[
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            decoration: BoxDecoration(
                              color: PharmColors.primary.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.layers_outlined, size: 16, color: PharmColors.primary),
                                const SizedBox(width: 8),
                                Text(
                                  widget.moduleTitle!,
                                  style: PharmTextStyles.bodySmall.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 12),
                        Text(
                           'Scan QR Code atau masukkan kode pairing di bawah ini pada Meta Quest 3 Anda.',
                          style: PharmTextStyles.bodyMedium.copyWith(
                            color: Theme.of(context).textTheme.bodySmall?.color,
                            height: 1.5,
                          ),
                          textAlign: TextAlign.center,
                        ),

                        const SizedBox(height: 28),

                        // QR Code Card
                        if (vrState.status != VrConnectionStatus.ready && !_isTimedOut)
                          _buildPairingCard(vrState),

                        // Timeout state
                        if (_isTimedOut && vrState.status != VrConnectionStatus.ready)
                          _buildTimeoutCard(),

                        // Connected state
                        if (vrState.status == VrConnectionStatus.ready)
                          _buildConnectedCard(vrState),

                        const SizedBox(height: 24),

                        // Instructions
                        _buildInstructions(vrState),

                        const SizedBox(height: 24),

                        // Manual check status
                        if (vrState.status == VrConnectionStatus.pairing)
                           _buildCheckButton(),

                        const SizedBox(height: 30),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPairingCard(VrConnectionState vrState) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
        boxShadow: [
          BoxShadow(color: PharmColors.primary.withOpacity(0.05), blurRadius: 24, spreadRadius: 2),
        ],
      ),
      child: Column(
        children: [
          // Pairing Code Display
          if (vrState.pairingCode != null) ...[
            Text('PAIRING CODE', style: PharmTextStyles.overline.copyWith(color: PharmColors.textTertiary)),
            const SizedBox(height: 8),
            Text(
              vrState.pairingCode!,
              style: PharmTextStyles.h1.copyWith(
                color: PharmColors.primary,
                fontSize: 48,
                letterSpacing: 8,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),
            Row(children: [
              Expanded(child: Divider(color: Theme.of(context).dividerColor.withOpacity(0.5))),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text('ATAU SCAN QR', style: PharmTextStyles.caption.copyWith(color: PharmColors.textTertiary)),
              ),
              Expanded(child: Divider(color: Theme.of(context).dividerColor.withOpacity(0.5))),
            ]),
            const SizedBox(height: 20),
          ],

          // QR Code
          AnimatedBuilder(
            animation: _pulseController,
            builder: (_, __) => Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: PharmColors.primary.withOpacity(0.3), width: 2),
              ),
              child: QrImageView(
                data: vrState.qrPayload.isNotEmpty ? vrState.qrPayload : 'pharmvr://pair?demo=1',
                version: QrVersions.auto,
                size: 160,
                eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: Color(0xFF1A1A2E)),
                dataModuleStyle: const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: Color(0xFF1A1A2E)),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Waiting indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: PharmColors.primary)),
              const SizedBox(width: 10),
              Text(
                'Menunggu headset terhubung...',
                style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildConnectedCard(VrConnectionState vrState) {
    return Container(
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: PharmColors.success.withOpacity(0.06),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: PharmColors.success.withOpacity(0.2)),
      ),
      child: Column(
        children: [
          Container(
            width: 80, height: 80,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: PharmColors.success.withOpacity(0.1),
              border: Border.all(color: PharmColors.success.withOpacity(0.3), width: 2),
            ),
            child: const Icon(Icons.check_circle, color: PharmColors.success, size: 40),
          ),
          const SizedBox(height: 16),
          Text(
            'Headset Terhubung!',
            style: PharmTextStyles.h3.copyWith(color: PharmColors.success),
          ),
          const SizedBox(height: 6),
          Text(
            vrState.headsetName ?? 'Meta Quest 3 SIAP DIGUNAKAN',
            style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color),
          ),
          const SizedBox(height: 20),
          // Connection details
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.battery_std, size: 14, color: PharmColors.success),
              const SizedBox(width: 4),
              Text('85%', style: PharmTextStyles.caption.copyWith(color: PharmColors.success)),
              const SizedBox(width: 16),
              const Icon(Icons.wifi, size: 14, color: PharmColors.success),
              const SizedBox(width: 4),
              Text('Stabil', style: PharmTextStyles.caption.copyWith(color: PharmColors.success)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInstructions(VrConnectionState vrState) {
    if (vrState.status == VrConnectionStatus.ready) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Cara Menghubungkan', style: PharmTextStyles.h4),
          const SizedBox(height: 14),
          _instructionStep(1, 'Gunakan Meta Quest 3 Anda'),
          _instructionStep(2, 'Buka aplikasi PharmVR di headset'),
          _instructionStep(3, 'Pilih "Connect Device" dan masukkan kode pairing'),
          _instructionStep(4, 'Atau arahkan kamera headset ke QR Code di layar ini'),
        ],
      ),
    );
  }

  Widget _instructionStep(int number, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 24, height: 24,
            decoration: BoxDecoration(shape: BoxShape.circle, color: PharmColors.primary.withOpacity(0.1)),
            child: Center(child: Text('$number', style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold))),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(text, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.4))),
        ],
      ),
    );
  }

  Widget _buildCheckButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: OutlinedButton.icon(
        onPressed: () => ref.read(vrConnectionProvider.notifier).syncConnectionStatus(),
        icon: const Icon(Icons.sync),
        label: const Text('CEK STATUS KONEKSI'),
        style: OutlinedButton.styleFrom(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          side: const BorderSide(color: PharmColors.primary),
        ),
      ),
    );
  }

  Widget _buildTimeoutCard() {
    return Container(
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: PharmColors.error.withOpacity(0.06),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: PharmColors.error.withOpacity(0.2)),
      ),
      child: Column(
        children: [
          Container(
            width: 72, height: 72,
            decoration: BoxDecoration(shape: BoxShape.circle, color: PharmColors.error.withOpacity(0.1)),
            child: const Icon(Icons.timer_off_outlined, color: PharmColors.error, size: 32),
          ),
          const SizedBox(height: 20),
          Text('Waktu Habis', style: PharmTextStyles.h3.copyWith(color: PharmColors.error)),
          const SizedBox(height: 10),
          Text(
            'Headset tidak merespons dalam waktu yang ditentukan. Pastikan headset aktif dan terhubung internet.',
            style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.5),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _startSession,
              icon: const Icon(Icons.refresh),
              label: const Text('COBA LAGI'),
              style: ElevatedButton.styleFrom(
                backgroundColor: PharmColors.error,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Color _statusColor(VrConnectionStatus s) {
    switch (s) {
      case VrConnectionStatus.ready: return PharmColors.success;
      case VrConnectionStatus.inProgress: return PharmColors.info;
      case VrConnectionStatus.pairing: return PharmColors.warning;
      case VrConnectionStatus.offline: return PharmColors.error;
      case VrConnectionStatus.idle: return PharmColors.textTertiary;
    }
  }

  String _statusText(VrConnectionStatus s) {
    switch (s) {
      case VrConnectionStatus.ready: return 'Terhubung';
      case VrConnectionStatus.pairing: return 'Menunggu Pairing';
      case VrConnectionStatus.inProgress: return 'Sesi Aktif';
      case VrConnectionStatus.offline: return 'Terputus';
      case VrConnectionStatus.idle: return 'Belum Terhubung';
    }
  }
}

