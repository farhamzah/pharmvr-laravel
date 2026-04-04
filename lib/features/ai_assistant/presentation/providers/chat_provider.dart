import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pharmvrpro/core/network/dio_provider.dart';
import 'package:pharmvrpro/features/ai_assistant/data/ai_assistant_api_service.dart';
import 'package:pharmvrpro/features/ai_assistant/data/ai_assistant_repository.dart';
import 'package:pharmvrpro/features/ai_assistant/domain/models/ai_session.dart';
import 'package:pharmvrpro/features/ai_assistant/domain/models/ai_message.dart';

// Providers for Data Layer
final aiAssistantApiServiceProvider = Provider<AiAssistantApiService>((ref) {
  final dio = ref.watch(dioProvider);
  return AiAssistantApiService(dio);
});

final aiAssistantRepositoryProvider = Provider<AiAssistantRepository>((ref) {
  return AiAssistantRepository(ref.watch(aiAssistantApiServiceProvider));
});

// Providers for UI State (Sessions list)
class AiSessionsNotifier extends AsyncNotifier<List<AiSession>> {
  @override
  Future<List<AiSession>> build() async {
    return ref.watch(aiAssistantRepositoryProvider).getSessions();
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() => ref.read(aiAssistantRepositoryProvider).getSessions());
  }
}

final aiSessionsProvider = AsyncNotifierProvider<AiSessionsNotifier, List<AiSession>>(AiSessionsNotifier.new);

// Active Chat Session Notifier
class ActiveSessionIdNotifier extends Notifier<String?> {
  @override
  String? build() => null;
  void set(String? id) => state = id;
}

final activeSessionIdProvider = NotifierProvider<ActiveSessionIdNotifier, String?>(
  ActiveSessionIdNotifier.new,
);

// Chat Messages Provider (Data fetching)
final chatMessagesProvider = FutureProvider.family<List<AiMessage>, String>((ref, sessionId) {
  return ref.watch(aiAssistantRepositoryProvider).getMessages(sessionId);
});

// Chat Controller for sending messages
class ChatController {
  final Ref _ref;
  final String _sessionId;

  ChatController(this._ref, this._sessionId);

  Future<void> sendMessage({
    required String text,
    String? assistantMode,
    String? moduleId,
  }) async {
    try {
      await _ref.read(aiAssistantRepositoryProvider).askQuestion(
        sessionId: _sessionId,
        question: text,
        assistantMode: assistantMode,
        moduleId: moduleId,
      );
      // Refresh messages
      _ref.invalidate(chatMessagesProvider(_sessionId));
    } catch (e) {
      rethrow;
    }
  }
}

final chatControllerProvider = Provider.family<ChatController, String>((ref, sessionId) {
  return ChatController(ref, sessionId);
});
