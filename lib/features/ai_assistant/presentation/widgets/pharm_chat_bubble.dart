import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/ai_message.dart';

class PharmChatBubble extends StatelessWidget {
  final AiMessage message;

  const PharmChatBubble({super.key, required this.message});

  @override
  Widget build(BuildContext context) {
    final isUser = message.sender == AiSender.user;

    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Column(
        crossAxisAlignment: isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
        children: [
          // ── Sender label ──
          if (!isUser)
            Padding(
              padding: const EdgeInsets.only(left: 40, bottom: 4),
              child: Text(
                'PharmAI',
                style: PharmTextStyles.caption.copyWith(
                  color: PharmColors.primary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),

          // ── Bubble row ──
          Row(
            mainAxisAlignment: isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              // Bot avatar
              if (!isUser) ...[
                Container(
                  width: 30,
                  height: 30,
                  margin: const EdgeInsets.only(right: 8),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: PharmColors.primary.withOpacity(0.1),
                    border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
                  ),
                  child: const Icon(Icons.auto_awesome, size: 14, color: PharmColors.primary),
                ),
              ],

              // Bubble
              Flexible(
                child: Container(
                  constraints: BoxConstraints(
                    maxWidth: MediaQuery.of(context).size.width * 0.75,
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: isUser
                        ? PharmColors.primary.withOpacity(0.12)
                        : PharmColors.surface,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(18),
                      topRight: const Radius.circular(18),
                      bottomLeft: Radius.circular(isUser ? 18 : 4),
                      bottomRight: Radius.circular(isUser ? 4 : 18),
                    ),
                    border: Border.all(
                      color: isUser
                          ? PharmColors.primary.withOpacity(0.3)
                          : PharmColors.cardBorder,
                    ),
                  ),
                  child: SelectableText(
                    message.text,
                    style: PharmTextStyles.bodyMedium.copyWith(
                      color: PharmColors.textPrimary,
                      height: 1.6,
                    ),
                  ),
                ),
              ),
            ],
          ),

          // ── Citation ──
          if (!isUser && message.citations != null && message.citations!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 6, left: 40),
              child: InkWell(
                onTap: () {},
                borderRadius: BorderRadius.circular(6),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: PharmColors.primary.withOpacity(0.06),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.menu_book_outlined, size: 12, color: PharmColors.primary),
                      const SizedBox(width: 5),
                      Text(
                        message.citations!.first.title,
                        style: PharmTextStyles.caption.copyWith(color: PharmColors.primary),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
