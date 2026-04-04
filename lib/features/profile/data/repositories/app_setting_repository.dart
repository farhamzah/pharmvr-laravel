import 'package:dio/dio.dart';
import '../../domain/models/app_setting.dart';

class AppSettingRepository {
  final Dio _dio;

  AppSettingRepository(this._dio);

  Future<AppSetting> getSettings() async {
    final response = await _dio.get('/app/settings');
    return AppSetting.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}
