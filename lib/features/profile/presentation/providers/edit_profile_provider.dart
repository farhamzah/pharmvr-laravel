import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/data_sources/profile_data_source.dart';
import '../../data/repositories/profile_repository_impl.dart';
import '../../domain/models/edit_profile_request.dart';
import 'profile_provider.dart';
import 'package:pharmvrpro/features/dashboard/presentation/providers/dashboard_provider.dart';

class EditProfileState {
  final bool isLoading;
  final bool isSuccess;
  final String? error;

  const EditProfileState({
    this.isLoading = false,
    this.isSuccess = false,
    this.error,
  });

  EditProfileState copyWith({
    bool? isLoading,
    bool? isSuccess,
    String? error,
    bool clearError = false,
  }) {
    return EditProfileState(
      isLoading: isLoading ?? this.isLoading,
      isSuccess: isSuccess ?? this.isSuccess,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class EditProfileNotifier extends Notifier<EditProfileState> {
  @override
  EditProfileState build() {
    return const EditProfileState();
  }

  Future<void> submitProfileChange(EditProfileRequest request) async {
    state = state.copyWith(isLoading: true, clearError: true, isSuccess: false);
    
    try {
      final repository = ref.read(profileRepositoryProvider);
      
      final data = request.toJson();
      if (request.imagePath != null) {
        data['avatar_path'] = request.imagePath;
      }

      await ref.read(profileProvider.notifier).updateProfile(data);
      
      // Sync Dashboard greeting
      ref.invalidate(dashboardProvider);

      state = state.copyWith(isLoading: false, isSuccess: true);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final editProfileProvider = NotifierProvider<EditProfileNotifier, EditProfileState>(() {
  return EditProfileNotifier();
});
