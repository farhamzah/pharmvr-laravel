import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../domain/models/news_article.dart';
import 'pharm_news_card.dart';

class NewsRelatedContent extends StatelessWidget {
  final List<NewsArticle> relatedArticles;

  const NewsRelatedContent({
    super.key,
    this.relatedArticles = const [],
  });

  @override
  Widget build(BuildContext context) {
    if (relatedArticles.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg, vertical: PharmSpacing.xl),
      decoration: BoxDecoration(
        color: PharmColors.surface.withOpacity(0.5),
        border: Border(
          top: BorderSide(color: PharmColors.divider.withOpacity(0.5)),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 4,
                height: 16,
                decoration: BoxDecoration(
                  color: PharmColors.primary,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 12),
              Text(
                'Related Articles',
                style: PharmTextStyles.h4.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.2,
                ),
              ),
            ],
          ),
          const SizedBox(height: PharmSpacing.lg),
          
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: relatedArticles.length,
            separatorBuilder: (context, index) => const SizedBox(height: PharmSpacing.md),
            itemBuilder: (context, index) {
              final article = relatedArticles[index];
              return PharmNewsCard(
                article: article,
                onTap: () {
                  context.pushReplacement('/news/detail/${article.slug}');
                },
              );
            },
          ),
          
          const SizedBox(height: PharmSpacing.xl),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: () {
                context.go('/news');
              },
              style: OutlinedButton.styleFrom(
                side: BorderSide(color: PharmColors.primary.withOpacity(0.2)),
                padding: const EdgeInsets.symmetric(vertical: 16),
                backgroundColor: PharmColors.primary.withOpacity(0.05),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: Text(
                'Explore More Intelligence',
                style: PharmTextStyles.button.copyWith(
                  color: PharmColors.primary,
                  fontSize: 12,
                ),
              ),
            ),
          ),
          const SizedBox(height: PharmSpacing.lg),
        ],
      ),
    );
  }
}
