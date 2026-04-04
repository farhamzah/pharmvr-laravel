import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pharmvrpro/features/vr_experience/data/repositories/vr_repository.dart';
import 'package:pharmvrpro/features/vr_experience/domain/models/vr_training_session.dart';

enum VrConnectionStatus {
  idle,
  pairing,
  ready,
  inProgress,
  offline,
}

class VrConnectionState {
  final VrConnectionStatus status;
  final int batteryLevel;
  final String? activeModuleSlug;
  final String? error;
  final String? pairingCode;
  final String? pairingToken;
  final int? pairingId;
  final DateTime? expiresAt;
  final String? headsetName;
  final bool isPaired;

  const VrConnectionState({
    this.status = VrConnectionStatus.idle,
    this.batteryLevel = 0,
    this.activeModuleSlug,
    this.error,
    this.pairingCode,
    this.pairingToken,
    this.pairingId,
    this.expiresAt,
    this.headsetName,
    this.isPaired = false,
  });

  /// QR payload URL for Meta Quest 3 to scan
  String get qrPayload {
    if (pairingToken == null) return '';
    return 'pharmvr://pair?token=$pairingToken&code=$pairingCode&slug=$activeModuleSlug';
  }

  VrConnectionState copyWith({
    VrConnectionStatus? status,
    int? batteryLevel,
    String? activeModuleSlug,
    String? error,
    String? pairingCode,
    String? pairingToken,
    int? pairingId,
    DateTime? expiresAt,
    String? headsetName,
    bool? isPaired,
    bool clearError = false,
    bool clearPairing = false,
  }) {
    return VrConnectionState(
      status: status ?? this.status,
      batteryLevel: batteryLevel ?? this.batteryLevel,
      activeModuleSlug: activeModuleSlug ?? this.activeModuleSlug,
      error: clearError ? null : (error ?? this.error),
      pairingCode: clearPairing ? null : (pairingCode ?? this.pairingCode),
      pairingToken: clearPairing ? null : (pairingToken ?? this.pairingToken),
      pairingId: clearPairing ? null : (pairingId ?? this.pairingId),
      expiresAt: clearPairing ? null : (expiresAt ?? this.expiresAt),
      headsetName: headsetName ?? this.headsetName,
      isPaired: isPaired ?? this.isPaired,
    );
  }
}

class VrConnectionNotifier extends Notifier<VrConnectionState> {
  Timer? _pollingTimer;

  @override
  VrConnectionState build() {
    ref.onDispose(() => _pollingTimer?.cancel());
    
    // Automatically sync on build to get initial status
    Future.microtask(() => syncConnectionStatus());
    
    return const VrConnectionState();
  }

  VrRepository get _repository => ref.read(vrRepositoryProvider);

  /// Initiate a new pairing session with the backend
  Future<void> initiatePairing(String moduleSlug) async {
    try {
      state = state.copyWith(status: VrConnectionStatus.pairing, error: null);
      
      final data = await _repository.startPairing();
      
      state = state.copyWith(
        pairingId: data['id'], // Use the correct key from backend
        pairingCode: data['pairing_code'],
        pairingToken: data['pairing_token'],
        expiresAt: data['expires_at'] != null ? DateTime.parse(data['expires_at']) : null,
        activeModuleSlug: moduleSlug,
      );

      _startPairingStatusPolling();
    } catch (e) {
      state = state.copyWith(status: VrConnectionStatus.idle, error: e.toString());
    }
  }

  void _startPairingStatusPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 3), (timer) async {
      try {
        final data = await _repository.getCurrentPairing();
        if (data != null && data['status'] == 'confirmed') {
          timer.cancel();
          await syncConnectionStatus();
        } else if (data == null || data['status'] == 'expired' || data['status'] == 'cancelled') {
          timer.cancel();
          state = state.copyWith(status: VrConnectionStatus.idle, clearPairing: true);
        }
      } catch (_) {
        // Continue polling
      }
    });
  }

  /// Sync device status from backend
  Future<void> syncConnectionStatus() async {
    try {
      final status = await _repository.getVrStatus();
      
      VrConnectionStatus connStatus;
      switch (status['connection_status']) {
        case 'connected':
          connStatus = status['active_session_id'] != null 
              ? VrConnectionStatus.inProgress 
              : VrConnectionStatus.ready;
          break;
        case 'standby':
          connStatus = VrConnectionStatus.ready;
          break;
        default:
          connStatus = VrConnectionStatus.offline;
      }

      state = state.copyWith(
        status: connStatus,
        isPaired: status['paired'] ?? false,
        headsetName: status['headset_name'],
        activeModuleSlug: status['active_module_summary']?['slug'] ?? state.activeModuleSlug,
        batteryLevel: 85, // Placeholder as real battery is not yet in API
      );

      // If active session exists, start session polling
      if (connStatus == VrConnectionStatus.inProgress) {
        _startSessionStatusPolling();
      }
    } catch (e) {
      // Don't override status with offline on simple network error during sync
      // state = state.copyWith(error: e.toString());
    }
  }

  void _startSessionStatusPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (timer) async {
      try {
        final session = await _repository.getCurrentSession();
        if (session == null || 
            session['session_status'] == 'completed' || 
            session['session_status'] == 'interrupted') {
          timer.cancel();
          await syncConnectionStatus();
        }
      } catch (_) {
        // Continue polling
      }
    });
  }

  /// Manually start a session from mobile app
  Future<void> launchSession(String moduleSlug) async {
    try {
      state = state.copyWith(status: VrConnectionStatus.inProgress, error: null);
      await _repository.startMobileSession(moduleSlug);
      _startSessionStatusPolling();
    } catch (e) {
      state = state.copyWith(status: VrConnectionStatus.ready, error: e.toString());
    }
  }

  void disconnect() {
    state = const VrConnectionState();
    _pollingTimer?.cancel();
  }
}

final currentSessionProvider = StreamProvider<VrTrainingSession?>((ref) async* {
  final repo = ref.watch(vrRepositoryProvider);
  final connState = ref.watch(vrConnectionProvider);
  
  if (connState.status != VrConnectionStatus.inProgress) {
    yield null;
    return;
  }

  while (true) {
    try {
      final data = await repo.getCurrentSession();
      if (data != null) {
        yield VrTrainingSession.fromJson(data);
      } else {
        yield null;
      }
    } catch (_) {
      // Ignore errors in stream
    }
    await Future.delayed(const Duration(seconds: 3));
  }
});

final vrConnectionProvider = NotifierProvider<VrConnectionNotifier, VrConnectionState>(() {
  return VrConnectionNotifier();
});

