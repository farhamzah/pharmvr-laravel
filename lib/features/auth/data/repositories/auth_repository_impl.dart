import '../../../../core/models/user.dart';
import '../data_sources/auth_data_source.dart';

class AuthRepository {
  final AuthDataSource _dataSource;

  AuthRepository(this._dataSource);

  Future<AuthResponse> login(String email, String password) async {
    final data = await _dataSource.login(email, password);
    final responseData = data['data'];
    return AuthResponse(
      user: User.fromJson(responseData['user'] as Map<String, dynamic>),
      token: responseData['token'] as String,
    );
  }

  Future<AuthResponse> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final data = await _dataSource.register(
      name: name,
      email: email,
      password: password,
      passwordConfirmation: password,
    );
    final responseData = data['data'];
    return AuthResponse(
      user: User.fromJson(responseData['user'] as Map<String, dynamic>),
      token: responseData['token'] as String,
    );
  }

  Future<void> logout() => _dataSource.logout();

  Future<void> forgotPassword(String email) => _dataSource.forgotPassword(email);

  Future<User> getMe() async {
    final data = await _dataSource.getMe();
    return User.fromJson(data['data'] as Map<String, dynamic>);
  }
}

class AuthResponse {
  final User user;
  final String token;

  AuthResponse({required this.user, required this.token});
}
