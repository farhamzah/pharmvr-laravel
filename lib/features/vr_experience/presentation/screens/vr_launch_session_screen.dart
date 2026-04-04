import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

import '../../domain/models/vr_training_session.dart';
import '../../domain/models/vr_ai_interaction.dart';
import '../providers/vr_ai_provider.dart';
import '../widgets/vr_session_hero.dart';
import '../widgets/vr_readiness_checklist.dart';
import '../widgets/vr_active_status_card.dart';
import '../widgets/vr_session_conclusion.dart';

class VrLaunchSessionScreen extends StatefulWidget {
  final String moduleId;

  const VrLaunchSessionScreen({
    super.key,
    required this.moduleId,
  });

  @override
  State<VrLaunchSessionScreen> createState() => _VrLaunchSessionScreenState();
}

class _VrLaunchSessionScreenState extends State<VrLaunchSessionScreen> {
  // Simulating Riverpod State
  late VrTrainingSession _session;
  Timer? _simulationTimer;

  @override
  void initState() {
    super.initState();
    // Initial Pre-Launch State
    _session = VrTrainingSession(
      sessionId: 'sess_9982',
      moduleId: widget.moduleId,
      moduleTitle: 'Simulasi Gowning Area Bersih Kelas A', // Cleanroom A Gowning Sim
      moduleDescription: 'Latih urutan memakai APD steril tanpa kontaminasi silang sesuai standar CPOB Farmasi.',
      phase: VrSessionPhase.launchReady,
      estimatedDurationMinutes: 15,
      isDeviceConnected: true, // Assuming device connected via Gateway earlier
      isPreTestPassed: true,
      isUserReady: true,
    );
  }

  @override
  void dispose() {
    _simulationTimer?.cancel();
    super.dispose();
  }

