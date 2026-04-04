import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../domain/models/learning_module.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../../../core/theme/pharm_spacing.dart';

class EducationCTASection extends StatelessWidget {
  final LearningModule module;

  const EducationCTASection({
    super.key,
    required this.module,
  });

  @override
  Widget build(BuildContext context) {
    final isVideo = module.type == 'video';
    final isModule = module.type == 'module';

    return Padding(
      padding: PharmSpacing.allLg,
      child: Column(
        children: [
          if (isModule) ...[
            // ── MODULE CTAs ──
            // Primary: Start Pre-Test
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: () {
                  context.push('/assessment/intro/${module.slug}/pre');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: PharmColors.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 4,
                  shadowColor: PharmColors.accentGlow,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.quiz_outlined, size: 22),
                    const SizedBox(width: 8),
                    Text('Mulai Pre-Test', style: PharmTextStyles.button),
                  ],
                ),
              ),
            ),
            const SizedBox(height: PharmSpacing.sm),
            // Secondary: Connect VR Headset
            SizedBox(
              width: double.infinity,
              height: 50,
              child: OutlinedButton(
                onPressed: () {
                  context.push('/vr/connect');
                },
                style: OutlinedButton.styleFrom(
                  side: BorderSide(color: PharmColors.success.withOpacity(0.5)),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.headset, size: 18, color: PharmColors.success),
                    const SizedBox(width: 8),
                    Text(
                      'Hubungkan VR Headset',
                      style: PharmTextStyles.button.copyWith(color: PharmColors.success),
                    ),
                  ],
                ),
              ),
            ),
          ] else ...[
            // ── VIDEO / DOCUMENT CTAs ──
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: () async {
                  if (isVideo && module.videoId != null) {
                    context.push(
                      '/education/detail/${module.id}/player/${module.videoId}?title=${Uri.encodeComponent(module.title)}',
                    );
                  } else if (!isVideo && module.fileUrl != null) {
                    if (module.sourceType == 'external') {
                      final url = Uri.parse(module.fileUrl!);
                      if (await canLaunchUrl(url)) {
                        await launchUrl(url, mode: LaunchMode.externalApplication);
                      }
                    } else {
                      context.push(
                        '/education/detail/${module.id}/viewer?url=${Uri.encodeComponent(module.fileUrl!)}&title=${Uri.encodeComponent(module.title)}',
                      );
                    }
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: isVideo ? PharmColors.primaryDark : PharmColors.info,
                  foregroundColor: PharmColors.textPrimary,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 4,
                  shadowColor: isVideo ? PharmColors.accentGlow : Colors.transparent,
                ),
                child: Ink(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        (isVideo ? PharmColors.primaryLight : PharmColors.info).withOpacity(0.9),
                        isVideo ? PharmColors.primaryDark : PharmColors.info.withOpacity(0.8),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Center(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          isVideo ? Icons.play_arrow_rounded : Icons.article_rounded,
                          size: 20,
                        ),
                        const SizedBox(width: 10),
                        Text(
                          isVideo ? 'MULAI VIDEO SEKARANG' : 'BUKA DOKUMEN MATERI',
                          style: PharmTextStyles.button.copyWith(
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.2,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],

          const SizedBox(height: 16),

          // AI Assistant Integration Hook (shared)
          SizedBox(
            width: double.infinity,
            height: 54,
            child: OutlinedButton(
              onPressed: () {
                final prompt = Uri.encodeComponent('Halo AI, saya baru saja membaca materi "${module.title}". Bisakah kamu jelaskan lebih detail tentang topik ini?');
                context.push('/ai-assistant/chat/new?prompt=$prompt');
              },
              style: OutlinedButton.styleFrom(
                side: BorderSide(color: PharmColors.primary.withOpacity(0.35), width: 1.5),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                backgroundColor: PharmColors.primary.withOpacity(0.05),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.auto_awesome, size: 18, color: PharmColors.primary),
                  const SizedBox(width: 10),
                  Text(
                    'TANYA AI TENTANG TOPIK INI',
                    style: PharmTextStyles.button.copyWith(
                      color: PharmColors.primary,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
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
