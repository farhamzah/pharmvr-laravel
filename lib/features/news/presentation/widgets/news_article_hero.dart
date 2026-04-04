import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../domain/models/news_article.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/config/network_constants.dart';

class NewsArticleHero extends StatelessWidget {
  final NewsArticle article;

  const NewsArticleHero({
    super.key,
    required this.article,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // 1. Background Image with immersive height
        Container(
          width: double.infinity,
          height: 420,
          decoration: BoxDecoration(
            color: PharmColors.surface,
            image: article.bannerUrl != null
                ? DecorationImage(
                    image: CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(article.bannerUrl!)),
                    fit: BoxFit.cover,
                  )
                : null,
          ),
        ),
        
        // 2. High-performance Gradient Layer
        Container(
          width: double.infinity,
          height: 420,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [
                PharmColors.background.withOpacity(0.0),
                PharmColors.background.withOpacity(0.2),
                PharmColors.background.withOpacity(0.7),
                PharmColors.background.withOpacity(0.95),
                PharmColors.background,
              ],
              stops: const [0.0, 0.3, 0.6, 0.85, 1.0],
            ),
          ),
        ),

        // 3. Hero Content positioned at the bottom
        Positioned(
          left: PharmSpacing.lg,
          right: PharmSpacing.lg,
          bottom: PharmSpacing.lg,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // High-character Badge
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: PharmColors.primary.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(4),
                  border: Border.all(
                    color: PharmColors.primary.withOpacity(0.25),
                  ),
                ),
                child: Text(
                  article.category.toUpperCase(),
                  style: PharmTextStyles.overline.copyWith(
                    color: PharmColors.primary,
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              const SizedBox(height: PharmSpacing.md),
              
              // Article Title - Shifted to Inter Bold for reading comfort
              Text(
                article.title,
                style: PharmTextStyles.h2.copyWith(
                  fontFamily: 'Inter', // Direct override for readability
                  color: Colors.white,
                  height: 1.25,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.4,
                ),
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: PharmSpacing.lg),
              
              // Refined Metadata Row
              Row(
                children: [
                  _buildMetaItem(Icons.calendar_today_rounded, _formatDate(article.publishedAt)),
                  const SizedBox(width: PharmSpacing.md),
                  _buildMetaItem(Icons.person_outline_rounded, article.author),
                  const SizedBox(width: PharmSpacing.md),
                  _buildMetaItem(Icons.timer_outlined, '${article.readingTime}m read'),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildMetaItem(IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 12, color: PharmColors.textSecondary.withOpacity(0.5)),
        const SizedBox(width: 4),
        Text(
          label,
          style: PharmTextStyles.caption.copyWith(
            color: PharmColors.textSecondary.withOpacity(0.7),
            fontSize: 11,
          ),
        ),
      ],
    );
  }

  String _formatDate(DateTime date) {
    final months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    return '${date.day} ${months[date.month - 1]}, ${date.year}';
  }
}
