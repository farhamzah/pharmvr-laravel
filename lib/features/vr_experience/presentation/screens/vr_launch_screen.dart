import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../providers/vr_connection_provider.dart';
import '../providers/vr_readiness_provider.dart';

class VrLaunchScreen extends ConsumerStatefulWidget {
  const VrLaunchScreen({super.key});

  @override
  ConsumerState<VrLaunchScreen> createState() => _VrLaunchScreenState();
}

class _VrLaunchScreenState extends ConsumerState<VrLaunchScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 3),
    )..repeat(reverse: true);

    // Initial status sync
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(vrConnectionProvider.notifier).syncConnectionStatus();
    });
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final vrState = ref.watch(vrConnectionProvider);
    final moduleSlug = vrState.activeModuleSlug ?? 'cleanroom-gowning';
    final readinessState = ref.watch(vrReadinessProvider);

    // Listen for connection changes to refresh readiness
    ref.listen(vrConnectionProvider, (prev, next) {
      if (prev?.status != next.status) {
        ref.read(vrReadinessProvider.notifier).fetchReadiness(moduleSlug);
      }
    });

    // Auto-fetch on first build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!readinessState.isLoading && readinessState.checklist.isEmpty) {
        ref.read(vrReadinessProvider.notifier).fetchReadiness(moduleSlug);
      }
    });

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Stack(
        children: [
          // Animated background glow
          Positioned.fill(
            child: AnimatedBuilder(
              animation: _pulseController,
              builder: (_, __) => Container(
                decoration: BoxDecoration(
                  gradient: RadialGradient(
                    center: const Alignment(0, -0.5),
                    radius: 1.5,
                    colors: [
                      _statusColor(vrState.status).withOpacity(0.08 * _pulseController.value),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
          ),
          
          // Connection Lost Overlay
          if (vrState.status == VrConnectionStatus.offline)
            _buildConnectionLostOverlay(),

          SafeArea(
            child: Column(
              children: [
                // App bar
                _buildAppBar(vrState),

                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 8),
                        // Hero banner
                        _buildHeroBanner(vrState),
                        const SizedBox(height: 24),
                        // Module info
                        _buildModuleInfo(),
                        const SizedBox(height: 24),
                        // Readiness checklist
                        _buildReadinessChecklist(vrState, readinessState, moduleSlug),
                        const SizedBox(height: 28),
                        // CTAs
                        _buildCTAs(vrState, readinessState, moduleSlug),
                        const SizedBox(height: 20),
                        // Help
                        _buildHelpSection(),
                        const SizedBox(height: 40),
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

  // ── App Bar ──
  Widget _buildAppBar(VrConnectionState vrState) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: Row(
        children: [
          IconButton(
            icon: Icon(Icons.arrow_back_ios, color: Theme.of(context).textTheme.labelSmall?.color, size: 20),
            onPressed: () => context.pop(),
          ),
          const Spacer(),
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
                Icon(_statusIcon(vrState.status), color: _statusColor(vrState.status), size: 14),
                const SizedBox(width: 6),
                Text(
                  _statusLabel(vrState.status),
                  style: PharmTextStyles.caption.copyWith(
                    color: _statusColor(vrState.status),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
    );
  }

  // ── Hero Banner ──
  Widget _buildHeroBanner(VrConnectionState vrState) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            PharmColors.primary.withOpacity(0.15),
            Theme.of(context).colorScheme.surface,
          ],
        ),
        border: Border.all(color: PharmColors.primary.withOpacity(0.1)),
      ),
      child: Column(
        children: [
          // Animated icon
          AnimatedBuilder(
            animation: _pulseController,
            builder: (_, __) {
              final scale = 1.0 + 0.05 * _pulseController.value;
              return Transform.scale(
                scale: scale,
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: _statusColor(vrState.status).withOpacity(0.1),
                    border: Border.all(
                      color: _statusColor(vrState.status).withOpacity(0.3 + 0.3 * _pulseController.value),
                      width: 2,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: _statusColor(vrState.status).withOpacity(0.15 * _pulseController.value),
                        blurRadius: 24,
                        spreadRadius: 4,
                      ),
                    ],
                  ),
                  child: Icon(
                    vrState.status == VrConnectionStatus.inProgress ? Icons.sensors : Icons.view_in_ar,
                    size: 36,
                    color: _statusColor(vrState.status),
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 16),
          Text(
            'VR TRAINING',
            style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 3),
          ),
          const SizedBox(height: 6),
          Text(
            vrState.status == VrConnectionStatus.inProgress
                ? 'Pelatihan Sedang Berjalan'
                : 'Pharmaceutical Cleanroom',
            style: PharmTextStyles.h2.copyWith(color: Theme.of(context).textTheme.displaySmall?.color),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  // ── Module Info ──
  Widget _buildModuleInfo() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: PharmColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text('MOD-01', style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.w600)),
              ),
              const Spacer(),
              Text('~15 min', style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
            ],
          ),
          const SizedBox(height: 12),
          Text('Protocol Gowning Cleanroom', style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
          const SizedBox(height: 6),
          Text(
            'Latih urutan gowning lengkap untuk masuk ke cleanroom ISO Kelas 5 sesuai GMP Annex 1.',
            style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.5),
          ),
          const SizedBox(height: 14),
          // Tags
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              _tag('GMP Annex 1', Icons.verified_outlined),
              _tag('Intermediate', Icons.signal_cellular_alt),
              _tag('Hands-on', Icons.pan_tool_outlined),
            ],
          ),
        ],
      ),
    );
  }

  Widget _tag(String label, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: Theme.of(context).textTheme.labelSmall?.color),
          const SizedBox(width: 5),
          Text(label, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.bodySmall?.color)),
        ],
      ),
    );
  }

  // ── Readiness Checklist ──
  Widget _buildReadinessChecklist(VrConnectionState vrState, VrReadinessState readiness, String slug) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Checklist Kesiapan', style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
            if (readiness.isLoading)
              const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)),
          ],
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
          ),
          child: Column(
            children: [
              if (readiness.checklist.isEmpty && !readiness.isLoading)
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Text('Gagal memuat data kesiapan.', style: PharmTextStyles.bodySmall),
                ),
              
              ...readiness.checklist.map((item) {
                Widget? action;
                if (!item.status) {
                  if (item.label.contains('Pre-test')) {
                    action = _actionButton('Kerjakan', () => context.push('/assessment/intro/$slug/pre'));
                  } else if (item.label.contains('Quest 3')) {
                    action = _actionButton('Hubungkan', () => context.push('/vr/connect'));
                  }
                }
                
                return Column(
                  children: [
                    _checkItem(item.label, item.status, _iconForLabel(item.label), action: action),
                    if (item != readiness.checklist.last) _divider(),
                  ],
                );
              }),
              
              if (readiness.blockingReasons.isNotEmpty)
                _blockingReasonsCard(readiness.blockingReasons),
                
              _aiRecommendation(),
            ],
          ),
        ),
      ],
    );
  }

  Widget _actionButton(String label, VoidCallback onPressed) {
    return TextButton(
      onPressed: onPressed,
      style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
      child: Text(label, style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold)),
    );
  }

  IconData _iconForLabel(String label) {
    if (label.contains('Pre-test')) return Icons.quiz_outlined;
    if (label.contains('Quest 3')) return Icons.headset;
    if (label.contains('Sesi Aktif')) return Icons.timer_outlined;
    return Icons.check_circle_outline;
  }

  Widget _checkItem(String label, bool done, IconData icon, {Widget? action}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          Container(
            width: 28, height: 28,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: done ? PharmColors.success.withOpacity(0.12) : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight),
            ),
            child: Icon(
              done ? Icons.check : icon,
              size: 14,
              color: done ? PharmColors.success : Theme.of(context).textTheme.labelSmall?.color,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              label,
              style: PharmTextStyles.bodySmall.copyWith(
                color: done ? Theme.of(context).textTheme.displaySmall?.color : Theme.of(context).textTheme.labelSmall?.color,
              ),
            ),
          ),
          if (action != null) action,
          if (action == null && done)
            const Icon(Icons.check_circle, size: 16, color: PharmColors.success),
        ],
      ),
    );
  }

  Widget _blockingReasonsCard(List<String> reasons) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: PharmColors.error.withOpacity(0.05),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: reasons.map((r) => Padding(
          padding: const EdgeInsets.only(bottom: 4),
          child: Row(
            children: [
              const Icon(Icons.error_outline, size: 14, color: PharmColors.error),
              const SizedBox(width: 8),
              Expanded(child: Text(r, style: PharmTextStyles.caption.copyWith(color: PharmColors.error))),
            ],
          ),
        )).toList(),
      ),
    );
  }

  Widget _aiRecommendation() {
    return Container(
      margin: const EdgeInsets.all(12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: PharmColors.primary.withOpacity(0.05),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: PharmColors.primary.withOpacity(0.1)),
      ),
      child: Row(
        children: [
          const Icon(Icons.auto_awesome, size: 16, color: PharmColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Tinjau kembali urutan gowning sebelum meluncurkan VR untuk hasil maksimal.',
              style: PharmTextStyles.caption.copyWith(color: PharmColors.primary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _divider() => Divider(height: 1, indent: 56, color: Theme.of(context).dividerColor.withOpacity(0.5));

  // ── CTAs ──
  Widget _buildCTAs(VrConnectionState vrState, VrReadinessState readiness, String slug) {
    final isConnected = vrState.status == VrConnectionStatus.ready;
    final isActive = vrState.status == VrConnectionStatus.inProgress;
    final canLaunch = isConnected && readiness.isEligible;

    return Column(
      children: [
        if (isActive) ...[
          _buildTelemetry(vrState),
          const SizedBox(height: 16),
          _buildEndSessionButton(),
        ] else ...[
          SizedBox(
            width: double.infinity,
            height: 52,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                gradient: canLaunch
                    ? const LinearGradient(colors: [PharmColors.primary, PharmColors.primaryDark])
                    : null,
                color: canLaunch ? null : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight),
                boxShadow: canLaunch
                    ? [BoxShadow(color: PharmColors.primary.withOpacity(0.25), blurRadius: 16, offset: const Offset(0, 4))]
                    : [],
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  borderRadius: BorderRadius.circular(14),
                  onTap: canLaunch
                      ? () => ref.read(vrConnectionProvider.notifier).launchSession(slug)
                      : (!isConnected ? () => context.push('/vr/connect') : null),
                  child: Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.play_circle_fill,
                          color: canLaunch ? Colors.white : Theme.of(context).textTheme.labelSmall?.color,
                          size: 24,
                        ),
                        const SizedBox(width: 10),
                        Text(
                          canLaunch ? 'LUNCURKAN PELATIHAN VR' : (isConnected ? 'LENGKAPI CHECKLIST' : 'HUBUNGKAN HEADSET'),
                          style: PharmTextStyles.button.copyWith(
                            color: canLaunch ? Colors.white : Theme.of(context).textTheme.labelSmall?.color,
                            letterSpacing: 1,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildTelemetry(VrConnectionState vrState) {
    final sessionData = ref.watch(currentSessionProvider);

    return sessionData.when(
      data: (session) {
        if (session == null) return const SizedBox.shrink();
        
        final mins = (session.timeElapsedSeconds ?? 0) ~/ 60;
        final secs = (session.timeElapsedSeconds ?? 0) % 60;
        final timeStr = '${mins.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';

        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
          ),
          child: Column(
            children: [
              _telemetryRow('Koneksi Headset', 'Stabil', PharmColors.success),
              Divider(height: 20, color: Theme.of(context).dividerColor),
              _telemetryRow('Waktu Sesi', timeStr, PharmColors.primary),
              Divider(height: 20, color: Theme.of(context).dividerColor),
              _telemetryRow('Status', session.currentStepName ?? 'In Progress', PharmColors.info),
            ],
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _telemetryRow(String label, String value, Color color) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color)),
        Text(value, style: PharmTextStyles.label.copyWith(color: color, fontWeight: FontWeight.w700)),
      ],
    );
  }

  Widget _buildEndSessionButton() {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: OutlinedButton.icon(
        onPressed: () => ref.read(vrConnectionProvider.notifier).syncConnectionStatus(),
        icon: const Icon(Icons.refresh, size: 18),
        label: Text(
          'MUAT ULANG STATUS',
          style: PharmTextStyles.label.copyWith(color: PharmColors.primary, fontWeight: FontWeight.w600, letterSpacing: 1),
        ),
        style: OutlinedButton.styleFrom(
          side: BorderSide(color: PharmColors.primary.withOpacity(0.3)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
    );
  }

  // ── Help ──
  Widget _buildHelpSection() {
    return Center(
      child: TextButton.icon(
        onPressed: () {},
        icon: Icon(Icons.help_outline, color: Theme.of(context).textTheme.labelSmall?.color, size: 16),
        label: Text(
          'Butuh bantuan koneksi?',
          style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
        ),
      ),
    );
  }

  Widget _buildConnectionLostOverlay() {
    return Container(
      color: Colors.black.withOpacity(0.85),
      padding: const EdgeInsets.symmetric(horizontal: 40),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 80, height: 80,
              decoration: BoxDecoration(shape: BoxShape.circle, color: PharmColors.error.withOpacity(0.1)),
              child: const Icon(Icons.headset_off, color: PharmColors.error, size: 40),
            ),
            const SizedBox(height: 24),
            Text('Koneksi Terputus', style: PharmTextStyles.h2.copyWith(color: Colors.white)),
            const SizedBox(height: 12),
            Text(
              'Sambungan dengan Meta Quest 3 terputus. Pastikan headset tetap menyala dan aplikasi PharmVR terbuka.',
              style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white70, height: 1.5),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => context.push('/vr/connect'),
                icon: const Icon(Icons.qr_code_scanner),
                label: const Text('HUBUNGKAN KEMBALI'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),
            const SizedBox(height: 16),
            TextButton(
              onPressed: () => context.go('/dashboard'),
              child: Text('Kembali ke Dashboard', style: PharmTextStyles.label.copyWith(color: Colors.white54)),
            ),
          ],
        ),
      ),
    );
  }

  // ── Helpers ──
  Color _statusColor(VrConnectionStatus s) {
    switch (s) {
      case VrConnectionStatus.ready: return PharmColors.success;
      case VrConnectionStatus.inProgress: return PharmColors.info;
      case VrConnectionStatus.pairing: return PharmColors.warning;
      case VrConnectionStatus.offline: return PharmColors.error;
      case VrConnectionStatus.idle: return PharmColors.textTertiary;
    }
  }

  IconData _statusIcon(VrConnectionStatus s) {
    switch (s) {
      case VrConnectionStatus.ready: return Icons.headset;
      case VrConnectionStatus.inProgress: return Icons.sensors;
      case VrConnectionStatus.pairing: return Icons.sync;
      case VrConnectionStatus.offline: return Icons.headset_off;
      case VrConnectionStatus.idle: return Icons.view_in_ar;
    }
  }

  String _statusLabel(VrConnectionStatus s) {
    switch (s) {
      case VrConnectionStatus.ready: return 'Siap';
      case VrConnectionStatus.inProgress: return 'Sesi Aktif';
      case VrConnectionStatus.pairing: return 'Pairing...';
      case VrConnectionStatus.offline: return 'Offline';
      case VrConnectionStatus.idle: return 'Belum Pairing';
    }
  }
}
