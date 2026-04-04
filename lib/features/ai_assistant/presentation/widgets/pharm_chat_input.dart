import 'package:flutter/material.dart';
import '../../../../core/theme/pharm_colors.dart';
import '../../../../core/theme/pharm_text_styles.dart';

class PharmChatInput extends StatefulWidget {
  final Function(String) onSend;
  final bool isLoading;

  const PharmChatInput({super.key, required this.onSend, this.isLoading = false});

  @override
  State<PharmChatInput> createState() => _PharmChatInputState();
}

class _PharmChatInputState extends State<PharmChatInput> {
  final _controller = TextEditingController();
  final _focusNode = FocusNode();
  bool _isComposing = false;

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _handleSubmitted(String text) {
    final trimmed = text.trim();
    if (trimmed.isEmpty || trimmed.length > 2000 || widget.isLoading) return;
    widget.onSend(trimmed);
    _controller.clear();
    setState(() => _isComposing = false);
    _focusNode.requestFocus();
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = MediaQuery.of(context).padding.bottom;

    return Container(
      decoration: BoxDecoration(
        color: PharmColors.surface,
        border: Border(top: BorderSide(color: PharmColors.divider)),
      ),
      padding: EdgeInsets.only(left: 12, right: 12, top: 10, bottom: 10 + bottomPadding),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          // Input field
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: PharmColors.surfaceLight,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: PharmColors.divider),
              ),
              child: TextField(
                controller: _controller,
                focusNode: _focusNode,
                onChanged: (text) => setState(() => _isComposing = text.trim().isNotEmpty),
                onSubmitted: _handleSubmitted,
                minLines: 1,
                maxLines: 5,
                maxLength: 2000,
                textInputAction: TextInputAction.send,
                style: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textPrimary),
                cursorColor: PharmColors.primary,
                decoration: InputDecoration(
                  hintText: 'Ask about GMP, CPOB...',
                  hintStyle: PharmTextStyles.bodyMedium.copyWith(color: PharmColors.textTertiary),
                  border: InputBorder.none,
                  counterText: '',
                  contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),

          // Send button
          AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            width: 44,
            height: 44,
            margin: const EdgeInsets.only(bottom: 1),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: _canSend ? PharmColors.primary : PharmColors.surfaceLight,
              boxShadow: _canSend
                  ? [BoxShadow(color: PharmColors.primary.withOpacity(0.2), blurRadius: 8)]
                  : [],
            ),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                customBorder: const CircleBorder(),
                onTap: _canSend ? () => _handleSubmitted(_controller.text) : null,
                child: Center(
                  child: widget.isLoading
                      ? SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            color: PharmColors.textTertiary,
                            strokeWidth: 2,
                          ),
                        )
                      : Icon(
                          Icons.arrow_upward_rounded,
                          color: _canSend ? PharmColors.background : PharmColors.textTertiary,
                          size: 20,
                        ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  bool get _canSend => _isComposing && !widget.isLoading;
}
