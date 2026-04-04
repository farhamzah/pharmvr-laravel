import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/vr_repository.dart';

class VrReadinessState {
  final bool isLoading;
  final bool isEligible;
  final List<VrChecklistItem> checklist;
  final List<String> blockingReasons;
  final String? error;

  VrReadinessState({
    this.isLoading = false,
    this.isEligible = false,
    this.checklist = const [],
    this.blockingReasons = const [],
    this.error,
  });

  VrReadinessState copyWith({
    bool? isLoading,
    bool? isEligible,
    List<VrChecklistItem>? checklist,
    List<String>? blockingReasons,
    String? error,
  }) {
    return VrReadinessState(
      isLoading: isLoading ?? this.isLoading,
      isEligible: isEligible ?? this.isEligible,
      checklist: checklist ?? this.checklist,
      blockingReasons: blockingReasons ?? this.blockingReasons,
      error: error,
    );
  }
}

class VrChecklistItem {
  final String label;
  final bool status;

  VrChecklistItem({required this.label, required this.status});

  factory VrChecklistItem.fromJson(Map<String, dynamic> json) {
    return VrChecklistItem(
      label: json['label'] as String,
      status: json['status'] as bool,
    );
  }
}

class VrReadinessNotifier extends Notifier<VrReadinessState> {
  @override
  VrReadinessState build() {
    return VrReadinessState();
  }

  Future<void> fetchReadiness(String moduleSlug) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final data = await ref.read(vrRepositoryProvider).getLaunchReadiness(moduleSlug);
      
      final checklist = (data['checklist'] as List)
          .map((item) => VrChecklistItem.fromJson(item as Map<String, dynamic>))
          .toList();
      
      state = state.copyWith(
        isLoading: false,
        isEligible: data['eligible_to_launch'] as bool,
        checklist: checklist,
        blockingReasons: List<String>.from(data['blocking_reasons'] ?? []),
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final vrReadinessProvider = NotifierProvider<VrReadinessNotifier, VrReadinessState>(() {
  return VrReadinessNotifier();
});
