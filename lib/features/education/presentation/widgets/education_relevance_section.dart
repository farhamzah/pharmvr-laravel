import 'package:flutter/material.dart';
import '../../domain/models/learning_module.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';

class EducationRelevanceSection extends StatelessWidget {
  final LearningModule module;

  const EducationRelevanceSection({
    super.key,
    required this.module,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: PharmSpacing.horizontalLg,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: PharmColors.surface.withOpacity(0.5),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: PharmColors.cardBorder,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: PharmColors.warning.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.map_outlined,
                  color: PharmColors.warning,
                  size: 18,
                ),
              ),
              const SizedBox(width: 12),
              Text(
                'Alur Pembelajaran', 
                style: PharmTextStyles.subtitle.copyWith(
                  color: PharmColors.warning,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          
          // ── ROADMAP STEPS ──
          _buildRoadmapStep(
            icon: Icons.psychology_outlined,
            title: 'Prasyarat / Rekomendasi',
            content: module.prerequisites ?? 'Terbuka untuk semua jenjang. Pahami teori ini sebelum mencoba materi terkait.',
            color: PharmColors.info,
            isLast: false,
            isActive: true,
          ),
          
          _buildRoadmapStep(
            icon: Icons.school_outlined,
            title: 'Topik Materi',
            content: '${module.category} • ${module.level}',
            color: PharmColors.primary,
            isLast: true,
            isActive: true,
            showGlow: true,
          ),
        ],
      ),
    );
  }

  Widget _buildRoadmapStep({
    required IconData icon,
    required String title,
    required String content,
    required Color color,
    required bool isLast,
    bool isActive = false,
    bool showGlow = false,
  }) {
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Left Column (Roadmap markers)
          Column(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: isActive ? color.withOpacity(0.15) : PharmColors.surfaceLight,
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: isActive ? color : PharmColors.divider,
                    width: 2,
                  ),
                  boxShadow: showGlow ? [
                    BoxShadow(
                      color: color.withOpacity(0.3),
                      blurRadius: 8,
                      spreadRadius: 1,
                    )
                  ] : null,
                ),
                child: Icon(
                  icon,
                  size: 16,
                  color: isActive ? color : PharmColors.textTertiary,
                ),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    margin: const EdgeInsets.symmetric(vertical: 4),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          color,
                          PharmColors.primary.withOpacity(0.5),
                        ],
                      ),
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 16),
          // Content
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 28),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: PharmTextStyles.caption.copyWith(
                      color: PharmColors.textSecondary,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    content,
                    style: PharmTextStyles.bodyMedium.copyWith(
                      color: PharmColors.textPrimary,
                      height: 1.4,
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
