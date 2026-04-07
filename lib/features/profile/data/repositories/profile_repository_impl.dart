import '../../../../core/models/user.dart';
import '../data_sources/profile_data_source.dart';

abstract class ProfileRepository {
  Future<User> getProfile();
  Future<User> updateProfile(Map<String, dynamic> data);
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  });
}

class ProfileRepositoryImpl implements ProfileRepository {
  final ProfileDataSource _dataSource;

  ProfileRepositoryImpl(this._dataSource);

  @override
  Future<User> getProfile() async {
    final response = await _dataSource.getProfile();
    return User.fromJson(response);
  }

  @override
  Future<User> updateProfile(Map<String, dynamic> data) async {
    final response = await _dataSource.updateProfile(data);
    return User.fromJson(response);
  }

  @override
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    await _dataSource.changePassword(
      currentPassword: currentPassword,
      newPassword: newPassword,
      newPasswordConfirmation: newPasswordConfirmation,
    );
  }
}
