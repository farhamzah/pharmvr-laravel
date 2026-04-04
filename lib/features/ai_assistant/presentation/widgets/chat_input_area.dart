import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_spacing.dart';
import '../../../../core/theme/pharm_text_styles.dart';

class ChatInputArea extends StatefulWidget {
  final ValueChanged<String> onSendMessage;
  final bool isTyping; // Prevent spamming while AI generates

  const ChatInputArea({
    super.key,
    required this.onSendMessage,
    this.isTyping = false,
  });

  @override
  State<ChatInputArea> createState() => _ChatInputAreaState();
}

class _ChatInputAreaState extends State<ChatInputArea> {
  final TextEditingController _controller = TextEditingController();

  void _handleSend() {
    final text = _controller.text;
    if (text.trim().isNotEmpty && !widget.isTyping) {
      widget.onSendMessage(text);
      _controller.clear();
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(
        left: PharmSpacing.md,
        right: PharmSpacing.md,
        top: PharmSpacing.sm,
        bottom: PharmSpacing.lg, // Safe area padding natively added roughly
      ),
      decoration: BoxDecoration(
        color: PharmColors.surface,
        border: Border(
          top: BorderSide(
            color: PharmColors.divider,
            width: 1,
          ),
        ),
      ),
      child: SafeArea(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            // Attachment Button
            IconButton(
              icon: const Icon(Icons.attach_file_rounded),
              color: PharmColors.textTertiary,
              onPressed: widget.isTyping 
                ? null 
                : () {
                  // Future: Image upload for AI label reading
                },
            ),
            
            // Text Field
            Expanded(
              child: Container(
                constraints: const BoxConstraints(
                  maxHeight: 120, // Limit grow height
                ),
                decoration: BoxDecoration(
                  color: PharmColors.surfaceLight,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: PharmColors.cardBorder,
                  ),
                ),
                child: TextField(
                  controller: _controller,
                  enabled: !widget.isTyping,
                  maxLines: null, // allows growing
                  textCapitalization: TextCapitalization.sentences,
                  style: PharmTextStyles.bodyMedium.copyWith(
                    color: PharmColors.textPrimary,
                  ),
                  decoration: InputDecoration(
                    hintText: widget.isTyping ? 'PharmAI sedang mengetik...' : 'Tanyakan sesuatu...',
                    hintStyle: PharmTextStyles.bodyMedium.copyWith(
                      color: PharmColors.textTertiary,
                    ),
                    border: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: PharmSpacing.lg,
                      vertical: 14,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: PharmSpacing.sm),
            
            // Send / Mic Button
            Container(
              decoration: BoxDecoration(
                color: widget.isTyping ? PharmColors.surfaceLight : PharmColors.primaryDark,
                shape: BoxShape.circle,
                boxShadow: widget.isTyping ? null : [
                  BoxShadow(
                    color: PharmColors.primaryDark.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: IconButton(
                icon: Icon(
                  widget.isTyping ? Icons.more_horiz : Icons.send_rounded,
                  color: widget.isTyping ? PharmColors.textTertiary : PharmColors.background,
                ),
                onPressed: widget.isTyping ? null : _handleSend,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
