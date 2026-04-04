import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/widgets/pharm_glass_card.dart';
import '../../domain/models/news_article.dart';
import '../../../../core/config/network_constants.dart';
import '../../../../core/widgets/pharm_network_image.dart';

class PharmNewsCard extends StatelessWidget {
  final NewsArticle article;
  final VoidCallback onTap;

  const PharmNewsCard({
    super.key,
    required this.article,
    required this.onTap,
  });

  String _timeAgo(DateTime date) {
    final diff = DateTime.now().difference(date);
    if (diff.inDays > 0) return '${diff.inDays}d ago';
    if (diff.inHours > 0) return '${diff.inHours}h ago';
    if (diff.inMinutes > 0) return '${diff.inMinutes}m ago';
    return 'Just now';
  }

  @override
  Widget build(BuildContext context) {
    return PharmGlassCard(
      padding: EdgeInsets.zero, // Enable full-bleed header
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Full-Bleed Thumbnail Image
            AspectRatio(
              aspectRatio: 16 / 9,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                    child: article.bannerUrl != null 
                      ? PharmNetworkImage(
                          url: NetworkConstants.sanitizeUrl(article.bannerUrl!),
                          fit: BoxFit.cover,
                          errorWidget: _buildImagePlaceholder(),
                        )
                      : _buildImagePlaceholder(),
                  ),
                  if (article.isExternal)
                    Positioned(
                      top: 12,
                      left: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFA855F7), // Magical Purple
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(color: const Color(0xFFA855F7).withOpacity(0.4), blurRadius: 10, spreadRadius: 0)
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.auto_awesome, size: 12, color: Colors.white),
                            const SizedBox(width: 4),
                            Text(
                              'CURATED',
                              style: PharmTextStyles.label.copyWith(
                                color: Colors.white, 
                                fontSize: 9, 
                                fontWeight: FontWeight.w900,
                                letterSpacing: 1.0,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
            
            // Content Area
            Padding(
              padding: const EdgeInsets.fromLTRB(
                PharmSpacing.md, 
                PharmSpacing.md, 
                PharmSpacing.md, 
                PharmSpacing.lg,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Meta Row (Source & Time)
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: PharmColors.primary.withOpacity(0.1),
                          border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          article.category.toUpperCase(),
                          style: PharmTextStyles.label.copyWith(
                            color: PharmColors.primary, 
                            fontSize: 9, 
                            fontWeight: FontWeight.w800,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      if (article.isExternal) ...[
                        Text(
                          'via ${article.sourceName ?? "External"}',
                          style: PharmTextStyles.caption.copyWith(
                            color: const Color(0xFFA855F7),
                            fontWeight: FontWeight.bold,
                            fontSize: 10,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text('•', style: PharmTextStyles.caption.copyWith(color: PharmColors.textSecondary.withOpacity(0.5))),
                        const SizedBox(width: 8),
                      ],
                      Icon(Icons.access_time_rounded, size: 12, color: PharmColors.textSecondary.withOpacity(0.5)),
                      const SizedBox(width: 4),
                      Text(
                        _timeAgo(article.publishedAt),
                        style: PharmTextStyles.caption.copyWith(
                          color: PharmColors.textSecondary.withOpacity(0.7),
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: PharmSpacing.md),
                  
                  // Title
                  Text(
                    article.title, 
                    style: PharmTextStyles.h4.copyWith(
                      height: 1.3,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  
                  const SizedBox(height: PharmSpacing.sm),
                  
                  // Content Summary
                  Text(
                    article.isExternal 
                        ? (article.aiSummary ?? article.excerpt) 
                        : article.excerpt, 
                    style: PharmTextStyles.bodySmall.copyWith(
                      color: PharmColors.textSecondary,
                      height: 1.5,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImagePlaceholder({bool isLoading = false}) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            PharmColors.surface,
            PharmColors.surface.withOpacity(0.5),
          ],
        ),
      ),
      child: Center(
        child: isLoading 
          ? const SizedBox(
              width: 24, 
              height: 24, 
              child: CircularProgressIndicator(strokeWidth: 2, color: PharmColors.primary),
            )
          : Icon(
              Icons.newspaper_rounded, 
              size: 40, 
              color: PharmColors.primary.withOpacity(0.2),
            ),
      ),
    );
  }
}
