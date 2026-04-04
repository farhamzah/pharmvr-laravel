import 'package:dio/dio.dart';
import '../../domain/models/news_article.dart';

class NewsRepository {
  final Dio _dio;

  NewsRepository(this._dio);

  Future<List<NewsArticle>> getNews({
    String? type,
    String? category,
    String? topic,
    String? search,
    bool? featured,
  }) async {
    final queryParams = <String, dynamic>{};
    if (type != null) queryParams['type'] = type;
    if (category != null) queryParams['category'] = category;
    if (topic != null) queryParams['topic'] = topic;
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (featured == true) queryParams['is_featured'] = 'true';

    final response = await _dio.get('/news', queryParameters: queryParams);
    final List data = response.data['data'];
    return data.map((json) => NewsArticle.fromJson(json)).toList();
  }

  Future<NewsDetail> getNewsDetail(String slug) async {
    final response = await _dio.get('/news/$slug');
    final article = NewsArticle.fromJson(response.data['data']);
    final relatedData = (response.data['related'] ?? response.data['related_news']) as List? ?? [];
    final relatedArticles = relatedData.map((e) => NewsArticle.fromJson(e)).toList();
    
    return NewsDetail(
      article: article,
      relatedNews: relatedArticles,
    );
  }
}

class NewsDetail {
  final NewsArticle article;
  final List<NewsArticle> relatedNews;

  NewsDetail({
    required this.article,
    required this.relatedNews,
  });
}
