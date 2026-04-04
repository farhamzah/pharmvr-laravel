class VrAiInteraction {
  final int interactionId;
  final String mode; // hint, reminder, feedback
  final String shortText;
  final String displayText;
  final String speechText;
  final String severity; // info, warning, error, success
  final String? recommendedNextAction;
  final Map<String, dynamic>? metadata;

  const VrAiInteraction({
    required this.interactionId,
    required this.mode,
    required this.shortText,
    required this.displayText,
    required this.speechText,
    required this.severity,
    this.recommendedNextAction,
    this.metadata,
  });

  factory VrAiInteraction.fromJson(Map<String, dynamic> json) {
    return VrAiInteraction(
      interactionId: json['interaction_id'] as int,
      mode: json['mode'] as String,
      shortText: json['short_text'] as String,
      displayText: json['display_text'] as String,
      speechText: json['speech_text'] as String,
      severity: json['severity'] as String? ?? 'info',
      recommendedNextAction: json['recommended_next_action'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }
}
