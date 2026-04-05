import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/widgets/pharm_glass_card.dart';
import '../../../../core/config/network_constants.dart';
import '../../../../core/widgets/pharm_network_image.dart';
import '../../domain/models/learning_module.dart';

class PharmEdukasiCard extends StatelessWidget {
  final LearningModule module;
  final VoidCallback onTap;
  final bool isGrid;

  const PharmEdukasiCard({
    super.key,
    required this.module,
    required this.onTap,
    this.isGrid = false,
  });

  @override
  Widget build(BuildContext context) {
    if (module.type == 'module') {
      return _buildModuleCard(context);
    }
    if (isGrid && module.type == 'video') {
      return _buildVideoGridCard(context);
    }
    return _buildDefaultCard(context);
  }

  // ═══════════════════════════════════════════════════════════
  // VIDEO GRID CARD — Cinematic gallery style
  // ═══════════════════════════════════════════════════════════
  Widget _buildVideoGridCard(BuildContext context) {
    return PharmGlassCard(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Thumbnail with Play Overlay
            Expanded(
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (module.effectiveThumbnailUrl != null)
                      PharmNetworkImage(
                        url: module.effectiveThumbnailUrl!,
                        fit: BoxFit.cover,
                      )
                    else
                      Container(color: Theme.of(context).colorScheme.surface),
                    
                    // Glassy Play Button Overlay
                    Center(
                      child: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.black.withOpacity(0.3),
                        ),
                        child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 32),
                      ),
                    ),

