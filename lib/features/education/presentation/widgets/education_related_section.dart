import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../domain/models/learning_module.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/config/network_constants.dart';

class EducationRelatedSection extends StatelessWidget {
  final List<LearningModule> relatedItems;
  final bool isLoading;

  const EducationRelatedSection({
    super.key,
    this.relatedItems = const [],
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const SizedBox.shrink();
    }

    if (relatedItems.isEmpty) return const SizedBox.shrink();

    return Container(
      color: PharmColors.surface, // Slight grounding background
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: PharmColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.next_plan_outlined, color: PharmColors.primary, size: 18),
              ),
              const SizedBox(width: 12),
              Text(
                'Lanjutkan Pembelajaran', 
                style: PharmTextStyles.h3.copyWith(
                  color: PharmColors.textPrimary,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: relatedItems.length,
            separatorBuilder: (context, index) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final content = relatedItems[index];
              return _buildRelatedCard(content);
            },
          ),
          const SizedBox(height: 40), 
        ],
      ),
    );
  }

  Widget _buildRelatedCard(LearningModule item) {
    final isVideo = item.type == 'video';

    return Container(
      decoration: BoxDecoration(
        color: PharmColors.surface.withOpacity(0.4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: PharmColors.cardBorder.withOpacity(0.5)),
      ),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          // Thumbnail / Icon Area
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: PharmColors.background,
              borderRadius: BorderRadius.circular(10),
              image: item.effectiveThumbnailUrl != null
                  ? DecorationImage(
                      image: CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(item.effectiveThumbnailUrl!)),
                      fit: BoxFit.cover,
                    )
                  : null,
            ),
            child: (item.effectiveThumbnailUrl == null)
                ? Icon(
                    isVideo ? Icons.play_circle_fill : Icons.description_rounded,
                    color: isVideo ? PharmColors.primary : PharmColors.info,
                    size: 24,
                  )
                : null,
          ),
          const SizedBox(width: 16),
          
          // Meta details
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  item.title,
                  style: PharmTextStyles.bodyMedium.copyWith(
                    color: PharmColors.textPrimary,
                    fontWeight: FontWeight.bold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      isVideo ? Icons.timer_outlined : Icons.menu_book_rounded,
                      size: 10,
                      color: PharmColors.textSecondary.withOpacity(0.6),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      isVideo 
                        ? '${item.durationMinutes ?? 0}m • Video' 
                        : '${item.pagesCount ?? 0} Hal • Dokumen',
                      style: PharmTextStyles.caption.copyWith(
                        color: PharmColors.textSecondary.withOpacity(0.8),
                        fontSize: 10,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          
          Icon(
            Icons.chevron_right_rounded,
            color: PharmColors.textTertiary.withOpacity(0.5),
            size: 20,
          ),
        ],
      ),
    );
  }
}
