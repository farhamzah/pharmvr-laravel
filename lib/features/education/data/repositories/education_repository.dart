import 'package:dio/dio.dart';
import '../../domain/models/learning_module.dart';

class EducationRepository {
  final Dio _dio;

  EducationRepository(this._dio);

  Future<List<LearningModule>> getModules({String? type}) async {
    final response = await _dio.get(
      '/edukasi',
      queryParameters: type != null ? {'content_type': type} : null,
    );
    final List data = response.data['data'];
    return data.map((json) => LearningModule.fromJson(json)).toList();
  }

  Future<EducationDetail> getModuleDetail(String slug) async {
    final response = await _dio.get('/edukasi/$slug');
    final module = LearningModule.fromJson(response.data['data']);
    final relatedData = response.data['related_content'] as List? ?? [];
    final relatedContent = relatedData.map((e) => LearningModule.fromJson(e)).toList();
    
    return EducationDetail(
      module: module,
      relatedContent: relatedContent,
    );
  }
}

class EducationDetail {
  final LearningModule module;
  final List<LearningModule> relatedContent;

  EducationDetail({
    required this.module,
    required this.relatedContent,
  });
}
