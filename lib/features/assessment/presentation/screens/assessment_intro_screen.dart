import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../domain/models/assessment_models.dart';
import '../providers/assessment_provider.dart';

class AssessmentIntroScreen extends ConsumerWidget {
  final String moduleId; // This is the module slug
  final String type; // 'pre' or 'post'
  
  const AssessmentIntroScreen({
    super.key, 
    required this.moduleId, 
    required this.type,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final backendType = type == 'post' ? 'post_test' : 'pre_test';
    final isPre = type == 'pre';
    
    final introAsync = ref.watch(assessmentIntroProvider((moduleSlug: moduleId, type: backendType)));

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: introAsync.when(
        data: (assessment) => _buildContent(context, ref, assessment, isPre),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, color: PharmColors.error, size: 48),
              const SizedBox(height: 16),
              Text('Gagal memuat assessment', style: PharmTextStyles.h3),
              const SizedBox(height: 8),
              Text(err.toString(), style: PharmTextStyles.bodySmall, textAlign: TextAlign.center),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => ref.refresh(assessmentIntroProvider((moduleSlug: moduleId, type: backendType))),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, WidgetRef ref, Assessment a, bool isPre) {
    return Stack(
      children: [
        // Background glow
        Positioned(
          top: -60, right: -40,
          child: Container(
            width: 200, height: 200,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(colors: [
                (isPre ? PharmColors.primary : PharmColors.success).withOpacity(0.06),
                Colors.transparent,
              ]),
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
                      icon: Icon(Icons.close, color: Theme.of(context).textTheme.labelSmall?.color, size: 22),
                      onPressed: () => context.pop(),
                    ),
                    const Spacer(),
                  ],
                ),
              ),

              // Content
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Module badge
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
                          Text(a.moduleTitle, style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold)),
                        ]),
                      ),
                      const SizedBox(height: 24),

                      // Hero icon
                      Container(
                        width: 72, height: 72,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: (isPre ? PharmColors.primary : PharmColors.success).withOpacity(0.08),
                          border: Border.all(color: (isPre ? PharmColors.primary : PharmColors.success).withOpacity(0.15)),
                        ),
                        child: Icon(
                          isPre ? Icons.fact_check_outlined : Icons.emoji_events_outlined,
                          size: 32,
                          color: isPre ? PharmColors.primary : PharmColors.success,
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Title
                      Text(a.title, style: PharmTextStyles.h2.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
                      const SizedBox(height: 10),

                      // Description
                      Text(
                        a.description,
                        style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.bodySmall?.color, height: 1.6),
                      ),
                      const SizedBox(height: 28),

                      // Info card
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.surface,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
                        ),
                        child: Column(
                          children: [
                            _infoRow(context, Icons.quiz_outlined, 'Questions', '${a.totalQuestions} multiple choice'),
                            _divider(context),
                            _infoRow(context, Icons.timer_outlined, 'Estimated Time', a.estimatedDuration),
                            _divider(context),
                            _infoRow(context, Icons.verified_outlined, 'Passing Score', '${a.passingScore}%'),
                            _divider(context),
                            _infoRow(context, Icons.history, 'Attempt Status', a.attemptInfo.status.toUpperCase()),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Eligibility / Warning
                      if (!a.isEligible)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: PharmColors.error.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: PharmColors.error.withOpacity(0.2)),
                          ),
                          child: Row(children: [
                            const Icon(Icons.warning_amber_rounded, color: PharmColors.error, size: 20),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                a.eligibilityMessage ?? 'Assessment ini belum bisa diambil.',
                                style: PharmTextStyles.caption.copyWith(color: PharmColors.error),
                              ),
                            ),
                          ]),
                        )
                      else ...[
                        Text(
                          isPre
                              ? 'This pre-test prepares you for the VR training session ahead.'
                              : 'This post-test evaluates your VR training performance.',
                          style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color, height: 1.4),
                        ),
                      ],
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),

              // Bottom CTA
              Padding(
                padding: const EdgeInsets.fromLTRB(28, 0, 28, 20),
                child: SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      gradient: LinearGradient(colors: [
                        !a.canStart 
                          ? Colors.grey 
                          : (isPre ? PharmColors.primary : PharmColors.success),
                        !a.canStart
                          ? Colors.grey.shade700
                          : (isPre ? PharmColors.primaryDark : const Color(0xFF00C853)),
                      ]),
                      boxShadow: a.canStart ? [BoxShadow(
                        color: (isPre ? PharmColors.primary : PharmColors.success).withOpacity(0.25),
                        blurRadius: 16, offset: const Offset(0, 4),
                      )] : null,
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(14),
                        onTap: !a.canStart ? null : () async {
                          // Show loading overlay or handle async in notifier
                          await ref.read(assessmentProvider.notifier).startSession(a);
                          if (context.mounted) {
                            final currentState = ref.read(assessmentProvider);
                            if (currentState.error != null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(currentState.error!), backgroundColor: PharmColors.error)
                              );
                            } else {
                              context.push('/assessment/question/$moduleId/$type');
                            }
                          }
                        },
                        child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Icon(a.canStart ? Icons.play_arrow : Icons.lock_outline, color: Colors.white, size: 20),
                          const SizedBox(width: 8),
                          Text(a.recommendedAction.toUpperCase(), style: PharmTextStyles.button.copyWith(color: Colors.white, letterSpacing: 1.2)),
                        ]),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _infoRow(BuildContext context, IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(children: [
        Container(
          width: 32, height: 32,
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(8), color: PharmColors.surfaceLight),
          child: Icon(icon, color: PharmColors.primary, size: 16),
        ),
        const SizedBox(width: 14),
        Text(label, style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.displaySmall?.color)),
        const Spacer(),
        Text(value, style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color)),
      ]),
    );
  }

  Widget _divider(BuildContext context) => Divider(height: 20, color: Theme.of(context).dividerColor.withOpacity(0.5));
}
