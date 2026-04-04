import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';

class AiChatHeader extends StatelessWidget implements PreferredSizeWidget {
  final VoidCallback onBackPressed;
  final String? activeContext; // "SOP Area Bersih", "Assessment 3", dll

  const AiChatHeader({
    super.key,
    required this.onBackPressed,
    this.activeContext,
  });

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: PharmColors.surfaceLight,
      elevation: 2,
      shadowColor: PharmColors.background.withOpacity(0.5),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new, color: PharmColors.textPrimary),
        onPressed: onBackPressed,
      ),
      title: Row(
        children: [
          // AI Avatar
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: PharmColors.primary.withOpacity(0.15),
              border: Border.all(
                color: PharmColors.primary.withOpacity(0.5),
                width: 1.5,
              ),
            ),
            child: const Center(
              child: Icon(
                Icons.smart_toy_rounded,
                color: PharmColors.primary,
                size: 22,
              ),
            ),
          ),
          const SizedBox(width: 12),
          
          // AI Info & Context Badge
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'PharmAI Assistant',
                  style: PharmTextStyles.subtitle.copyWith(
                    color: PharmColors.textPrimary,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (activeContext != null) ...[
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          color: PharmColors.success,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Konteks: $activeContext',
                          style: PharmTextStyles.caption.copyWith(
                            color: PharmColors.textSecondary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ] else ...[
                  const SizedBox(height: 2),
                  Text(
                    'Online - Siap membantu',
                    style: PharmTextStyles.caption.copyWith(
                      color: PharmColors.textSecondary,
                    ),
                  ),
                ]
              ],
            ),
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.more_vert_rounded, color: PharmColors.textPrimary),
          onPressed: () {
            // Future feature: clear chat, change AI strictness, dll.
          },
        ),
      ],
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}
