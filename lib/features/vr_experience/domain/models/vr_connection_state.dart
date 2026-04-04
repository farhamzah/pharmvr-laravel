enum VrConnectionStatus {
  idle,         // Initial state, no connection attempt yet
  pairing,      // Searching/Connecting via shortcode/network
  connected,    // Success, ready to launch
  failed        // Error occurred
}

class VrDeviceSession {
  final String? deviceId;
  final String? deviceName; // E.g. "Oculus Quest 3 (PharmVR Lab)"
  final DateTime? lastConnected;
  final String? activeShortCode; // e.g. "A49B"
  final String? errorMessage;
  final VrConnectionStatus status;

  const VrDeviceSession({
    this.deviceId,
    this.deviceName,
    this.lastConnected,
    this.activeShortCode,
    this.errorMessage,
    required this.status,
  });

  // Future JSON serialization logic for Laravel backend
  factory VrDeviceSession.fromJson(Map<String, dynamic> json) {
    return VrDeviceSession(
      deviceId: json['device_id'] as String?,
      deviceName: json['device_name'] as String?,
      lastConnected: json['last_connected'] != null 
          ? DateTime.parse(json['last_connected'] as String) 
          : null,
      activeShortCode: json['active_short_code'] as String?,
      errorMessage: json['error_message'] as String?,
      status: _parseStatus(json['status'] as String?),
    );
  }

  static VrConnectionStatus _parseStatus(String? status) {
    switch (status) {
      case 'pairing':
        return VrConnectionStatus.pairing;
      case 'connected':
        return VrConnectionStatus.connected;
      case 'failed':
        return VrConnectionStatus.failed;
      default:
        return VrConnectionStatus.idle;
    }
  }

  // Helper getters for UI
  bool get isIdle => status == VrConnectionStatus.idle;
  bool get isPairing => status == VrConnectionStatus.pairing;
  bool get isConnected => status == VrConnectionStatus.connected;
  bool get isFailed => status == VrConnectionStatus.failed;

  // Initial empty state
  const VrDeviceSession.initial()
      : deviceId = null,
        deviceName = null,
        lastConnected = null,
        activeShortCode = null,
        errorMessage = null,
        status = VrConnectionStatus.idle;
}
