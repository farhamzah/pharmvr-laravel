import 'package:flutter/material.dart';
import '../../domain/models/learning_module.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/config/network_constants.dart';
import '../../../../core/widgets/pharm_network_image.dart';
import 'package:cached_network_image/cached_network_image.dart';

class EducationContentHero extends StatelessWidget {
  final LearningModule module;

  const EducationContentHero({
    super.key,
    required this.module,
  });

  @override
  Widget build(BuildContext context) {
    final isVideo = module.type == 'video';
    final isModule = module.type == 'module';

    return Container(
      width: double.infinity,
      color: PharmColors.background,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Preview Area
          if (isModule)
            _buildModulePreviewArea()
          else
            _buildPreviewArea(isVideo),
          
          // 2. Title & Metadata
          Padding(
            padding: PharmSpacing.allLg,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Badge
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: PharmSpacing.sm,
                    vertical: 6.0,
                  ),
                  decoration: BoxDecoration(
                    color: isModule ? PharmColors.success.withOpacity(0.15) 
                        : (isVideo
                            ? PharmColors.primary.withOpacity(0.15)
                            : PharmColors.info.withOpacity(0.15)),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: isModule ? PharmColors.success.withOpacity(0.3)
                          : (isVideo
                              ? PharmColors.primary.withOpacity(0.3)
                              : PharmColors.info.withOpacity(0.3)),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isModule ? Icons.vrpano 
                            : (isVideo ? Icons.play_circle_fill : Icons.description_rounded),
                        size: 14,
                        color: isModule ? PharmColors.success 
                            : (isVideo ? PharmColors.primaryLight : PharmColors.info),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isModule ? 'VR TRAINING MODULE' 
                            : (isVideo ? 'VIDEO MODULE' : 'DOCUMENT MODULE'),
                        style: PharmTextStyles.overline.copyWith(
                          color: isModule ? PharmColors.success 
                              : (isVideo ? PharmColors.primaryLight : PharmColors.info),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: PharmSpacing.md),
                
                // Title
                Text(
                  module.title,
                  style: PharmTextStyles.h2.copyWith(
                    color: PharmColors.textPrimary,
                  ),
                ),
                const SizedBox(height: PharmSpacing.sm),
                
                // Metadata Row
                Row(
                  children: [
                    Icon(
                      Icons.category_rounded,
                      size: 16,
                      color: PharmColors.textTertiary,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      module.category,
                      style: PharmTextStyles.bodyMedium.copyWith(
                        color: PharmColors.textSecondary,
                      ),
                    ),
                    const SizedBox(width: PharmSpacing.md),
                    if (isVideo && module.durationMinutes != null) ...[
                      Icon(
                        Icons.timer_outlined,
                        size: 16,
                        color: PharmColors.textTertiary,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${module.durationMinutes} mins',
                        style: PharmTextStyles.bodyMedium.copyWith(
                          color: PharmColors.textSecondary,
                        ),
                      ),
                    ],
                    if (!isVideo && !isModule && module.pagesCount != null) ...[
                      Icon(
                        Icons.insert_drive_file_outlined,
                        size: 16,
                        color: PharmColors.textTertiary,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${module.pagesCount} Pages',
                        style: PharmTextStyles.bodyMedium.copyWith(
                          color: PharmColors.textSecondary,
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPreviewArea(bool isVideo) {
    if (isVideo) {
      return Container(
        width: double.infinity,
        height: 240, 
        decoration: BoxDecoration(
          color: PharmColors.surface,
          image: module.effectiveThumbnailUrl != null
              ? DecorationImage(
                  image: CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(module.effectiveThumbnailUrl!)),
                  fit: BoxFit.cover,
                )
              : null,
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            if (module.effectiveThumbnailUrl != null)
              Container(
                color: Colors.black.withOpacity(0.4),
              ),
            Container(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: PharmColors.primary.withOpacity(0.2),
                border: Border.all(color: PharmColors.primary, width: 2),
              ),
              padding: const EdgeInsets.all(12),
              child: const Icon(
                Icons.play_arrow_rounded,
                color: PharmColors.primary,
                size: 40,
              ),
            ),
          ],
        ),
      );
    } else {
      // ── DOCUMENT PREMIUM HERO ──
      final hasThumbnail = module.effectiveThumbnailUrl != null;
      
      return Container(
        width: double.infinity,
        height: 280, // Slightly taller for portrait focus
        decoration: BoxDecoration(
          color: PharmColors.background,
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            // Background Blur / Ambient Glow
            if (hasThumbnail)
              Positioned.fill(
                child: Opacity(
                  opacity: 0.15,
                  child: PharmNetworkImage(
                    url: NetworkConstants.sanitizeUrl(module.effectiveThumbnailUrl!),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
            
            // Bottom Gradient for grounding
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      PharmColors.background.withOpacity(0),
                      PharmColors.background,
                    ],
                  ),
                ),
              ),
            ),

            // The Main Document "Book"
            Container(
              width: 160,
              height: 220,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.5),
                    blurRadius: 30,
                    spreadRadius: 2,
                    offset: const Offset(0, 15),
                  ),
                  BoxShadow(
                    color: PharmColors.info.withOpacity(0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 0),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (hasThumbnail)
                      PharmNetworkImage(
                        url: NetworkConstants.sanitizeUrl(module.effectiveThumbnailUrl!),
                        fit: BoxFit.cover,
                      )
                    else
                      Container(
                        color: PharmColors.surfaceLight,
                        child: Center(
                          child: Icon(
                            Icons.description_rounded,
                            size: 64,
                            color: PharmColors.info.withOpacity(0.3),
                          ),
                        ),
                      ),
                    
                    // Subtle lighting overlay
                    Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [
                            Colors.white.withOpacity(0.1),
                            Colors.transparent,
                            Colors.black.withOpacity(0.2),
                          ],
                        ),
                      ),
                    ),
                    
                    // Left "Spine" Shadow
                    Positioned(
                      left: 0,
                      top: 0,
                      bottom: 0,
                      width: 6,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.centerLeft,
                            end: Alignment.centerRight,
                            colors: [
                              Colors.black.withOpacity(0.2),
                              Colors.transparent,
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
    }
  }

  Widget _buildModulePreviewArea() {
    return Container(
      width: double.infinity,
      height: 220,
      decoration: BoxDecoration(
        color: PharmColors.surface,
        image: module.effectiveThumbnailUrl != null
            ? DecorationImage(
                image: CachedNetworkImageProvider(NetworkConstants.sanitizeUrl(module.effectiveThumbnailUrl!)),
                fit: BoxFit.cover,
              )
            : null,
      ),
      child: Stack(
        children: [
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                stops: const [0.0, 0.5, 1.0],
                colors: [
                  Colors.black.withOpacity(0.3),
                  Colors.black.withOpacity(0.5),
                  PharmColors.background,
                ],
              ),
            ),
          ),
          Center(
            child: Container(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: PharmColors.primary.withOpacity(0.15),
                border: Border.all(color: PharmColors.primary.withOpacity(0.4), width: 2),
                boxShadow: [
                  BoxShadow(
                    color: PharmColors.primary.withOpacity(0.3),
                    blurRadius: 24,
                    spreadRadius: 4,
                  ),
                ],
              ),
              padding: const EdgeInsets.all(16),
              child: const Icon(
                Icons.vrpano,
                color: PharmColors.primary,
                size: 40,
              ),
            ),
          ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 12,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: PharmColors.surface.withOpacity(0.85),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: PharmColors.primary.withOpacity(0.15)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _miniStep(Icons.quiz_outlined, 'Pre-Test'),
                  _miniConnector(),
                  _miniStep(Icons.vrpano, 'VR Sim'),
                  _miniConnector(),
                  _miniStep(Icons.fact_check_outlined, 'Post-Test'),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _miniStep(IconData icon, String label) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: PharmColors.primary.withOpacity(0.12),
            border: Border.all(color: PharmColors.primary.withOpacity(0.3)),
          ),
          child: Icon(icon, size: 14, color: PharmColors.primary),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: PharmTextStyles.caption.copyWith(
            color: PharmColors.textTertiary,
            fontSize: 9,
          ),
        ),
      ],
    );
  }

  Widget _miniConnector() {
    return Container(
      width: 20,
      height: 2,
      margin: const EdgeInsets.only(bottom: 14),
      color: PharmColors.primary.withOpacity(0.2),
    );
  }
}
