class AiSession {
  final String id;
  final String title;
  final String? lastMessagePreview;
  final DateTime createdAt;
  final DateTime updatedAt;
  final String? moduleId;
  final String? assistantMode;

  const AiSession({
    required this.id,
    required this.title,
    this.lastMessagePreview,
    required this.createdAt,
    required this.updatedAt,
    this.moduleId,
    this.assistantMode,
  });

  factory AiSession.fromJson(Map<String, dynamic> json) {
    return AiSession(
      id: json['id'].toString(),
      title: json['session_title'] as String? ?? 'New Chat Session',
      lastMessagePreview: json['last_message'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
      moduleId: json['module_id']?.toString(),
      assistantMode: json['assistant_mode'] as String?,
    );
  }
}
