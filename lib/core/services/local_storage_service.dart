import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class LocalStorageService {
  static const _rememberMeEmailKey = 'remember_me_email';
  
  LocalStorageService();

  Future<void> saveRememberMeEmail(String email) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_rememberMeEmailKey, email);
  }

  Future<String?> getRememberMeEmail() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_rememberMeEmailKey);
  }

  Future<void> clearRememberMeEmail() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_rememberMeEmailKey);
  }
}

final localStorageProvider = Provider<LocalStorageService>((ref) {
  return LocalStorageService();
});
