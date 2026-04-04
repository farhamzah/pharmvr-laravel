import 'package:flutter/material.dart';
import '../../domain/models/news_article.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';

class NewsArticleBody extends StatelessWidget {
  final NewsArticle article;

  const NewsArticleBody({
    super.key,
    required this.article,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Premium Introduction/Excerpt Block
        if (article.excerpt.isNotEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg, vertical: PharmSpacing.lg),
              decoration: BoxDecoration(
                color: PharmColors.surface.withOpacity(0.4),
                border: const Border(
                  left: BorderSide(
                    color: PharmColors.primary,
                    width: 4,
                  ),
                ),
                borderRadius: const BorderRadius.horizontal(right: Radius.circular(16)),
              ),
              child: Text(
                article.excerpt,
                style: PharmTextStyles.subtitle.copyWith(
                  color: Colors.white.withOpacity(0.85),
                  height: 1.7,
                  fontStyle: FontStyle.italic,
                  fontSize: 15,
                  letterSpacing: 0.1,
                ),
              ),
            ),
          ),
          
        const SizedBox(height: PharmSpacing.xl + 4),
        
        // 2. Main Article Content - High Density Readability
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.lg),
          child: Text(
            article.content ?? '',
            style: PharmTextStyles.bodyLarge.copyWith(
              color: Colors.white,
              height: 1.9, // Increased for breathable long-form reading
              letterSpacing: 0.35,
              fontSize: 16,
            ),
          ),
        ),
        
        const SizedBox(height: PharmSpacing.xxl),
      ],
    );
  }
}