  // --- FAKE UNITY/LARAVEL TELEMETRY SYNC --- //
  void _startVrSimulation() {
    setState(() {
      _session = VrTrainingSession(
        sessionId: _session.sessionId,
        moduleId: _session.moduleId,
        moduleTitle: _session.moduleTitle,
        moduleDescription: _session.moduleDescription,
        phase: VrSessionPhase.inProgress,
        estimatedDurationMinutes: _session.estimatedDurationMinutes,
        timeElapsedSeconds: 0,
        currentStepIndex: 1,
        totalSteps: 5,
        currentStepName: 'Memasuki Airlock', // "Entering Airlock"
      );
    });

    int ticks = 0;
    _simulationTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      ticks++;

      setState(() {
        // Update Time
        int newTime = (_session.timeElapsedSeconds ?? 0) + 1;
        
        // Simulate step progression
        int newStep = _session.currentStepIndex ?? 1;
        String newStepName = _session.currentStepName ?? '';
        
        if (ticks == 3) {
           newStep = 2;
           newStepName = 'Mencuci Tangan (Standard WHO)'; // "Hand Washing WHO"
        } else if (ticks == 6) {
           newStep = 3;
           newStepName = 'Memakai Sarung Tangan Steril'; // "Donning Sterile Gloves"
        } else if (ticks == 9) {
           newStep = 4;
           newStepName = 'Mengenakan Coverall'; // "Donning Coverall"
        } else if (ticks == 12) {
           newStep = 5;
           newStepName = 'Melewati Validasi Sensor AI'; // "Passing AI Sensor Validation"
        } else if (ticks == 15) {
           _completeSimulation();
           timer.cancel();
           return;
        }

        _session = VrTrainingSession(
          sessionId: _session.sessionId,
          moduleId: _session.moduleId,
          moduleTitle: _session.moduleTitle,
          moduleDescription: _session.moduleDescription,
          phase: _session.phase,
          estimatedDurationMinutes: _session.estimatedDurationMinutes,
          timeElapsedSeconds: newTime,
          currentStepIndex: newStep,
          totalSteps: _session.totalSteps,
          currentStepName: newStepName,
        );
      });
    });
  }

  void _completeSimulation() {
    setState(() {
      _session = VrTrainingSession(
        sessionId: _session.sessionId,
        moduleId: _session.moduleId,
        moduleTitle: _session.moduleTitle,
        moduleDescription: _session.moduleDescription,
        phase: VrSessionPhase.completed,
        estimatedDurationMinutes: _session.estimatedDurationMinutes,
        timeElapsedSeconds: _session.timeElapsedSeconds,
        finalScore: 92, // Deducted 8 points for touching outside sterile zone
      );
    });
  }

  void _interruptSimulation() {
    _simulationTimer?.cancel();
    setState(() {
      _session = VrTrainingSession(
        sessionId: _session.sessionId,
        moduleId: _session.moduleId,
        moduleTitle: _session.moduleTitle,
        moduleDescription: _session.moduleDescription,
        phase: VrSessionPhase.interrupted,
        estimatedDurationMinutes: _session.estimatedDurationMinutes,
        timeElapsedSeconds: _session.timeElapsedSeconds,
        interruptReason: 'Headset dilepas sebelum validasi langkah ke-3 selesai.', // "Headset removed before step 3 completed."
      );
    });
  }
  // ----------------------------------------- //

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text(
          'PharmVR Engine', // System-level technical feel
          style: PharmTextStyles.h4.copyWith(color: PharmColors.textPrimary),
        ),
        backgroundColor: PharmColors.background,
        elevation: 0,
        centerTitle: true,
        iconTheme: const IconThemeData(color: PharmColors.textPrimary),
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          return SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight: constraints.maxHeight, // Push actions to bottom
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                   Column(
                     children: [
                       VrSessionHero(session: _session),
                       const SizedBox(height: PharmSpacing.xl),
                       VrReadinessChecklist(session: _session),
                       VrActiveStatusCard(session: _session),
                       Consumer(
                         builder: (context, ref, _) {
                           final interaction = ref.watch(vrAiProvider);
                           if (interaction == null || _session.phase != VrSessionPhase.inProgress) {
                             return const SizedBox.shrink();
                           }
                           return _buildAiGuidance(context, ref, interaction);
                         },
                       ),
                       VrSessionConclusion(session: _session),
                     ],
                   ),
                   
                   _buildBottomActions(),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildBottomActions() {
    return Padding(
      padding: PharmSpacing.allLg,
      child: Column(
        children: [
          if (_session.phase == VrSessionPhase.launchReady)
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: _session.isReadyToLaunch ? _startVrSimulation : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.primaryDark,
                  disabledBackgroundColor: PharmColors.surfaceLight,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  shadowColor: PharmColors.accentGlow,
                  elevation: _session.isReadyToLaunch ? 8 : 0,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.play_circle_fill_rounded, 
                      color: _session.isReadyToLaunch ? PharmColors.background : PharmColors.textTertiary,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'LUNCURKAN VR', // "LAUNCH VR"
                      style: PharmTextStyles.button.copyWith(
                        color: _session.isReadyToLaunch ? PharmColors.background : PharmColors.textTertiary,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
          if (_session.phase == VrSessionPhase.inProgress)
             SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: _interruptSimulation,
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.surface,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                    side: const BorderSide(color: PharmColors.error, width: 1.5),
                  ),
                ),
                child: Text(
                  'Hentikan Sesi Darurat', // "Emergency Stop Session"
                  style: PharmTextStyles.button.copyWith(
                    color: PharmColors.error,
                  ),
                ),
              ),
            ),

          if (_session.phase == VrSessionPhase.completed || _session.phase == VrSessionPhase.interrupted)
             SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: () {
                   // context.go('/dashboard'); Return to dash
                   Navigator.of(context).pop();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.surfaceLight,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                    side: const BorderSide(color: PharmColors.divider),
                  ),
                ),
                child: Text(
                  'Kembali ke Dasbor', // "Return to Dashboard"
                  style: PharmTextStyles.button.copyWith(
                    color: PharmColors.textPrimary,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildAiGuidance(BuildContext context, WidgetRef ref, VrAiInteraction interaction) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg, vertical: PharmSpacing.md),
      padding: const EdgeInsets.all(PharmSpacing.lg),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            PharmColors.primary.withValues(alpha: 0.15),
            PharmColors.primary.withValues(alpha: 0.05),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: PharmColors.primary.withValues(alpha: 0.2)),
        boxShadow: [
          BoxShadow(
            color: PharmColors.accentGlow.withValues(alpha: 0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.auto_awesome, color: PharmColors.primary, size: 20),
              const SizedBox(width: 8),
              Text(
                'LIVE GUIDANCE',
                style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 1.5),
              ),
              const Spacer(),
              IconButton(
                onPressed: () => ref.read(vrAiProvider.notifier).clearInteraction(),
                icon: const Icon(Icons.close, size: 16, color: PharmColors.textSecondary),
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            interaction.displayText,
            style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimary, height: 1.5),
          ),
          if (interaction.recommendedNextAction != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: PharmColors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.info_outline, size: 14, color: PharmColors.primary),
                  const SizedBox(width: 6),
                  Text(
                    interaction.recommendedNextAction!,
                    style: PharmTextStyles.caption.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