                    // Duration Tag
                    Positioned(
                      bottom: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.black87,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          '${module.durationMinutes ?? 0}m',
                          style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            // Text Info
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                   Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: PharmColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          module.category.toUpperCase(),
                          style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, fontSize: 8),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    module.title,
                    style: PharmTextStyles.bodyMedium.copyWith(
                      fontWeight: FontWeight.bold,
                      height: 1.1,
                      color: Theme.of(context).textTheme.displaySmall?.color,
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

  // ═══════════════════════════════════════════════════════════
  // MODULE CARD — Premium VR-themed card with journey steps
  // ═══════════════════════════════════════════════════════════
  Widget _buildModuleCard(BuildContext context) {
    return PharmGlassCard(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Thumbnail with VR overlay
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
              child: SizedBox(
                height: 140,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (module.effectiveThumbnailUrl != null)
                      PharmNetworkImage(
                        url: module.effectiveThumbnailUrl!,
                        fit: BoxFit.cover,
                      )
                    else
                      Container(color: Theme.of(context).colorScheme.surface),
                    // Gradient overlay
                    Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Colors.transparent,
                            Theme.of(context).scaffoldBackgroundColor.withOpacity(0.9),
                          ],
                        ),
                      ),
                    ),
                    // VR Badge
                    Positioned(
                      top: 12,
                      left: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: PharmColors.primary.withOpacity(0.85),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: PharmColors.primary.withOpacity(0.4),
                              blurRadius: 8,
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.vrpano, color: Colors.white, size: 14),
                            const SizedBox(width: 4),
                            Text(
                              'VR MODULE',
                              style: PharmTextStyles.overline.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    // Duration badge
                    Positioned(
                      top: 12,
                      right: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.5),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.timer_outlined, color: Colors.white70, size: 12),
                            const SizedBox(width: 4),
                            Text(
                              '${module.durationMinutes ?? 0} min',
                              style: PharmTextStyles.caption.copyWith(color: Colors.white70),
                            ),
                          ],
                        ),
                      ),
                    ),
                    // Module ID at bottom
                    Positioned(
                      bottom: 10,
                      left: 12,
                      child: Text(
                        module.code,
                        style: PharmTextStyles.caption.copyWith(
                          color: PharmColors.primary,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Content section
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    module.title,
                    style: PharmTextStyles.h4.copyWith(color: Theme.of(context).textTheme.displaySmall?.color),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    module.description,
                    style: PharmTextStyles.bodySmall.copyWith(color: Theme.of(context).textTheme.bodySmall?.color),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 14),

                  // Journey Steps Mini Preview
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.surface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: PharmColors.primary.withOpacity(0.1)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _journeyStep(context, Icons.quiz_outlined, 'Pre-Test', module.journey?.preTest.status ?? 'available'),
                        _journeyConnector(),
                        _journeyStep(context, Icons.vrpano, 'VR Sim', module.journey?.vrSim.status ?? 'locked'),
                        _journeyConnector(),
                        _journeyStep(context, Icons.fact_check_outlined, 'Post-Test', module.journey?.postTest.status ?? 'locked'),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),
                  // Course module label
                  Row(
                    children: [
                      Icon(Icons.school_outlined, size: 14, color: Theme.of(context).textTheme.labelSmall?.color),
                      const SizedBox(width: 6),
                      Text(
                        module.category,
                        style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.labelSmall?.color),
                      ),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: PharmColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          'Mulai Belajar →',
                          style: PharmTextStyles.caption.copyWith(
                            color: PharmColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _journeyStep(BuildContext context, IconData icon, String label, String status) {
    Color stepColor;
    IconData stepIcon = icon;
    
    switch (status) {
      case 'passed':
      case 'completed':
        stepColor = PharmColors.success;
        stepIcon = Icons.check_circle_rounded;
        break;
      case 'failed':
        stepColor = PharmColors.error;
        stepIcon = Icons.error_outline_rounded;
        break;
      case 'available':
        stepColor = PharmColors.primary;
        break;
      case 'locked':
      default:
        stepColor = Theme.of(context).disabledColor;
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: stepColor.withOpacity(0.1),
            border: Border.all(color: stepColor.withOpacity(0.3)),
            boxShadow: status != 'locked' ? [
              BoxShadow(
                color: stepColor.withOpacity(0.2),
                blurRadius: 4,
                spreadRadius: 1,
              )
            ] : null,
          ),
          child: Icon(stepIcon, size: 16, color: stepColor),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: PharmTextStyles.caption.copyWith(
            color: status == 'locked' ? Theme.of(context).disabledColor : Theme.of(context).textTheme.labelSmall?.color,
            fontSize: 9,
            fontWeight: status != 'locked' ? FontWeight.bold : FontWeight.normal,
          ),
        ),
      ],
    );
  }

  Widget _journeyConnector() {
    return Container(
      width: 20,
      height: 2,
      margin: const EdgeInsets.only(bottom: 16),
      color: PharmColors.primary.withOpacity(0.2),
    );
  }

  // ═══════════════════════════════════════════════════════════
  // DEFAULT CARD — Video & Document
  // ═══════════════════════════════════════════════════════════
  Widget _buildDefaultCard(BuildContext context) {
    final isVideo = module.type == 'video';

    return PharmGlassCard(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16), // Increased padding for premium feel
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Thumbnail Container with Depth (Portrait for Document)
              Container(
                width: 76,
                height: 104,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.35),
                      blurRadius: 10,
                      offset: const Offset(4, 4),
                    ),
                    BoxShadow(
                      color: PharmColors.primary.withOpacity(0.1),
                      blurRadius: 4,
                      offset: const Offset(-1, -1),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      // Base Image
                      if (module.effectiveThumbnailUrl != null)
                        PharmNetworkImage(
                          url: module.effectiveThumbnailUrl!,
                          fit: BoxFit.cover,
                          errorWidget: _buildPlaceholderIcon(isVideo),
                        )
                      else
                        _buildPlaceholderIcon(isVideo),
                      
                      // Paper/Book Texture Overlay (Subtle)
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.centerLeft,
                            end: Alignment.centerRight,
                            colors: [
                              Colors.black.withOpacity(0.15),
                              Colors.transparent,
                              Colors.white.withOpacity(0.05),
                            ],
                            stops: const [0.0, 0.05, 1.0],
                          ),
                        ),
                      ),
                      
                      // Inner Glow Border
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(
                            color: Colors.white.withOpacity(0.1),
                            width: 0.5,
                          ),
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(width: 18),

              // Content Details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Badge & Page Count
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: [
                                (isVideo ? PharmColors.primary : PharmColors.info).withOpacity(0.15),
                                (isVideo ? PharmColors.primary : PharmColors.info).withOpacity(0.05),
                              ],
                            ),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: (isVideo ? PharmColors.primary : PharmColors.info).withOpacity(0.2),
                            ),
                          ),
                          child: Text(
                            module.category.toUpperCase(),
                            style: PharmTextStyles.overline.copyWith(
                              color: isVideo ? PharmColors.primaryLight : PharmColors.info,
                              fontSize: 8,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.8,
                            ),
                          ),
                        ),
                        if (!isVideo && module.pagesCount != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            child: Row(
                              children: [
                                Icon(
                                  Icons.menu_book_rounded, 
                                  size: 11, 
                                  color: PharmColors.textSecondary.withOpacity(0.5)
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '${module.pagesCount} Hal',
                                  style: PharmTextStyles.caption.copyWith(
                                    fontSize: 9, 
                                    color: PharmColors.textSecondary.withOpacity(0.7),
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          )
                        else if (isVideo)
                          Row(
                            children: [
                              Icon(
                                Icons.timer_outlined, 
                                size: 11, 
                                color: PharmColors.textSecondary.withOpacity(0.5)
                              ),
                              const SizedBox(width: 4),
                              Text(
                                '${module.durationMinutes ?? 0}m',
                                style: PharmTextStyles.caption.copyWith(
                                  fontSize: 9, 
                                  color: PharmColors.textSecondary.withOpacity(0.7),
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Title
                    Text(
                      module.title,
                      style: PharmTextStyles.bodyMedium.copyWith(
                        fontWeight: FontWeight.w800,
                        color: PharmColors.textPrimary,
                        height: 1.25,
                        letterSpacing: 0.1,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),

                    // Description / Summary
                    Text(
                      (module.shortSummary != null && module.shortSummary!.isNotEmpty)
                          ? module.shortSummary!
                          : module.description,
                      style: PharmTextStyles.caption.copyWith(
                        color: PharmColors.textSecondary.withOpacity(0.7),
                        height: 1.5,
                        fontSize: 10,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    const SizedBox(height: 14),

                    // Footer Action Hint
                    Row(
                      children: [
                        Container(
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: (isVideo ? PharmColors.primary : PharmColors.info).withOpacity(0.1),
                          ),
                          child: Icon(
                            isVideo ? Icons.play_arrow_rounded : Icons.arrow_forward_rounded,
                            size: 14,
                            color: isVideo ? PharmColors.primary : PharmColors.info,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          isVideo ? 'MULAI VIDEO' : 'PELAJARI MATERI',
                          style: PharmTextStyles.overline.copyWith(
                            color: isVideo ? PharmColors.primary : PharmColors.info,
                            fontSize: 8,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPlaceholderIcon(bool isVideo) {
    return Center(
      child: Icon(
        isVideo ? Icons.play_circle_outline_rounded : Icons.description_rounded,
        size: 32,
        color: PharmColors.primary.withOpacity(0.3),
      ),
    );
  }
}
