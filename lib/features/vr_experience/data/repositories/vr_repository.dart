import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';

class VrRepository {
  final Dio _dio;

  VrRepository(this._dio);

  /// Start a pairing session
  /// Returns {pairing_id, pairing_code, pairing_token, expires_at, status}
  Future<Map<String, dynamic>> startPairing({String? trainingModuleId}) async {
    final response = await _dio.post('/vr/pairings/start', data: {
      if (trainingModuleId != null) 'training_module_id': trainingModuleId,
    });
    return response.data['data'];
  }

  /// Check current pairing status
  Future<Map<String, dynamic>?> getCurrentPairing() async {
    final response = await _dio.get('/vr/pairings/current');
    return response.data['data'];
  }

  /// Cancel a pending pairing
  Future<void> cancelPairing(int pairingId) async {
    await _dio.post('/vr/pairings/$pairingId/cancel');
  }

  /// Get current VR connection status
  Future<Map<String, dynamic>> getVrStatus() async {
    final response = await _dio.get('/vr/status');
    return response.data['data'];
  }

  /// Get launch readiness for a module
  Future<Map<String, dynamic>> getLaunchReadiness(String moduleSlug) async {
    final response = await _dio.get('/vr/modules/$moduleSlug/launch-readiness');
    return response.data['data'];
  }

  /// Start a VR session from mobile
  Future<Map<String, dynamic>> startMobileSession(String moduleSlug) async {
    final response = await _dio.post('/vr/sessions/start', data: {
      'module_slug': moduleSlug,
    });
    return response.data['data'];
  }

  /// Get current active session details
  Future<Map<String, dynamic>?> getCurrentSession() async {
    final response = await _dio.get('/vr/sessions/current');
    return response.data['data'];
  }

  /// Get specific session details
  Future<Map<String, dynamic>> getSessionDetail(int sessionId) async {
    final response = await _dio.get('/vr/sessions/$sessionId');
    return response.data['data'];
  }

  /// Generate AI Hint
  Future<Map<String, dynamic>> generateAiHint({
    required int sessionId,
    String? moduleSlug,
    String? currentStep,
    double? progressPercentage,
    required List<Map<String, dynamic>> recentEvents,
  }) async {
    final response = await _dio.post('/vr/ai/hint', data: {
      'session_id': sessionId,
      if (moduleSlug != null) 'module_slug': moduleSlug,
      if (currentStep != null) 'current_step': currentStep,
      if (progressPercentage != null) 'progress_percentage': progressPercentage,
      'recent_events': recentEvents,
    });
    return response.data['data'];
  }

  /// Generate AI Reminder
  Future<Map<String, dynamic>> generateAiReminder({
    required int sessionId,
    required String topic,
    String? moduleSlug,
    String? currentStep,
    double? progressPercentage,
  }) async {
    final response = await _dio.post('/vr/ai/reminder', data: {
      'session_id': sessionId,
      'topic': topic,
      if (moduleSlug != null) 'module_slug': moduleSlug,
      if (currentStep != null) 'current_step': currentStep,
      if (progressPercentage != null) 'progress_percentage': progressPercentage,
    });
    return response.data['data'];
  }

  /// Generate AI Feedback
  Future<Map<String, dynamic>> generateAiFeedback({
    required int sessionId,
    required String eventType,
    required Map<String, dynamic> event,
    String? moduleSlug,
    String? currentStep,
    double? progressPercentage,
    String? userActionSummary,
  }) async {
    final response = await _dio.post('/vr/ai/feedback', data: {
      'session_id': sessionId,
      'event_type': eventType,
      'event': event,
      if (moduleSlug != null) 'module_slug': moduleSlug,
      if (currentStep != null) 'current_step': currentStep,
      if (progressPercentage != null) 'progress_percentage': progressPercentage,
      if (userActionSummary != null) 'user_action_summary': userActionSummary,
    });
    return response.data['data'];
  }
}

final vrRepositoryProvider = Provider<VrRepository>((ref) {
  return VrRepository(ref.watch(dioProvider));
});
