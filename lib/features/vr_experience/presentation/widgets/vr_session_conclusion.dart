import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/vr_training_session.dart';
import '../components/vr_panels.dart';

class VrSessionConclusion extends StatelessWidget {
  final VrTrainingSession session;

  const VrSessionConclusion({
    super.key,
    required this.session,
  });

  @override
  Widget build(BuildContext context) {
    if (session.phase != VrSessionPhase.completed && session.phase != VrSessionPhase.interrupted) {
      return const SizedBox.shrink();
    }

    final isSuccess = session.phase == VrSessionPhase.completed;

    return Padding(
      padding: PharmSpacing.horizontalLg,
      child: Column(
        children: [
          // Outcome Card via Generic Component
          VrOutcomePanel(
            isSuccess: isSuccess,
            title: isSuccess ? 'Lulus Simulasi CPOB' : 'Simulasi Terhenti',
            description: isSuccess 
              ? 'Anda telah berhasil menyelesaikan misi pelatihan ini. Semua data telemetri VR telah disinkronkan ke Dasbor Evaluasi Anda.'
              : session.interruptReason ?? 'Sesi VR terputus sebelum operasi laboratorium selesai disimulasikan.',
            trailingBadge: isSuccess && session.finalScore != null 
              ? Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: PharmColors.warning, // Goldish
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '${session.finalScore}/100',
                    style: PharmTextStyles.label.copyWith(
                      color: PharmColors.background,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                )
              : null,
          ),
          
          const SizedBox(height: PharmSpacing.xl),
          
          // Next Actions
          if (isSuccess) ...[
            _buildActionCard(
              title: 'Lanjut ke Post-Test', // "Proceed to Post-Test"
              subtitle: 'Selesaikan evaluasi tertulis untuk mendapatkan sertifikat.', // "Finish written eval to get cert."
              icon: Icons.quiz_rounded,
              color: PharmColors.primary,
              onTap: () {},
            ),
            const SizedBox(height: PharmSpacing.md),
            _buildActionCard(
              title: 'Analisis Kesalahan (AI)', // "AI Mistake Analysis"
              subtitle: 'PharmAI telah meninjau log pergerakan tangan Anda saat di lab.', // "PharmAI reviewed your hand tracking logs."
              icon: Icons.auto_awesome,
              color: PharmColors.info,
              onTap: () {},
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: PharmSpacing.allMd,
        decoration: BoxDecoration(
          color: PharmColors.surfaceLight,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: PharmColors.cardBorder),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: PharmSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: PharmTextStyles.subtitle.copyWith(
                      color: PharmColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: PharmTextStyles.caption.copyWith(
                      color: PharmColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            Icon(Icons.chevron_right_rounded, color: PharmColors.textTertiary),
          ],
        ),
      ),
    );
  }
}
