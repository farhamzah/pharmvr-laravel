import 'package:flutter/material.dart';
import '../theme/pharm_colors.dart';
import '../theme/pharm_text_styles.dart';

/// Backend-ready enum for journey states
enum TrainingStepState { locked, active, completed }

/// Supports both horizontal (dashboard) and vertical (module detail) layouts
enum TrainingJourneyDirection { horizontal, vertical }

/// Reusable widget to show the Pre-Test -> VR Training -> Post-Test journey
class PharmTrainingJourney extends StatelessWidget {
  final int? currentStage; // 0: Start, 1: Pre-test done, 2: VR done, 3: Post-test done
  final String? currentStep; // [NEW] Backend step ID: 'pre-test', 'vr-sim', 'post-test', 'completed'
  final String? moduleTitle;
  final TrainingJourneyDirection direction;
  final VoidCallback? onPreTest;
  final VoidCallback? onLaunchVr;
  final VoidCallback? onPostTest;
  final VoidCallback? onViewSummary;

  const PharmTrainingJourney({
    super.key,
    this.currentStage,
    this.currentStep,
    this.moduleTitle,
    this.direction = TrainingJourneyDirection.horizontal,
    this.onPreTest,
    this.onLaunchVr,
    this.onPostTest,
    this.onViewSummary,
  });

  int get effectiveStage {
    if (currentStep != null) {
      switch (currentStep) {
        case 'pre_test': return 0;
        case 'vr_sim': return 1;
        case 'post_test': return 2;
        case 'completed': return 3;
      }
    }
    return currentStage ?? 0;
  }

  @override
  Widget build(BuildContext context) {
    final isHorizontal = direction == TrainingJourneyDirection.horizontal;
    final stage = effectiveStage;
    
    // Determine states based on stage
    final preTestState = stage > 0 ? TrainingStepState.completed : (stage == 0 ? TrainingStepState.active : TrainingStepState.locked);
    final vrState = stage > 1 ? TrainingStepState.completed : (stage == 1 ? TrainingStepState.active : TrainingStepState.locked);
    final postTestState = stage > 2 ? TrainingStepState.completed : (stage == 2 ? TrainingStepState.active : TrainingStepState.locked);

    final widgetBody = Container(
      padding: EdgeInsets.all(isHorizontal ? 16 : 24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : PharmColors.dividerLight),
        boxShadow: [
          BoxShadow(
            color: Theme.of(context).brightness == Brightness.dark 
                ? Colors.black.withOpacity(0.2) 
                : Colors.black.withOpacity(0.05), 
            blurRadius: 10, offset: const Offset(0, 4)
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Optional Header Text (e.g. Next Recommended Action)
          if (moduleTitle != null) ...[
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: PharmColors.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    'JOURNEY',
                    style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 1.5),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    moduleTitle!,
                    style: PharmTextStyles.bodyMedium.copyWith(color: Theme.of(context).textTheme.displaySmall?.color, fontWeight: FontWeight.bold),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
          ],

          // The Journey Steps
          if (isHorizontal)
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(child: _buildStep(context, Icons.quiz_outlined, 'Pre-Test', preTestState, onPreTest)),
                _buildConnector(context, isHorizontal, preTestState == TrainingStepState.completed),
                Expanded(child: _buildStep(context, Icons.vrpano, 'VR Training', vrState, onLaunchVr, isPrimary: true)),
                _buildConnector(context, isHorizontal, vrState == TrainingStepState.completed),
                Expanded(child: _buildStep(context, Icons.fact_check_outlined, 'Post-Test', postTestState, onPostTest)),
              ],
            )
          else
            Column(
              children: [
                _buildStep(context, Icons.quiz_outlined, 'Pre-Test', preTestState, onPreTest, isHorizontal: false),
                _buildConnector(context, isHorizontal, preTestState == TrainingStepState.completed),
                _buildStep(context, Icons.vrpano, 'VR Training', vrState, onLaunchVr, isPrimary: true, isHorizontal: false),
                _buildConnector(context, isHorizontal, vrState == TrainingStepState.completed),
                _buildStep(context, Icons.fact_check_outlined, 'Post-Test', postTestState, onPostTest, isHorizontal: false),
              ],
            ),

          // Completion / Next Action Banner
          if (stage >= 3 && onViewSummary != null) ...[
            const SizedBox(height: 24),
            InkWell(
              onTap: onViewSummary,
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(colors: [PharmColors.success, Color(0xFF00C853)]),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.analytics_outlined, color: PharmColors.background, size: 18),
                    const SizedBox(width: 8),
                    Text(
                      'VIEW TRAINING SUMMARY',
                      style: PharmTextStyles.label.copyWith(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
          ] else if (isHorizontal) ...[
            const SizedBox(height: 16),
            Center(
              child: Text(
                _getNextActionText(stage),
                style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
              ),
            ),
          ]
        ],
      ),
    );

