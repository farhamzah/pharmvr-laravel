import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_provider.dart';
import '../../data/repositories/news_repository.dart';
import '../../domain/models/news_article.dart';

final newsRepositoryProvider = Provider<NewsRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return NewsRepository(dio);
});

class NewsFilter {
  final String? type;
  final String? topicCategory;
  final String? search;

  const NewsFilter({this.type, this.topicCategory, this.search});

  NewsFilter copyWith({
    String? type,
    String? topicCategory,
    String? search,
    bool clearType = false,
    bool clearTopicCategory = false,
  }) {
    return NewsFilter(
      type: clearType ? null : (type ?? this.type),
      topicCategory: clearTopicCategory ? null : (topicCategory ?? this.topicCategory),
      search: search ?? this.search,
    );
  }
}

class NewsFilterNotifier extends Notifier<NewsFilter> {
  @override
  NewsFilter build() => const NewsFilter();

  void updateState(NewsFilter newFilter) {
    state = newFilter;
  }
}

final newsFilterProvider = NotifierProvider<NewsFilterNotifier, NewsFilter>(NewsFilterNotifier.new);

class NewsNotifier extends AsyncNotifier<List<NewsArticle>> {
  @override
  Future<List<NewsArticle>> build() async {
    final filter = ref.watch(newsFilterProvider);
    final repository = ref.watch(newsRepositoryProvider);
    return repository.getNews(
      type: filter.type,
      topic: filter.topicCategory,
      search: filter.search,
    );
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final filter = ref.read(newsFilterProvider);
      final repository = ref.read(newsRepositoryProvider);
      return repository.getNews(
        type: filter.type,
        topic: filter.topicCategory,
        search: filter.search,
      );
    });
  }
}

final newsProvider = AsyncNotifierProvider<NewsNotifier, List<NewsArticle>>(() {
  return NewsNotifier();
});

final articleDetailProvider = FutureProvider.family<NewsDetail, String>((ref, slug) async {
  final repository = ref.watch(newsRepositoryProvider);
  return repository.getNewsDetail(slug);
});
