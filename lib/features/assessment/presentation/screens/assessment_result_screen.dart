import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/assessment_models.dart';
import '../providers/assessment_provider.dart';

class AssessmentResultScreen extends ConsumerStatefulWidget {
  final String moduleId;
  final String type;
  const AssessmentResultScreen({super.key, required this.moduleId, required this.type});

  @override
  ConsumerState<AssessmentResultScreen> createState() => _AssessmentResultScreenState();
}

class _AssessmentResultScreenState extends ConsumerState<AssessmentResultScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final state = ref.read(assessmentProvider);
      if (state.result == null && state.attemptId != null) {
        ref.read(assessmentProvider.notifier).loadResult(state.attemptId!);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(assessmentProvider);
    final result = state.result;

    if (state.error != null && result == null) {
      return Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        appBar: AppBar(title: const Text('Assessment Result')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: PharmColors.error, size: 48),
              const SizedBox(height: 16),
              Text('Gagal memuat hasil: ${state.error}', style: PharmTextStyles.bodyMedium),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => context.go('/dashboard'),
                child: const Text('KEMBALI KE DASHBOARD'),
              ),
            ],
          ),
        ),
      );
    }

    if (result == null) {
      return Scaffold(
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        body: const Center(child: CircularProgressIndicator(color: PharmColors.primary)),
      );
    }

    final isPre = result.type == 'pre_test' || result.type == 'pretest';
    final mins = result.timeTakenSeconds ~/ 60;
    final secs = result.timeTakenSeconds % 60;
    final color = result.passed ? PharmColors.success : PharmColors.error;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          child: Column(
            children: [
              const SizedBox(height: 20),

              // Score Ring
              SizedBox(
                width: 160, height: 160,
                child: CustomPaint(
                  painter: _ScoreRingPainter(percentage: result.score / 100, color: color),
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('${result.score}%', style: PharmTextStyles.h1.copyWith(color: color, fontSize: 38)),
                        Text(result.passed ? 'LULUS' : 'PERLU EVALUASI', style: PharmTextStyles.overline.copyWith(color: color, letterSpacing: 2)),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Module badge
              if (state.assessment != null)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: PharmColors.primary.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
                  ),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(Icons.view_in_ar, color: PharmColors.primary, size: 14),
                    const SizedBox(width: 6),
                    Text(state.assessment!.moduleTitle, style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold)),
                  ]),
                ),
              const SizedBox(height: 6),
              Text(isPre ? 'Pre-Test Selesai' : 'Post-Test Selesai', style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
              const SizedBox(height: 24),

              // Interpretation card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.06),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: color.withOpacity(0.12)),
                ),
                child: Row(children: [
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), color: color.withOpacity(0.12)),
                    child: Icon(result.passed ? Icons.emoji_events_outlined : Icons.school_outlined, color: color, size: 20),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Text(
                      result.journeyRelevance.isNotEmpty 
                        ? result.journeyRelevance 
                        : (result.passed
                            ? (isPre
                                ? 'Fondasi yang bagus! Anda siap untuk simulasi VR.'
                                : 'Penguasaan materi luar biasa! Pelatihan VR Anda efektif.')
                            : (isPre
                                ? 'Tinjau kembali materi dan coba lagi sebelum masuk VR.'
                                : 'Pertimbangkan untuk meninjau modul atau mengulang sesi VR.')),
                      style: PharmTextStyles.bodySmall.copyWith(color: color, height: 1.5),
                    ),
                  ),
                ]),
              ),
              const SizedBox(height: 20),

              // Stats card
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
                ),
                child: Column(children: [
                  _statRow(context, 'Jawaban Benar', '${result.correctAnswers} / ${result.totalQuestions}'),
                  _divider(context),
                  _statRow(context, 'Waktu Pengerjaan', '${mins}m ${secs}s'),
                  _divider(context),
                  _statRow(context, 'Tipe Assessment', isPre ? 'Pre-Test' : 'Post-Test'),
                ]),
              ),
              const SizedBox(height: 24),

              // Recommendations
              Text('Langkah Selanjutnya', style: PharmTextStyles.h4),
              const SizedBox(height: 14),

              if (isPre && result.passed)
                _RecommendationCard(
                  icon: Icons.qr_code_scanner,
                  color: PharmColors.primary,
                  title: result.recommendationAction.isNotEmpty ? result.recommendationAction : 'Hubungkan Meta Quest 3',
                  subtitle: 'Scan QR Code untuk memulai sesi VR training',
                  onTap: () => context.go('/vr/connect'),
                  isPrimary: true,
                ),
              if (isPre && !result.passed)
                _RecommendationCard(
                  icon: Icons.refresh,
                  color: PharmColors.warning,
                  title: result.recommendationAction.isNotEmpty ? result.recommendationAction : 'Ulangi Pre-Test',
                  subtitle: 'Tinjau materi dan coba lagi',
                  onTap: () => context.go('/assessment/intro/${widget.moduleId}/pre'),
                  isPrimary: true,
                ),
              if (!isPre && result.passed)
                _RecommendationCard(
                  icon: Icons.analytics_outlined,
                  color: PharmColors.success,
                  title: result.recommendationAction.isNotEmpty ? result.recommendationAction : 'Lihat Ringkasan Pelatihan',
                  subtitle: 'Lihat analisis performa lengkap Anda',
                  onTap: () => context.go('/vr/summary'), 
                  isPrimary: true,
                ),
              if (!isPre && !result.passed)
                _RecommendationCard(
                  icon: Icons.vrpano,
                  color: PharmColors.info,
                  title: result.recommendationAction.isNotEmpty ? result.recommendationAction : 'Ulangi Sesi VR',
                  subtitle: 'Berlatih lagi untuk meningkatkan skor Anda',
                  onTap: () => context.go('/vr/connect'), 
                  isPrimary: true,
                ),
              const SizedBox(height: 10),
              _RecommendationCard(
                icon: Icons.auto_awesome,
                color: PharmColors.info,
                title: 'Tanya Asisten AI',
                subtitle: 'Dapatkan penjelasan untuk topik yang Anda lewatkan',
                onTap: () => context.go('/ai-assistant'),
              ),
              const SizedBox(height: 10),
              _RecommendationCard(
                icon: Icons.library_books_outlined,
                color: PharmColors.warning,
                title: 'Tinjau Modul',
                subtitle: 'Baca kembali materi pembelajaran',
                onTap: () => context.pop(), // Go back to detail
              ),
              const SizedBox(height: 24),

              // Back to dashboard
              TextButton(
                onPressed: () => context.go('/dashboard'),
                child: Text('Kembali ke Dashboard', style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _statRow(BuildContext context, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(children: [
        Text(label, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.bodySmall?.color)),
        const Spacer(),
        Text(value, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, fontWeight: FontWeight.bold)),
      ]),
    );
  }

  Widget _divider(BuildContext context) => Divider(height: 20, color: Theme.of(context).dividerColor.withOpacity(0.5));
}

