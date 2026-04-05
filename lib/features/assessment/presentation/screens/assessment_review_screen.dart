import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../providers/assessment_provider.dart';

class AssessmentReviewScreen extends ConsumerWidget {
  final String moduleId;
  final String type;
  const AssessmentReviewScreen({super.key, required this.moduleId, required this.type});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(assessmentProvider);
    final timeLimit = state.assessment?.timeLimitSeconds ?? 0;
    final remaining = (timeLimit - state.elapsedSeconds).clamp(0, timeLimit);
    final mins = remaining ~/ 60;
    final secs = remaining % 60;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: PharmColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new, color: Theme.of(context).textTheme.labelSmall?.color, size: 20),
          onPressed: () => context.pop(),
        ),
        title: Text('Review Answers', style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
        centerTitle: true,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: Theme.of(context).dividerColor),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Summary stats
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _miniStat(context, Icons.timer_outlined, '${mins.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}', 'Remaining'),
                    Container(width: 1, height: 32, color: Theme.of(context).dividerColor),
                    _miniStat(context, Icons.check_circle_outline, '${state.answeredCount}', 'Answered'),
                    Container(width: 1, height: 32, color: Theme.of(context).dividerColor),
                    _miniStat(context, Icons.help_outline, '${state.totalQuestions - state.answeredCount}', 'Skipped'),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              Text('Question Map', style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
              const SizedBox(height: 14),

              // Question grid
              Expanded(
                child: GridView.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 5, crossAxisSpacing: 10, mainAxisSpacing: 10,
                  ),
                  itemCount: state.totalQuestions,
                  itemBuilder: (context, i) {
                    final answered = state.selectedAnswers.containsKey(i);
                    final current = state.currentQuestionIndex == i;
                    return GestureDetector(
                      onTap: () {
                        ref.read(assessmentProvider.notifier).goToQuestion(i);
                        context.pop();
                      },
                      child: Container(
                        decoration: BoxDecoration(
                          color: current
                              ? PharmColors.primary.withOpacity(0.12)
                              : answered
                                  ? PharmColors.success.withOpacity(0.1)
                                  : Theme.of(context).colorScheme.surface,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: current ? PharmColors.primary : answered ? PharmColors.success.withOpacity(0.4) : Theme.of(context).dividerColor,
                            width: current ? 2 : 1,
                          ),
                        ),
                        child: Center(
                          child: Text(
                            '${i + 1}',
                            style: PharmTextStyles.bodyLarge.copyWith(
                              color: current ? PharmColors.primary : answered ? PharmColors.success : Theme.of(context).textTheme.labelSmall?.color,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 16),

              // Warning if not all answered
              if (!state.allAnswered)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: PharmColors.warning.withOpacity(0.06),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: PharmColors.warning.withOpacity(0.15)),
                  ),
                  child: Row(children: [
                    const Icon(Icons.warning_amber_rounded, color: PharmColors.warning, size: 18),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Answer all questions before submitting.',
                        style: PharmTextStyles.caption.copyWith(color: PharmColors.warning),
                      ),
                    ),
                  ]),
                ),

              // Submit CTA
              SizedBox(
                width: double.infinity,
                height: 52,
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(14),
                    gradient: state.allAnswered
                        ? const LinearGradient(colors: [PharmColors.primary, PharmColors.primaryDark])
                        : null,
                    color: state.allAnswered ? null : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.backgroundLight),
                    boxShadow: state.allAnswered
                        ? [BoxShadow(color: PharmColors.primary.withOpacity(0.25), blurRadius: 16, offset: const Offset(0, 4))]
                        : [],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(14),
                      onTap: state.allAnswered
                          ? () => _showSubmitDialog(context, ref)
                          : null,
                      child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                        Icon(Icons.send, size: 18, color: state.allAnswered ? Colors.white : Theme.of(context).textTheme.labelSmall?.color),
                        const SizedBox(width: 8),
                        Text(
                          'SUBMIT ASSESSMENT',
                          style: PharmTextStyles.button.copyWith(
                            color: state.allAnswered ? Colors.white : Theme.of(context).textTheme.labelSmall?.color,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ]),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
    );
  }

  Widget _miniStat(BuildContext context, IconData icon, String value, String label) {
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Icon(icon, color: PharmColors.primary, size: 18),
      const SizedBox(height: 4),
      Text(value, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, fontWeight: FontWeight.w700)),
      Text(label, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color, fontSize: 10)),
    ]);
  }

  void _showSubmitDialog(BuildContext context, WidgetRef ref) {
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
                decoration: BoxDecoration(shape: BoxShape.circle, color: PharmColors.primary.withOpacity(0.1)),
                child: const Icon(Icons.send, color: PharmColors.primary, size: 24),
              ),
              const SizedBox(height: 16),
              Text('Submit Assessment?', style: PharmTextStyles.h3.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
              const SizedBox(height: 8),
              Text(
                'Once submitted, you cannot change your answers. Make sure you have reviewed all questions.',
                style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.5),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              Row(children: [
                Expanded(
                  child: SizedBox(
                    height: 44,
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(ctx),
                      style: OutlinedButton.styleFrom(
                        side: BorderSide(color: Theme.of(context).dividerColor),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: Text('Review', style: PharmTextStyles.label.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, fontWeight: FontWeight.w600)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: SizedBox(
                    height: 44,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [PharmColors.primary, PharmColors.primaryDark]),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          borderRadius: BorderRadius.circular(12),
                          onTap: () {
                            Navigator.pop(ctx);
                            ref.read(assessmentProvider.notifier).submitAssessment();
                            context.go('/assessment/result/$moduleId/$type');
                          },
                          child: Center(child: Text('SUBMIT', style: PharmTextStyles.label.copyWith(color: Colors.white, fontWeight: FontWeight.w700, letterSpacing: 0.8))),
                        ),
                      ),
                    ),
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
