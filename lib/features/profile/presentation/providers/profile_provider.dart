import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/models/user.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/data_sources/profile_data_source.dart';
import '../../data/repositories/profile_repository_impl.dart';
import '../../../auth/presentation/providers/auth_provider.dart';

final profileDataSourceProvider = Provider<ProfileDataSource>((ref) {
  final dio = ref.watch(dioProvider);
  return ProfileDataSource(dio);
});

final profileRepositoryProvider = Provider<ProfileRepository>((ref) {
  final dataSource = ref.watch(profileDataSourceProvider);
  return ProfileRepositoryImpl(dataSource);
});

class ProfileState {
  final User? user;
  final bool isLoading;
  final String? error;

  const ProfileState({
    this.user,
    this.isLoading = false,
    this.error,
  });

  ProfileState copyWith({
    User? user,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return ProfileState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class ProfileNotifier extends Notifier<ProfileState> {
  @override
  ProfileState build() {
    // Listen to changes in authProvider globally so profile dynamically updates
    ref.listen(authProvider.select((s) => s.user), (previous, next) {
      if (next != state.user) {
        state = state.copyWith(user: next);
      }
    });

    // Start with authProvider user initial state
    final authUser = ref.read(authProvider).user;
    return ProfileState(user: authUser);
  }

  Future<void> fetchProfile() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final repository = ref.read(profileRepositoryProvider);
      final user = await repository.getProfile();
      state = state.copyWith(isLoading: false, user: user);
      
      // Sync to global auth state
      ref.read(authProvider.notifier).updateUser(user);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  Future<void> updateProfile(Map<String, dynamic> data) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final repository = ref.read(profileRepositoryProvider);
      final user = await repository.updateProfile(data);
      state = state.copyWith(isLoading: false, user: user);
      
      // Sync to global auth state so all screens update
      ref.read(authProvider.notifier).updateUser(user);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final repository = ref.read(profileRepositoryProvider);
      await repository.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );
      state = state.copyWith(isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  String _mapError(Object e) {
    if (e is DioException) {
      return e.error?.toString() ?? 'Terjadi kesalahan pada server.';
    }
    return e.toString();
  }
}

final profileProvider = NotifierProvider<ProfileNotifier, ProfileState>(() {
  return ProfileNotifier();
});
