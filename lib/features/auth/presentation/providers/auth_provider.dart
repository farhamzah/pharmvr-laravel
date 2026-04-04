import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/models/user.dart';
import '../../../../core/network/dio_provider.dart';
import '../../../../core/network/api_exception.dart';
import '../../data/data_sources/auth_data_source.dart';
import '../../data/repositories/auth_repository_impl.dart';
import '../../../dashboard/presentation/providers/dashboard_provider.dart';
import '../../../news/presentation/providers/news_provider.dart';
import '../../../education/presentation/providers/education_provider.dart';

final authDataSourceProvider = Provider<AuthDataSource>((ref) {
  final dio = ref.watch(dioProvider);
  return AuthDataSource(dio);
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final dataSource = ref.watch(authDataSourceProvider);
  return AuthRepository(dataSource);
});

class AuthState {
  final bool isAuthenticated;
  final bool isLoading;
  final bool registrationSuccess;
  final String? error;
  final User? user;
  final String? token;

  const AuthState({
    this.isAuthenticated = false,
    this.isLoading = false,
    this.registrationSuccess = false,
    this.error,
    this.user,
    this.token,
  });

  AuthState copyWith({
    bool? isAuthenticated,
    bool? isLoading,
    bool? registrationSuccess,
    String? error,
    User? user,
    String? token,
    bool clearError = false,
  }) {
    return AuthState(
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      isLoading: isLoading ?? this.isLoading,
      registrationSuccess: registrationSuccess ?? (clearError ? false : this.registrationSuccess),
      error: clearError ? null : (error ?? this.error),
      user: user ?? this.user,
      token: token ?? this.token,
    );
  }
}

class AuthNotifier extends Notifier<AuthState> {
  @override
  AuthState build() => const AuthState();

  Future<void> login(String email, String password, bool rememberMe) async {
    state = state.copyWith(isLoading: true, clearError: true, registrationSuccess: false);
    try {
      final repository = ref.read(authRepositoryProvider);
      final response = await repository.login(email, password);

      // ALWAYS save token for the current session to ensure Dio interceptor picks it up
      final secureStorage = ref.read(secureStorageProvider);
      await secureStorage.saveToken(response.token);

      state = state.copyWith(
        isLoading: false, 
        isAuthenticated: true,
        user: response.user,
        token: response.token,
      );

      // Invalidate providers to force refresh data for the new user
      _invalidateDataProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  Future<void> checkAuth() async {
    final secureStorage = ref.read(secureStorageProvider);
    final token = await secureStorage.getToken();
    
    if (token != null) {
      state = state.copyWith(token: token, isLoading: true);
      try {
        final repository = ref.read(authRepositoryProvider);
        final user = await repository.getMe();
        
        state = state.copyWith(
          isAuthenticated: true,
          user: user,
          isLoading: false,
        );
      } catch (e) {
        // If /me fails (e.g. token expired), clear state
        await logout();
      }
    }
  }

  Future<void> register(String name, String email, String password) async {
    state = state.copyWith(isLoading: true, clearError: true, registrationSuccess: false);
    try {
      final repository = ref.read(authRepositoryProvider);
      // Still call register, but we won't use the token to authenticate locally
      await repository.register(
        name: name,
        email: email,
        password: password,
      );
      
      state = state.copyWith(
        isLoading: false, 
        isAuthenticated: false, // Do NOT auto-login
        registrationSuccess: true, // Mark registration as successful
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  Future<void> logout() async {
    try {
      // Best effort notify backend
      if (state.token != null) {
        await ref.read(authRepositoryProvider).logout();
      }
    } catch (_) {}

    final secureStorage = ref.read(secureStorageProvider);
    await secureStorage.deleteToken();
    state = const AuthState(isAuthenticated: false);
    
    // Refresh UI state
    _invalidateDataProviders();
  }

  void _invalidateDataProviders() {
    ref.invalidate(dashboardProvider);
    ref.invalidate(newsProvider);
    ref.invalidate(edukasiProvider);
  }
  
  void resetState() {
    state = const AuthState();
  }

  Future<void> resetPassword(String email) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final repository = ref.read(authRepositoryProvider);
      await repository.forgotPassword(email);
      state = state.copyWith(isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _mapError(e));
    }
  }

  void updateUser(User user) {
    state = state.copyWith(user: user);
  }

  void clearError() {
    if (state.error != null) {
      state = state.copyWith(clearError: true);
    }
  }

  String _mapError(Object e) {
    if (e is DioException) {
      final error = e.error;
      if (error is ApiException) {
        return error.message;
      }
      if (e.type == DioExceptionType.connectionTimeout || e.type == DioExceptionType.receiveTimeout) {
        return 'Koneksi ke server terputus/lambat.';
      }
    }
    return 'Terjadi kesalahan. Silakan coba lagi.';
  }
}

final authProvider = NotifierProvider<AuthNotifier, AuthState>(() {
  return AuthNotifier();
});
