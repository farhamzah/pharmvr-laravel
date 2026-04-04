import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/chat_message.dart';
import 'package:flutter_markdown/flutter_markdown.dart';

class ChatBubble extends StatelessWidget {
  final ChatMessage message;

  const ChatBubble({
    super.key,
    required this.message,
  });

  @override
  Widget build(BuildContext context) {
    switch (message.sender) {
      case ChatSender.user:
        return _buildUserBubble(context);
      case ChatSender.ai:
        return _buildAiBubble(context);
      case ChatSender.system:
        return _buildSystemBubble(context);
    }
  }

  Widget _buildUserBubble(BuildContext context) {
    return Align(
      alignment: Alignment.centerRight,
      child: Container(
        margin: const EdgeInsets.only(left: 40), // Keeps bubble constrained
        padding: const EdgeInsets.all(PharmSpacing.md),
        decoration: BoxDecoration(
          color: PharmColors.primaryDark,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(20),
            topRight: Radius.circular(20),
            bottomLeft: Radius.circular(20),
            bottomRight: Radius.circular(4), // Sharp corner mapping
          ),
          boxShadow: [
            BoxShadow(
              color: PharmColors.primaryDark.withOpacity(0.2),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Text(
          message.text,
          style: PharmTextStyles.bodyMedium.copyWith(
            color: Colors.white, // User bubble text is white on primaryDark background
            height: 1.5,
          ),
        ),
      ),
    );
  }

  Widget _buildAiBubble(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(right: 32),
        padding: const EdgeInsets.all(PharmSpacing.md),
        decoration: BoxDecoration(
          color: PharmColors.surfaceLight,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(20),
            topRight: Radius.circular(20),
            bottomRight: Radius.circular(20),
            bottomLeft: Radius.circular(4),
          ),
          border: Border.all(color: Theme.of(context).brightness == Brightness.dark ? PharmColors.cardBorder : Theme.of(context).dividerColor.withOpacity(0.5)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            MarkdownBody(
              data: message.text,
              selectable: true,
              styleSheet: MarkdownStyleSheet(
                p: PharmTextStyles.bodyMedium.copyWith(
                  color: Theme.of(context).textTheme.displaySmall?.color,
                  height: 1.5,
                ),
                strong: PharmTextStyles.bodyBold.copyWith(
                  color: PharmColors.primary,
                ),
                listBullet: TextStyle(color: PharmColors.primary),
              ),
            ),
            
            // Render Citation if available
            if (message.citationSource != null) ...[
              const SizedBox(height: PharmSpacing.sm),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Theme.of(context).brightness == Brightness.dark ? PharmColors.background.withOpacity(0.5) : PharmColors.primary.withOpacity(0.05),
                  borderRadius: BorderRadius.circular(4),
                  border: Border.all(color: Theme.of(context).dividerColor),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.menu_book_rounded, size: 12, color: PharmColors.primary),
                    const SizedBox(width: 4),
                    Text(
                      message.citationSource!,
                      style: PharmTextStyles.caption.copyWith(
                        color: Theme.of(context).textTheme.bodySmall?.color,
                      ),
                    ),
                  ],
                ),
              ),
            ]
          ],
        ),
      ),
    );
  }

  Widget _buildSystemBubble(BuildContext context) {
    return Center(
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: PharmSpacing.md),
        padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.md, vertical: 8),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Theme.of(context).dividerColor),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.info_outline, size: 14, color: PharmColors.primary),
            const SizedBox(width: 8),
            Text(
              message.text,
              style: PharmTextStyles.caption.copyWith(
                color: Theme.of(context).textTheme.labelSmall?.color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
