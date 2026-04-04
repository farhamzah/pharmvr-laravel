import 'package:dio/dio.dart';
import '../../domain/models/home_hub.dart';

class HomeRepository {
  final Dio _dio;

  HomeRepository(this._dio);

  Future<HomeHubData> getHomeData() async {
    final response = await _dio.get('/home');
    return HomeHubData.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}