class _RecommendationCard extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final bool isPrimary;
  const _RecommendationCard({required this.icon, required this.color, required this.title,
    required this.subtitle, required this.onTap, this.isPrimary = false});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: isPrimary ? color.withOpacity(0.06) : Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: isPrimary ? color.withOpacity(0.15) : (Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight)),
          ),
          child: Row(children: [
            Container(
              width: 42, height: 42,
              decoration: BoxDecoration(borderRadius: BorderRadius.circular(12), color: color.withOpacity(0.12)),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(title, style: PharmTextStyles.bodyMedium.copyWith(
                  color: isPrimary ? color : Theme.of(context).textTheme.displaySmall?.color, fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                Text(subtitle, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
              ]),
            ),
            Icon(Icons.arrow_forward_ios, size: 12, color: isPrimary ? color : Theme.of(context).textTheme.labelSmall?.color),
          ]),
        ),
      ),
    );
  }
}

class _ScoreRingPainter extends CustomPainter {
  final double percentage;
  final Color color;
  _ScoreRingPainter({required this.percentage, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width / 2 - 10;

    canvas.drawCircle(center, radius, Paint()
      ..color = Colors.white.withOpacity(0.06)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 10
      ..strokeCap = StrokeCap.round);

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -pi / 2,
      2 * pi * percentage,
      false,
      Paint()
        ..color = color
        ..style = PaintingStyle.stroke
        ..strokeWidth = 10
        ..strokeCap = StrokeCap.round,
    );

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -pi / 2,
      2 * pi * percentage,
      false,
      Paint()
        ..color = color.withOpacity(0.2)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 18
        ..strokeCap = StrokeCap.round
        ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 8),
    );
  }

  @override
  bool shouldRepaint(covariant _ScoreRingPainter old) => old.percentage != percentage || old.color != color;
}
