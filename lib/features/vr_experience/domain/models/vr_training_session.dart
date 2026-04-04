enum VrSessionPhase {
  launchReady,    // Checklist pending or ready to launch
  inProgress,     // Actively connected and running in VR headset
  completed,      // Success, viewing summary / post-test
  interrupted,    // Disconnected mid-session, or Unity crashed
  failed          // Failed to launch entirely
}

class VrTrainingSession {
  final String sessionId;
  final String moduleId;
  final String moduleTitle;
  final String moduleDescription;
  final VrSessionPhase phase;
  final int estimatedDurationMinutes;
  final int? timeElapsedSeconds;
  
  // Launch Readiness Checklist
  final bool isDeviceConnected;
  final bool isPreTestPassed;
  final bool isUserReady;

  // Active Progress
  final String? currentStepName;
  final int? currentStepIndex;
  final int? totalSteps;
  
  // End Results
  final int? finalScore;
  final String? interruptReason;

  const VrTrainingSession({
    required this.sessionId,
    required this.moduleId,
    required this.moduleTitle,
    required this.moduleDescription,
    required this.phase,
    required this.estimatedDurationMinutes,
    this.timeElapsedSeconds,
    this.isDeviceConnected = false,
    this.isPreTestPassed = false,
    this.isUserReady = false,
    this.currentStepName,
    this.currentStepIndex,
    this.totalSteps,
    this.finalScore,
    this.interruptReason,
  });

  bool get isReadyToLaunch => isDeviceConnected && isPreTestPassed && isUserReady;

  // Future JSON serialization logic for Laravel + Unity sync
  factory VrTrainingSession.fromJson(Map<String, dynamic> json) {
    return VrTrainingSession(
      sessionId: json['session_id'] as String,
      moduleId: json['module_id'] as String,
      moduleTitle: json['module_title'] as String,
      moduleDescription: json['module_description'] as String,
      phase: _parsePhase(json['phase'] as String?),
      estimatedDurationMinutes: json['estimated_duration_minutes'] as int? ?? 15,
      timeElapsedSeconds: json['time_elapsed_seconds'] as int?,
      isDeviceConnected: json['is_device_connected'] as bool? ?? false,
      isPreTestPassed: json['is_pre_test_passed'] as bool? ?? false,
      isUserReady: json['is_user_ready'] as bool? ?? false,
      currentStepName: json['current_step_name'] as String?,
      currentStepIndex: json['current_step_index'] as int?,
      totalSteps: json['total_steps'] as int?,
      finalScore: json['final_score'] as int?,
      interruptReason: json['interrupt_reason'] as String?,
    );
  }

  static VrSessionPhase _parsePhase(String? phaseConfig) {
    switch (phaseConfig) {
      case 'in_progress': return VrSessionPhase.inProgress;
      case 'completed': return VrSessionPhase.completed;
      case 'interrupted': return VrSessionPhase.interrupted;
      case 'failed': return VrSessionPhase.failed;
      default: return VrSessionPhase.launchReady;
    }
  }
}
