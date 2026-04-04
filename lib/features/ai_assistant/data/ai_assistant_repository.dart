import '../domain/models/ai_session.dart';
import '../domain/models/ai_message.dart';
import 'ai_assistant_api_service.dart';

class AiAssistantRepository {
  final AiAssistantApiService _apiService;

  AiAssistantRepository(this._apiService);

  Future<List<AiSession>> getSessions() => _apiService.getSessions();
  
  Future<AiSession> getSession(String id) => _apiService.getSession(id);

  Future<List<AiMessage>> getMessages(String sessionId) => _apiService.getMessages(sessionId);

  Future<AiSession> startSession({String? title, String? moduleId, String? assistantMode}) => 
      _apiService.startSession(title: title, moduleId: moduleId, assistantMode: assistantMode);

  Future<AiMessage> askQuestion({
    required String sessionId,
    required String question,
    String? assistantMode,
    String? moduleId,
    String? sceneContext,
    String? objectContext,
  }) =>
      _apiService.askQuestion(
        sessionId: sessionId,
        question: question,
        assistantMode: assistantMode,
        moduleId: moduleId,
        sceneContext: sceneContext,
        objectContext: objectContext,
      );
}