    return widgetBody;
  }

  String _getNextActionText(int stage) {
    switch (stage) {
      case 0: return 'Next Action: Complete Pre-Test to unlock VR';
      case 1: return 'Next Action: Launch VR Training';
      case 2: return 'Next Action: Complete Post-Test to finish';
      default: return 'Module Completed';
    }
  }

  Widget _buildStep(BuildContext context, IconData icon, String label, TrainingStepState state, VoidCallback? onTap, {bool isPrimary = false, bool isHorizontal = true}) {
    final isDone = state == TrainingStepState.completed;
    final isActive = state == TrainingStepState.active;

    final color = isDone ? PharmColors.success : (isActive ? PharmColors.primary : Theme.of(context).textTheme.labelSmall!.color!);
    final bgColor = isDone ? PharmColors.success.withOpacity(0.12) : (isActive ? PharmColors.primary.withOpacity(0.12) : (Theme.of(context).brightness == Brightness.dark ? PharmColors.surfaceLight : PharmColors.dividerLight.withOpacity(0.5)));

    final size = isPrimary ? (isHorizontal ? 56.0 : 48.0) : (isHorizontal ? 48.0 : 40.0);

    final VoidCallback effectiveOnTap = () {
      if (isActive || isDone) {
        onTap?.call();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Please complete the previous step to unlock $label.',
              style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.background),
            ),
            backgroundColor: PharmColors.warning,

            behavior: SnackBarBehavior.floating,
            margin: const EdgeInsets.only(bottom: 80, left: 20, right: 20),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            duration: const Duration(seconds: 2),
            elevation: 8,
          ),
        );
      }
    };

    final content = Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          width: size,
          height: size,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: bgColor,
            border: Border.all(
              color: isActive ? color : color.withOpacity(0.3),
              width: isActive ? 2 : 1,
            ),
            boxShadow: isActive ? [BoxShadow(color: color.withOpacity(0.25), blurRadius: 16)] : [],
          ),
          child: Icon(
            isDone ? Icons.check : icon,
            color: color,
            size: isPrimary ? 24 : 20,
          ),
        ),
        if (isHorizontal) const SizedBox(height: 12),
        if (isHorizontal)
          Text(
            label,
            style: PharmTextStyles.caption.copyWith(
              color: isActive 
                  ? Theme.of(context).textTheme.displaySmall?.color 
                  : (isDone ? Theme.of(context).textTheme.bodySmall?.color : Theme.of(context).textTheme.labelSmall?.color),
              fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
            ),
            textAlign: TextAlign.center,
          ),
      ],
    );

    if (!isHorizontal) {
      return InkWell(
         onTap: effectiveOnTap,
         borderRadius: BorderRadius.circular(12),
         child: Padding(
           padding: const EdgeInsets.symmetric(vertical: 8.0),
           child: Row(
             children: [
               content,
               const SizedBox(width: 16),
               Expanded(
                 child: Column(
                   crossAxisAlignment: CrossAxisAlignment.start,
                   children: [
                     Text(
                       label,
                       style: PharmTextStyles.bodyMedium.copyWith(
                         color: isActive 
                             ? Theme.of(context).textTheme.displaySmall?.color 
                             : (isDone ? Theme.of(context).textTheme.bodySmall?.color : Theme.of(context).textTheme.labelSmall?.color),
                         fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
                       ),
                     ),
                     if (isActive)
                       Text('Click to start', style: PharmTextStyles.caption.copyWith(color: PharmColors.primary)),
                     if (isDone)
                       Text('Completed', style: PharmTextStyles.caption.copyWith(color: PharmColors.success)),
                   ],
                 ),
               ),
               if (isActive)
                 Icon(Icons.arrow_forward_ios, color: PharmColors.primary, size: 14),
             ],
           ),
         ),
      );
    }

    return GestureDetector(
      onTap: effectiveOnTap,
      behavior: HitTestBehavior.opaque,
      child: content,
    );
  }

  Widget _buildConnector(BuildContext context, bool isHorizontal, bool isDone) {
    if (isHorizontal) {
      return Container(
        width: 32,
        height: 2,
        margin: const EdgeInsets.only(top: 24), // Align with center of circle
        color: isDone ? PharmColors.success.withOpacity(0.5) : Theme.of(context).dividerColor.withOpacity(0.3),
      );
    } else {
      return Container(
        width: 2,
        height: 24,
        margin: const EdgeInsets.only(left: 20, top: 4, bottom: 4), // Align with center of circle
        color: isDone ? PharmColors.success.withOpacity(0.5) : Theme.of(context).dividerColor.withOpacity(0.3),
      );
    }
  }
}
