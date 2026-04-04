import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/widgets/pharm_loading_indicator.dart';
import '../../../../core/widgets/states/pharm_error_state.dart';
import '../providers/news_provider.dart';
import '../widgets/news_article_hero.dart';
import '../widgets/news_related_content.dart';

class NewsExternalDetailScreen extends ConsumerWidget {
  final String articleId;

  const NewsExternalDetailScreen({
    super.key,
    required this.articleId,
  });

  Future<void> _launchUrl(String? urlStr) async {
    if (urlStr == null || urlStr.isEmpty) return;
    final Uri url = Uri.parse(urlStr);
    if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
      debugPrint('Could not launch $urlStr');
    }
  }

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
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              NewsArticleHero(article: detail.article),
              
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg, vertical: PharmSpacing.xl),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // AI Summary section
                    Container(
                      padding: const EdgeInsets.all(PharmSpacing.lg),
                      decoration: BoxDecoration(
                        color: const Color(0xFFA855F7).withOpacity(0.05),
                        border: Border.all(color: const Color(0xFFA855F7).withOpacity(0.2)),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.auto_awesome, color: Color(0xFFA855F7), size: 20),
                              const SizedBox(width: 8),
                              Text(
                                'AI-Curated Summary in Bahasa Indonesia',
                                style: PharmTextStyles.subtitle.copyWith(color: const Color(0xFFA855F7), fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          const SizedBox(height: PharmSpacing.md),
                          Text(
                            detail.article.aiSummary ?? detail.article.excerpt,
                            style: PharmTextStyles.bodyLarge.copyWith(height: 1.6, color: PharmColors.textSecondary),
                          ),
                          
                          if (detail.article.aiTags != null && detail.article.aiTags!.isNotEmpty) ...[
                            const SizedBox(height: PharmSpacing.lg),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: detail.article.aiTags!.map((tag) => Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: PharmColors.surfaceLight,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text('#$tag', style: PharmTextStyles.caption.copyWith(color: PharmColors.textSecondary)),
                              )).toList(),
                            ),
                          ]
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: PharmSpacing.xl),
                    
                    // Disclaimer
                    Container(
                      padding: const EdgeInsets.all(PharmSpacing.md),
                      decoration: BoxDecoration(
                        color: Colors.amber.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.info_outline, color: Colors.amber, size: 20),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              'Disclaimer: Artikel asli disediakan oleh ${detail.article.sourceName ?? "pihak ketiga"}. Ringkasan di atas dibuat menggunakan AI untuk tujuan referensi edukasi dan tidak menggantikan konteks penuh artikel sumber.',
                              style: PharmTextStyles.bodySmall.copyWith(color: Colors.amber.shade200, height: 1.4),
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: PharmSpacing.xl),
                    
                    // Read Original Button
                    ElevatedButton(
                      onPressed: () => _launchUrl(detail.article.originalUrl),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: PharmColors.primary,
                        foregroundColor: PharmColors.background,
                        padding: const EdgeInsets.symmetric(vertical: PharmSpacing.md),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        elevation: 0,
                        minimumSize: const Size(double.infinity, 56),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.open_in_new_rounded, size: 20),
                          const SizedBox(width: 8),
                          Text(
                            'BACA ARTIKEL ASLI DI ${detail.article.sourceName?.toUpperCase() ?? "SITUS SUMBER"}',
                            style: PharmTextStyles.button.copyWith(fontWeight: FontWeight.bold, letterSpacing: 1),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              
              NewsRelatedContent(relatedArticles: detail.relatedNews),
              const SizedBox(height: 100),
            ],
          ),
        ),
      ),
    );
  }
}
