import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../providers/assessment_provider.dart';
import '../../../../core/config/network_constants.dart';
import '../../../../core/widgets/pharm_network_image.dart';

class AssessmentQuestionScreen extends ConsumerStatefulWidget {
  final String moduleId;
  final String type;
  const AssessmentQuestionScreen({super.key, required this.moduleId, required this.type});

  @override
  ConsumerState<AssessmentQuestionScreen> createState() => _AssessmentQuestionScreenState();
}

class _AssessmentQuestionScreenState extends ConsumerState<AssessmentQuestionScreen> {
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      final notifier = ref.read(assessmentProvider.notifier);
      notifier.tick();
      
      // Auto-submit if time runs out
      final state = ref.read(assessmentProvider);
      final assessment = state.assessment;
      if (assessment != null && state.elapsedSeconds >= assessment.durationSeconds) {
        _timer?.cancel();
        _showTimesUpDialog();
      }
    });
  }

  void _showTimesUpDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => PopScope(
        canPop: false,
        child: Dialog(
          backgroundColor: Theme.of(context).colorScheme.surface,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 72, height: 72,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle, 
                    color: PharmColors.error.withOpacity(0.1),
                    border: Border.all(color: PharmColors.error.withOpacity(0.2)),
                  ),
                  child: const Icon(Icons.timer_off_outlined, color: PharmColors.error, size: 36),
                ),
                const SizedBox(height: 24),
                Text('Waktu Habis!', style: PharmTextStyles.h2.copyWith(color: PharmColors.error)),
                const SizedBox(height: 12),
                Text(
                  'Batas waktu pengerjaan telah berakhir. Jawaban Anda akan disimpan secara otomatis.',
                  style: PharmTextStyles.bodyMedium.copyWith(
                    color: Theme.of(context).textTheme.bodySmall?.color, 
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton(
                    onPressed: () async {
                      // Start the loading process
                      await ref.read(assessmentProvider.notifier).submitAssessment();
                      
                      // Check for errors before navigating blindly
                      if (mounted) {
                        final currentState = ref.read(assessmentProvider);
                        Navigator.pop(ctx);
                        if (currentState.error != null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(currentState.error!), backgroundColor: PharmColors.error)
                          );
                        } else {
                          context.go('/assessment/result/${widget.moduleId}/${widget.type}');
                        }
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: PharmColors.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('LIHAT HASIL'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(assessmentProvider);
    final q = state.currentQuestion;
    
    if (q == null) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final assessment = state.assessment!;
    final timeLimit = assessment.durationSeconds;
    final remaining = (timeLimit - state.elapsedSeconds).clamp(0, timeLimit);
    final mins = remaining ~/ 60;
    final secs = remaining % 60;
    final isLow = remaining < 60;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Stack(
        children: [
          SafeArea(
            child: Column(
              children: [
                // ── Top bar ──
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                  child: Row(
                    children: [
                      // Timer pill
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: (isLow ? PharmColors.error : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight)).withOpacity(isLow ? 0.15 : 1),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: isLow ? PharmColors.error.withOpacity(0.4) : Theme.of(context).dividerColor),
                        ),
                        child: Row(mainAxisSize: MainAxisSize.min, children: [
                          Icon(Icons.timer_outlined, size: 14, color: isLow ? PharmColors.error : PharmColors.textSecondary),
                          const SizedBox(width: 6),
                          Text(
                            '${mins.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}',
                            style: PharmTextStyles.label.copyWith(color: isLow ? PharmColors.error : Theme.of(context).textTheme.displaySmall?.color, fontWeight: FontWeight.w700),
                          ),
                        ]),
                      ),
                      const Spacer(),
                      Flexible(
                        child: Text(
                          assessment.moduleTitle, 
                          style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color), 
                          overflow: TextOverflow.ellipsis
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        icon: Icon(Icons.close, color: Theme.of(context).textTheme.labelSmall?.color, size: 20),
                        onPressed: () => _showQuitDialog(),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                    ],
                  ),
                ),

                // ── Progress ──
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(99),
                        child: LinearProgressIndicator(
                          value: state.progress,
                          backgroundColor: Theme.of(context).dividerColor,
                          color: PharmColors.primary,
                          minHeight: 4,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Pertanyaan ${state.currentQuestionIndex + 1} dari ${state.totalQuestions}',
                        style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // ── Question + Options ──
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (q.imageUrl != null) ...[
                          ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: PharmNetworkImage(
                              url: NetworkConstants.sanitizeUrl(q.imageUrl!),
                              height: 180,
                              width: double.infinity,
                              fit: BoxFit.cover,
                            ),
                          ),
                          const SizedBox(height: 20),
                        ],
                        Text(q.questionText, style: PharmTextStyles.bodyLarge.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, height: 1.6, fontWeight: FontWeight.w500)),
                        const SizedBox(height: 24),
                        ...q.options.map((option) {
                          final isSelected = state.currentSelectedOption == option.id;
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: _OptionCard(
                              label: option.label,
                              text: option.text,
                              isSelected: isSelected,
                              onTap: () => ref.read(assessmentProvider.notifier).selectAnswer(option.id),
                            ),
                          );
                        }),
                      ],
                    ),
                  ),
                ),

                // ── Bottom nav ──
                Container(
                  padding: const EdgeInsets.fromLTRB(20, 14, 20, 16),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surface,
                    border: Border(top: BorderSide(color: Theme.of(context).dividerColor.withOpacity(0.5))),
                  ),
                  child: Row(
                    children: [
                      // Previous
                      if (!state.isFirstQuestion) ...[
                        Expanded(
                          child: SizedBox(
                            height: 48,
                            child: OutlinedButton.icon(
                              onPressed: () => ref.read(assessmentProvider.notifier).previousQuestion(),
                              icon: const Icon(Icons.arrow_back, size: 16),
                              label: const Text('Sebelumnya'),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Theme.of(context).textTheme.bodySmall?.color,
                                side: const BorderSide(color: PharmColors.divider),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                      ],
                      // Next / Submit
                      Expanded(
                        child: SizedBox(
                          height: 48,
                          child: DecoratedBox(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(14),
                              gradient: state.currentSelectedOption != null
                                  ? const LinearGradient(colors: [PharmColors.primary, PharmColors.primaryDark])
                                  : null,
                              color: state.currentSelectedOption == null ? (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight) : null,
                            ),
                            child: Material(
                              color: Colors.transparent,
                              child: InkWell(
                                borderRadius: BorderRadius.circular(14),
                                onTap: state.currentSelectedOption != null
                                    ? () async {
                                        if (state.isLastQuestion) {
                                          try {
                                            await ref.read(assessmentProvider.notifier).submitAssessment();
                                            if (mounted) {
                                              context.go('/assessment/result/${widget.moduleId}/${widget.type}');
                                            }
                                          } catch (e) {
                                            if (mounted) {
                                              ScaffoldMessenger.of(context).showSnackBar(
                                                SnackBar(
                                                  content: Text('Gagal mengirim jawaban: ${e.toString()}'),
                                                  backgroundColor: PharmColors.error,
                                                )
                                              );
                                            }
                                          }
                                        } else {
                                          ref.read(assessmentProvider.notifier).nextQuestion();
                                        }
                                      }
                                    : null,
                                child: Center(
                                  child: Text(
                                    state.isLastQuestion ? 'SUBMIT' : 'LANJUT',
                                    style: PharmTextStyles.label.copyWith(
                                      color: state.currentSelectedOption != null ? Colors.white : Theme.of(context).textTheme.labelSmall?.color,
                                      fontWeight: FontWeight.w700,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          if (state.isLoading)
            Container(
              color: Colors.black26,
              child: const Center(child: CircularProgressIndicator()),
            ),
        ],
      ),
    );
  }

  void _showQuitDialog() {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Theme.of(context).colorScheme.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 52, height: 52,
                decoration: BoxDecoration(shape: BoxShape.circle, color: PharmColors.warning.withOpacity(0.1)),
                child: const Icon(Icons.warning_amber_rounded, color: PharmColors.warning, size: 26),
              ),
              const SizedBox(height: 16),
              Text('Keluar Assessment?', style: PharmTextStyles.h3),
              const SizedBox(height: 8),
              Text(
                'Progres pengerjaan Anda tidak akan disimpan jika Anda keluar sekarang.',
                style: PharmTextStyles.bodySmall,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              Row(children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Lanjutkan'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () { 
                      Navigator.pop(ctx); 
                      context.go('/dashboard'); 
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: PharmColors.error,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('KELUAR'),
                  ),
                ),
              ]),
            ],
          ),
        ),
      ),
    );
  }
}

class _OptionCard extends StatelessWidget {
  final String label;
  final String text;
  final bool isSelected;
  final VoidCallback onTap;
  const _OptionCard({required this.label, required this.text, required this.isSelected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isSelected ? PharmColors.primary.withOpacity(0.08) : Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected ? PharmColors.primary : (Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
              width: isSelected ? 1.5 : 1,
            ),
          ),
          child: Row(
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                width: 32, height: 32,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isSelected ? PharmColors.primary : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight),
                  border: Border.all(color: isSelected ? PharmColors.primary : Theme.of(context).dividerColor),
                ),
                child: Center(
                  child: Text(label, style: PharmTextStyles.label.copyWith(
                    color: isSelected ? Colors.white : Theme.of(context).textTheme.bodySmall?.color,
                    fontWeight: FontWeight.bold,
                  )),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(text, style: PharmTextStyles.bodyMedium.copyWith(
                  color: isSelected ? Theme.of(context).textTheme.displaySmall?.color : Theme.of(context).textTheme.bodySmall?.color,
                  height: 1.4,
                )),
              ),
              if (isSelected)
                const Icon(Icons.check_circle, color: PharmColors.primary, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}
