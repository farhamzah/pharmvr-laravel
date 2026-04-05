import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';
import '../../domain/models/ai_message.dart';
import 'chat_bubble.dart';

class ChatMessageList extends StatelessWidget {
  final List<AiMessage> messages;
  final bool isTyping;
  final ScrollController? scrollController;

  const ChatMessageList({
    super.key,
    required this.messages,
    this.isTyping = false,
    this.scrollController,
  });

  @override
  Widget build(BuildContext context) {
    if (messages.isEmpty && !isTyping) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.forum_outlined,
              size: 48,
              color: PharmColors.textTertiary.withOpacity(0.5),
            ),
            const SizedBox(height: PharmSpacing.md),
            Text(
              'Mulai percakapan baru',
              style: PharmTextStyles.bodyMedium.copyWith(
                color: PharmColors.textTertiary,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      controller: scrollController,
      padding: const EdgeInsets.symmetric(horizontal: PharmSpacing.md, vertical: PharmSpacing.lg),
      reverse: true, // Newest messages at the bottom
      itemCount: messages.length + (isTyping ? 1 : 0),
      itemBuilder: (context, index) {
        if (isTyping && index == 0) {
          // Render typing indicator as the newest item (index 0)
          return const _TypingIndicatorBubble();
        }

        // Adjust index if typing indicator is present
        final messageIndex = isTyping ? index - 1 : index;
        final message = messages[messageIndex];

        return Padding(
          padding: const EdgeInsets.only(bottom: PharmSpacing.md),
          child: ChatBubble(message: message),
        );
      },
    );
  }
}

class _TypingIndicatorBubble extends StatelessWidget {
  const _TypingIndicatorBubble();

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: PharmSpacing.md, right: 60),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: PharmColors.surfaceLight,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(16),
            topRight: Radius.circular(16),
            bottomRight: Radius.circular(16),
            bottomLeft: Radius.circular(4),
          ),
          border: Border.all(color: PharmColors.cardBorder),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildDot(0),
            const SizedBox(width: 4),
            _buildDot(150),
            const SizedBox(width: 4),
            _buildDot(300),
          ],
        ),
      ),
    );
  }

  Widget _buildDot(int delayOffset) {
    return const SizedBox(
      width: 6,
      height: 6,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: PharmColors.textTertiary,
          shape: BoxShape.circle,
        ),
      ),
    );
  }
}
