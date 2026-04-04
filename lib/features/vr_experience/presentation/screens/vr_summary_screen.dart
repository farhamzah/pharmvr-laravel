import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/core/theme/pharm_spacing.dart';
import 'package:pharmvrpro/core/widgets/pharm_glass_card.dart';
import 'package:pharmvrpro/core/widgets/pharm_primary_button.dart';
import 'package:pharmvrpro/features/vr_experience/data/repositories/vr_repository.dart';

class VrSummaryScreen extends ConsumerStatefulWidget {
  const VrSummaryScreen({super.key});

  @override
  ConsumerState<VrSummaryScreen> createState() => _VrSummaryScreenState();
}

class _VrSummaryScreenState extends ConsumerState<VrSummaryScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _summary;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchSummary();
  }

  Future<void> _fetchSummary() async {
    try {
      final data = await ref.read(vrRepositoryProvider).getCurrentSession();
      setState(() {
        _summary = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_error != null) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: PharmColors.error),
              const SizedBox(height: 16),
              Text('Gagal memuat ringkasan sesi', style: PharmTextStyles.h3),
              const SizedBox(height: 8),
              Text(_error!, style: PharmTextStyles.bodySmall),
              const SizedBox(height: 24),
              PharmPrimaryButton(text: 'KEMBALI', onPressed: () => context.go('/dashboard')),
            ],
          ),
        ),
      );
    }

    final score = _summary?['score'] ?? 0;
    final moduleSummary = _summary?['training_module_summary'];
    final metrics = _summary?['metrics'] ?? {};

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header with Rank
            Container(
              width: double.infinity,
              padding: const EdgeInsets.only(top: 80, bottom: 40, left: 24, right: 24),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF00B4D8), Color(0xFF0077B6)],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Column(
                children: [
                  const Icon(Icons.workspace_premium, size: 80, color: Colors.amber),
                  const SizedBox(height: 16),
                  Text(score >= 80 ? 'Level Up!' : 'Sesi Selesai', style: PharmTextStyles.h1.copyWith(color: Colors.white)),
                  Text(
                    moduleSummary?['title'] ?? 'Training Complete',
                    style: PharmTextStyles.bodyMedium.copyWith(color: Colors.white70),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(PharmSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Performance Analytics', style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
                  const SizedBox(height: PharmSpacing.md),
                  
                  PharmGlassCard(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildBigStat(context, 'Score', '$score', '%', score >= 80 ? Colors.green : Colors.orange),
                        _buildBigStat(context, 'Status', _summary?['status']?.toUpperCase() ?? 'N/A', '', PharmColors.primary),
                        _buildBigStat(context, 'Duration', '${_summary?['session_duration_minutes'] ?? 0}', 'min', Colors.blue),
                      ],
                    ),
                  ),
                  
                   const SizedBox(height: PharmSpacing.xl),
                  Text('Behavioral Breakdown', style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
                  const SizedBox(height: PharmSpacing.md),
                  
                  _buildMetricTile(context, 'Akurasi Prosedur', metrics['procedure_accuracy']?.toDouble() ?? 0.0, Colors.green),
                  _buildMetricTile(context, 'Kepatuhan Keamanan', metrics['safety_index']?.toDouble() ?? 0.0, Colors.blue),
                  _buildMetricTile(context, 'Efisiensi Waktu', metrics['time_efficiency_index']?.toDouble() ?? 0.0, Colors.orange),
                  
                  const SizedBox(height: PharmSpacing.xxxl),
                  
                  PharmPrimaryButton(
                    text: 'BACK TO DASHBOARD',
                    onPressed: () => context.go('/dashboard'),
                  ),
                  const SizedBox(height: PharmSpacing.xl),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBigStat(BuildContext context, String label, String value, String unit, Color color) {
    return Column(
      children: [
        Text(label, style: PharmTextStyles.label.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
        const SizedBox(height: 4),
        Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(value, style: PharmTextStyles.h1.copyWith(color: color, fontSize: 28)),
            if (unit.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Text(unit, style: PharmTextStyles.bodySmall.copyWith(color: color)),
              ),
          ],
        ),
      ],
    );
  }

  Widget _buildMetricTile(BuildContext context, String title, double value, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: PharmSpacing.md),
      child: PharmGlassCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.bodySmall?.color)),
                Text('${(value * 100).toInt()}%', style: PharmTextStyles.label.copyWith(color: color, fontWeight: FontWeight.bold)),
              ],
            ),
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: value,
                backgroundColor: Colors.white12,
                color: color,
                minHeight: 4,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
