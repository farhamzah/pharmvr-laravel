import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/vr_ai_interaction.dart';
import '../../data/repositories/vr_repository.dart';

class VrAiNotifier extends Notifier<VrAiInteraction?> {
  @override
  VrAiInteraction? build() => null;

  Future<void> fetchHint({
    required int sessionId,
    String? moduleSlug,
    String? currentStep,
    double? progressPercentage,
    required List<Map<String, dynamic>> recentEvents,
  }) async {
    try {
      final repo = ref.read(vrRepositoryProvider);
      final data = await repo.generateAiHint(
        sessionId: sessionId,
        moduleSlug: moduleSlug,
        currentStep: currentStep,
        progressPercentage: progressPercentage,
        recentEvents: recentEvents,
      );
      state = VrAiInteraction.fromJson(data);
    } catch (_) {
      // Keep existing state on error or handle as needed
    }
  }

  Future<void> fetchReminder({
    required int sessionId,
    required String topic,
    String? moduleSlug,
    String? currentStep,
    double? progressPercentage,
  }) async {
    try {
      final repo = ref.read(vrRepositoryProvider);
      final data = await repo.generateAiReminder(
        sessionId: sessionId,
        topic: topic,
        moduleSlug: moduleSlug,
        currentStep: currentStep,
        progressPercentage: progressPercentage,
      );
      state = VrAiInteraction.fromJson(data);
    } catch (_) {}
  }

  void clearInteraction() {
    state = null;
  }
}

final vrAiProvider = NotifierProvider<VrAiNotifier, VrAiInteraction?>(() {
  return VrAiNotifier();
});
