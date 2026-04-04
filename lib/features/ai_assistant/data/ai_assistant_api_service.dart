import 'package:dio/dio.dart';
import '../domain/models/ai_session.dart';
import '../domain/models/ai_message.dart';

class AiAssistantApiService {
  final Dio _dio;

  AiAssistantApiService(this._dio);

  Future<List<AiSession>> getSessions() async {
    final response = await _dio.get('/ai-assistant/chat/sessions');
    final List data = response.data['data']?['data'] ?? []; // Pagination wrapper
    return data.map((json) => AiSession.fromJson(json)).toList();
  }

  Future<AiSession> getSession(String id) async {
    final response = await _dio.get('/ai-assistant/chat/sessions/$id');
    return AiSession.fromJson(response.data['data']);
  }

  Future<List<AiMessage>> getMessages(String sessionId) async {
    final response = await _dio.get('/ai-assistant/chat/sessions/$sessionId/messages');
    final List data = response.data['data'] ?? [];
    return data.map((json) => AiMessage.fromJson(json)).toList();
  }

  Future<AiSession> startSession({String? title, String? moduleId, String? assistantMode}) async {
    final response = await _dio.post('/ai-assistant/chat/start', data: {
      'session_title': title, // Match backend key
      'module_id': moduleId,
      'assistant_mode': assistantMode,
      'platform': 'mobile',
    });
    return AiSession.fromJson(response.data['data']);
  }

  Future<AiMessage> askQuestion({
    required String sessionId,
    required String question,
    String? assistantMode,
    String? moduleId,
    String? sceneContext,
    String? objectContext,
  }) async {
    final payload = {
      'session_id': sessionId,
      'question': question.trim(),
      'assistant_mode': assistantMode,
      'platform': 'mobile',
      'module_id': moduleId,
      'scene_context': sceneContext,
      'object_context': objectContext,
    };

    // Debug logging for development
    print('AI_REQUEST_PAYLOAD: $payload');

    final response = await _dio.post('/ai-assistant/chat/ask', data: payload);
    
    print('AI_RESPONSE_STATUS: ${response.statusCode}');
    print('AI_RESPONSE_BODY: ${response.data}');

    return AiMessage.fromJson(response.data['data']);
  }
}
