import 'cited_source.dart';

enum AiSender { user, assistant, system }

class AiMessage {
  final String id;
  final String text;
  final AiSender sender;
  final DateTime? timestamp;
  final List<CitedSource>? citations;
  final bool isTyping;
  final List<String>? suggestedFollowUps;
  final bool isRestricted; // For out-of-domain answers
  final String? responseMode; // grounded, restricted, neutral

  const AiMessage({
    required this.id,
    required this.text,
    required this.sender,
    this.timestamp,
    this.citations,
    this.isTyping = false,
    this.suggestedFollowUps,
    this.isRestricted = false,
    this.responseMode,
  });

  factory AiMessage.fromJson(Map<String, dynamic> json) {
    return AiMessage(
      id: json['id'].toString(),
      text: json['message_text'] as String? ?? '',
      sender: _parseSender(json['sender']?.toString().toLowerCase()),
      timestamp: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null,
      citations: (json['cited_sources_json'] as List?)?.map((c) => CitedSource.fromJson(c)).toList(),
      suggestedFollowUps: (json['suggested_followups'] as List?)?.map((q) => q.toString()).toList(),
      isRestricted: json['response_mode'] == 'restricted',
      responseMode: json['response_mode'] as String?,
    );
  }

  static AiSender _parseSender(String? role) {
    if (role == 'user') return AiSender.user;
    if (role == 'system') return AiSender.system;
    return AiSender.assistant;
  }
}
