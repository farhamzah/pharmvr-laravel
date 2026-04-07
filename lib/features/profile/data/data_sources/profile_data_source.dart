import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http_parser/http_parser.dart';

class ProfileDataSource {
  final Dio _dio;

  ProfileDataSource(this._dio);

  Future<Map<String, dynamic>> getProfile() async {
    final response = await _dio.get('/profile');
    return response.data['data'];
  }

  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    final formData = FormData.fromMap(data);
    
    if (data.containsKey('avatar_path') && data['avatar_path'] != null) {
      final file = XFile(data['avatar_path']);
      final bytes = await file.readAsBytes();
      
      String filename = file.name;
      final ext = filename.contains('.') ? filename.split('.').last.toLowerCase() : 'jpg';
      final mimeType = (ext == 'png') ? 'image/png' : 'image/jpeg';
      
      if (!filename.contains('.')) {
        filename += (ext == 'png') ? '.png' : '.jpg';
      }
      
      formData.files.add(MapEntry(
        'avatar',
        MultipartFile.fromBytes(
          bytes, 
          filename: filename,
          contentType: MediaType.parse(mimeType),
        ),
      ));
      // Remove the path from data since we use 'avatar' key for the file
      formData.fields.removeWhere((e) => e.key == 'avatar_path');
    }

    try {
      final response = await _dio.post(
        '/profile', 
        data: formData,
      );
      return response.data['data'];
    } catch (e) {
      if (e is DioException) {
        print('Profile Update Error Response: ${e.response?.data}');
      }
      rethrow;
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    await _dio.put('/profile/password', data: {
      'current_password': currentPassword,
      'password': newPassword,
      'password_confirmation': newPasswordConfirmation,
    });
  }
}
