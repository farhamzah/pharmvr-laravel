import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/features/ai_assistant/domain/models/ai_message.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/providers/chat_provider.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_message_bubble.dart';

/// A compact AI chat modal that can be launched from anywhere in the app.
/// Shows a quick chat interface with PharmAI without leaving the current screen.
class AiQuickChatModal extends ConsumerStatefulWidget {
  final String? contextHint;

  const AiQuickChatModal({super.key, this.contextHint});

  /// Show the AI quick chat modal from any context
  static void show(BuildContext context, {String? contextHint}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => AiQuickChatModal(contextHint: contextHint),
    );
  }

  @override
  ConsumerState<AiQuickChatModal> createState() => _AiQuickChatModalState();
}

class _AiQuickChatModalState extends ConsumerState<AiQuickChatModal> {
  final _controller = TextEditingController();
  final _scrollController = ScrollController();
  static const _chatKey = '_quick_chat';
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    // Quick chat usually uses a session with a fixed placeholder ID
  }

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _send(String text) async {
    final trimmed = text.trim();
    
    // Requirement 4: Consistently validate across all paths
    if (trimmed.length < 3) {
      if (mounted && trimmed.isNotEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Pertanyaan minimal 3 karakter'),
            backgroundColor: Colors.amber,
          ),
        );
      }
      return;
    }

    if (_isSending) return;
    
    setState(() => _isSending = true);
    // DO NOT clear yet for consistency with main screen
    _scrollToBottom();

    try {
      await ref.read(chatControllerProvider(_chatKey)).sendMessage(text: trimmed);
      _controller.clear(); 
    } catch (e) {
      if (mounted) {
        String errorMsg = 'Signal loss: $e';
        if (e.toString().contains('422')) {
          errorMsg = 'Permintaan tidak valid (min. 3 karakter)';
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(errorMsg), backgroundColor: Colors.redAccent),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSending = false);
      }
    }
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent + 100,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final messagesState = ref.watch(chatMessagesProvider(_chatKey));
    final screenHeight = MediaQuery.of(context).size.height;

    return Container(
      height: screenHeight * 0.75,
      decoration: const BoxDecoration(
        color: PharmColors.background,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        border: Border(
           top: BorderSide(color: PharmColors.primary, width: 1),
        ),
      ),
      child: Column(
        children: [
          // Handle bar
          const SizedBox(height: 10),
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: PharmColors.divider,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 12),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: PharmColors.primary.withOpacity(0.2),
                        blurRadius: 10,
                      )
                    ],
                    color: PharmColors.surface,
                    border: Border.all(color: PharmColors.primary.withOpacity(0.3)),
                  ),
                  child: const Icon(Icons.auto_awesome, size: 18, color: PharmColors.primary),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('PHARMAI_NEURAL_LINK',
                          style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 1.5)),
                      Text(
                        _isSending ? 'PROCESSING_SIGNAL...' : 'ONLINE_QUICK_ACCESS',
                        style: PharmTextStyles.caption.copyWith(
                          color: _isSending ? PharmColors.primary : Colors.white24,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.white24, size: 20),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Divider(height: 1, color: PharmColors.cardBorder.withOpacity(0.5)),

          // Messages
          Expanded(
            child: messagesState.when(
              loading: () => const Center(child: CircularProgressIndicator(color: PharmColors.primary)),
              error: (err, stack) => _buildQuickSuggestions(),
              data: (messages) {
                if (messages.isEmpty && !_isSending) {
                  return _buildQuickSuggestions();
                }
                
                return ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                  itemCount: messages.length + (_isSending ? 1 : 0),
                  itemBuilder: (context, index) {
                    if (index == messages.length) {
                       return AiMessageBubble(
                        message: AiMessage(
                          id: 'typing',
                          text: '...',
                          sender: AiSender.assistant,
                          timestamp: DateTime.now(),
                          isTyping: true,
                        ),
                      );
                    }
                    return AiMessageBubble(message: messages[index]);
                  },
                );
              },
            ),
          ),

          // Input Area (Requirement 1, 2, 5)
          Container(
            padding: EdgeInsets.fromLTRB(
              16, 12, 8,
              MediaQuery.of(context).viewInsets.bottom > 0
                  ? 12.0
                  : MediaQuery.of(context).padding.bottom + 16,
            ),
            decoration: BoxDecoration(
              color: PharmColors.surface.withOpacity(0.98),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              border: Border(
                top: BorderSide(color: PharmColors.primary.withOpacity(0.2), width: 1.5),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.3),
                  blurRadius: 15,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                      color: PharmColors.background,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
                    ),
                    child: TextField(
                      controller: _controller,
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                      cursorColor: PharmColors.primary,
                      maxLines: 4, // Requirement 3: Multiline
                      minLines: 1,
                      textInputAction: TextInputAction.send,
                      onSubmitted: _send,
                      decoration: InputDecoration(
                        hintText: 'Tanya PharmAI...',
                        hintStyle: TextStyle(color: Colors.white.withOpacity(0.15), fontSize: 13),
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                Padding(
                  padding: const EdgeInsets.only(bottom: 2),
                  child: IconButton(
                    icon: Icon(
                      Icons.send_rounded,
                      color: _isSending ? Colors.white10 : PharmColors.primary,
                      size: 22,
                    ),
                    onPressed: _isSending ? null : () => _send(_controller.text),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickSuggestions() {
    final suggestions = [
      'Apa itu CPOB?',
      'Cleanroom gowning protocol',
      'Line clearance procedure',
      if (widget.contextHint != null) widget.contextHint!,
    ];

    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.auto_awesome, size: 40, color: PharmColors.primary.withOpacity(0.2)),
            const SizedBox(height: 16),
            Text(
              'QUICK_INQUIRIES',
              style: PharmTextStyles.overline.copyWith(color: PharmColors.primary, letterSpacing: 2.0),
            ),
            const SizedBox(height: 24),
            SizedBox(
              height: 44,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 8),
                itemCount: suggestions.length,
                itemBuilder: (context, index) {
                  final text = suggestions[index];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    child: InkWell(
                      onTap: () => _send(text),
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        decoration: BoxDecoration(
                          color: PharmColors.surfaceLight.withOpacity(0.5),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: PharmColors.primary.withOpacity(0.2)),
                        ),
                        child: Center(
                          child: Text(
                            text,
                            style: const TextStyle(color: Colors.white70, fontSize: 11),
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
