import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../providers/news_provider.dart';
import '../widgets/news_article_hero.dart';
import '../widgets/news_article_body.dart';
import '../widgets/news_related_content.dart';

class NewsDetailScreen extends ConsumerWidget {
  final String articleId;

  const NewsDetailScreen({
    super.key,
    required this.articleId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final articleState = ref.watch(articleDetailProvider(articleId));

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      extendBodyBehindAppBar: true, 
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: IconThemeData(
          color: Theme.of(context).brightness == Brightness.dark 
              ? Colors.white 
              : PharmColors.primary,
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.bookmark_border_rounded),
            onPressed: () {},
            tooltip: 'Bookmark Article',
          ),
          IconButton(
            icon: const Icon(Icons.share_rounded),
            onPressed: () {},
            tooltip: 'Share',
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: articleState.when(
        loading: () => const Center(child: PharmLoadingIndicator()),
        error: (error, stack) => PharmErrorState.generic(
          message: error.toString(),
          onRetry: () => ref.refresh(articleDetailProvider(articleId)),
        ),
        data: (detail) => SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              NewsArticleHero(article: detail.article),
              NewsArticleBody(article: detail.article),
              NewsRelatedContent(relatedArticles: detail.relatedNews),
            ],
          ),
        ),
      ),
    );
  }
}
