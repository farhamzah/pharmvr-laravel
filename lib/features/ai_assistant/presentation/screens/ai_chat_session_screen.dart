import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/features/ai_assistant/domain/models/ai_message.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/providers/chat_provider.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_message_bubble.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_suggestion_chip.dart';

class AiChatSessionScreen extends ConsumerStatefulWidget {
  final String? sessionId;
  final String? initialPrompt;
  final String? assistantMode;

  const AiChatSessionScreen({
    super.key,
    this.sessionId,
    this.initialPrompt,
    this.assistantMode,
  });

  @override
  ConsumerState<AiChatSessionScreen> createState() => _AiChatSessionScreenState();
}

class _AiChatSessionScreenState extends ConsumerState<AiChatSessionScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  String? _currentSessionId;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    _currentSessionId = widget.sessionId;
    
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      if (_currentSessionId == null) {
        await _initializeSession();
      }
      
      if (widget.initialPrompt != null) {
        _sendMessage(widget.initialPrompt!);
      }
    });
  }

  Future<void> _initializeSession() async {
    try {
      final session = await ref.read(aiAssistantRepositoryProvider).startSession(
        title: widget.initialPrompt != null ? 'Chat: ${widget.initialPrompt}' : 'New Neural Session',
        assistantMode: widget.assistantMode,
      );
      if (mounted) {
        setState(() {
          _currentSessionId = session.id;
        });
        // Refresh session list in background
        ref.read(aiSessionsProvider.notifier).refresh();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to initialize session: $e')),
        );
      }
    }
  }

  Future<void> _sendMessage(String text) async {
    final trimmedText = text.trim();
    
    // 1. Client-side Validation (Fix for Requirement 3)
    if (trimmedText.length < 3) {
      if (mounted && trimmedText.isNotEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Pertanyaan minimal 3 karakter'),
            backgroundColor: Colors.amber,
          ),
        );
      }
      return;
    }

    if (_currentSessionId == null || _isSending) return;
    
    setState(() => _isSending = true);
    // DO NOT clear yet (Fix for Requirement 3)
    _scrollToBottom();

    try {
      await ref.read(chatControllerProvider(_currentSessionId!)).sendMessage(
        text: trimmedText,
        assistantMode: widget.assistantMode,
      );
      
      // SUCCESS: Clear input (Requirement 3)
      _messageController.clear();
      
    } catch (e) {
      if (mounted) {
        String errorMsg = 'Signal loss: $e';
        
        // Error Mapping (Requirement 6)
        if (e.toString().contains('422')) {
          errorMsg = 'Permintaan tidak valid (min. 3 karakter)';
        } else if (e.toString().contains('SocketException') || e.toString().contains('connection')) {
          errorMsg = 'Koneksi ke server bermasalah';
        } else if (e.toString().contains('404')) {
          errorMsg = 'AI belum memiliki referensi yang sesuai';
        }

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMsg),
            backgroundColor: Colors.redAccent,
          ),
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
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_currentSessionId == null) {
      return const Scaffold(
        backgroundColor: PharmColors.background,
        body: Center(child: CircularProgressIndicator(color: PharmColors.primary)),
      );
    }

    final messagesState = ref.watch(chatMessagesProvider(_currentSessionId!));

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'NEURAL INTERFACE',
              style: PharmTextStyles.overline.copyWith(
                color: PharmColors.primary,
                letterSpacing: 2.0,
                fontWeight: FontWeight.w900,
              ),
            ),
            Row(
              children: [
                Text(
                  'ACTIVE_SESSION: ${(_currentSessionId!.length > 8 ? _currentSessionId!.substring(0, 8) : _currentSessionId!).toUpperCase()}',
                  style: PharmTextStyles.caption.copyWith(
                    color: Colors.white.withValues(alpha: 0.5),
                    fontSize: 10,
                    letterSpacing: 0.5,
                  ),
                ),
                if (widget.assistantMode != null) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: PharmColors.primary.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(4),
                      border: Border.all(color: PharmColors.primary.withValues(alpha: 0.3)),
                    ),
                    child: Text(
                      widget.assistantMode!.toUpperCase().replaceAll('_', ' '),
                      style: const TextStyle(color: PharmColors.primary, fontSize: 8, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
        backgroundColor: PharmColors.surface.withValues(alpha: 0.8),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline, color: PharmColors.primary),
            onPressed: () => _showSessionInfo(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: messagesState.when(
              loading: () => const Center(child: CircularProgressIndicator(color: PharmColors.primary)),
              error: (err, stack) => Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('ERROR_LOST_SIGNAL', style: PharmTextStyles.overline.copyWith(color: Colors.red)),
                    const SizedBox(height: 8),
                    Text('$err', style: const TextStyle(color: Colors.white24, fontSize: 12)),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () => ref.invalidate(chatMessagesProvider(_currentSessionId!)),
                      child: const Text('RETRY'),
                    ),
                  ],
                ),
              ),
              data: (messages) {
                if (messages.isEmpty && !_isSending) {
                  return _buildEmptyChat();
                }
                
                // Auto-scroll when new message arrives
                _scrollToBottom();

                return Scrollbar(
                  controller: _scrollController,
                  child: ListView.builder(
                    controller: _scrollController,
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
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
                      final message = messages[index];
                      return AiMessageBubble(
                        message: message,
                        onCitationTap: (source) => _showSourceDetail(source),
                        onSuggestionTap: (s) => _sendMessage(s),
                        onRelatedTopicTap: () => _sendMessage('Can you show me related topics for this?'),
                        onSupportedTopicsTap: () => _sendMessage('What topics are currently supported?'),
                      );
                    },
                  ),
                );
              },
            ),
          ),
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildEmptyChat() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.auto_awesome, color: PharmColors.primary.withValues(alpha: 0.2), size: 64),
          const SizedBox(height: 16),
          Text(
            'AWAITING INPUT...',
            style: PharmTextStyles.overline.copyWith(
              color: PharmColors.primary.withValues(alpha: 0.4),
              letterSpacing: 4.0,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputArea() {
    final List<String> suggestions;
    switch (widget.assistantMode) {
      case 'gmp_expert':
        suggestions = ['Process Validation', 'Cleanroom levels', 'Deviation handling', 'CPOB Standard 2024'];
        break;
      case 'training_support':
        suggestions = ['Gowning quiz', 'SOP summary', 'Calculation exam', 'Educational case study'];
        break;
      case 'lab_procedures':
        suggestions = ['Calibrating scale', 'Sample prep', 'Waste disposal', 'Safety protocols'];
        break;
      default:
        suggestions = ['Tell me more', 'Give examples', 'GMP requirements', 'System overview'];
    }

    // Usability: Handle both Keyboard and Notch (Requirement 5)
    final bottomPadding = MediaQuery.of(context).viewInsets.bottom > 0
        ? 12.0
        : 12.0 + MediaQuery.of(context).padding.bottom;

    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 16,
        bottom: bottomPadding,
      ),
      decoration: BoxDecoration(
        color: PharmColors.surface.withValues(alpha: 0.95),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
        border: Border(
          top: BorderSide(color: PharmColors.primary.withValues(alpha: 0.2), width: 1.5),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.4),
            blurRadius: 20,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Quick suggestion chips - REQUIREMENT 6: Horizontal scrolling
          if (_messageController.text.isEmpty && !_isSending)
            Container(
              height: 40,
              margin: const EdgeInsets.only(bottom: 16),
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: suggestions.length,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: AiSuggestionChip(
                      label: suggestions[index],
                      onTap: () => _sendMessage(suggestions[index]),
                    ),
                  );
                },
              ),
            ),
            
          Row(
            crossAxisAlignment: CrossAxisAlignment.end, // Align to baseline as it grows
            children: [
              Expanded(
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  decoration: BoxDecoration(
                    color: PharmColors.background,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(
                      color: _isSending ? Colors.white12 : PharmColors.primary.withValues(alpha: 0.3),
                      width: 1.5,
                    ),
                  ),
                  child: TextField(
                    controller: _messageController,
                    onSubmitted: _sendMessage,
                    enabled: !_isSending,
                    maxLines: 5, // REQUIREMENT 3: Multiline support
                    minLines: 1,
                    textInputAction: TextInputAction.send,
                    style: const TextStyle(color: Colors.white, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: widget.assistantMode == 'gmp_expert' 
                        ? 'Inquire for compliance...'
                        : widget.assistantMode == 'training_support'
                          ? 'Request learning aid...'
                          : widget.assistantMode == 'lab_procedures'
                            ? 'Ask for procedural steps...'
                            : 'Transmit inquiry...',
                      hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.2), fontSize: 14),
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Padding(
                padding: const EdgeInsets.only(bottom: 2), // Slight lift to align with single line input
                child: _buildSendButton(),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSendButton() {
    return Container(
      decoration: const BoxDecoration(
        color: PharmColors.primary,
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: PharmColors.primary,
            blurRadius: 8,
            spreadRadius: -2,
          ),
        ],
      ),
      child: IconButton(
        icon: _isSending 
          ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
        onPressed: _isSending ? null : () => _sendMessage(_messageController.text),
      ),
    );
  }

  void _showSourceDetail(String? sourceTitle) {
    if (sourceTitle == null) return;
    // Show Source Detail Bottom Sheet
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => _SourceDetailSheet(sourceTitle: sourceTitle),
    );
  }

  void _showSessionInfo() {
    // Optional session info dialog
  }
}

class _SourceDetailSheet extends StatelessWidget {
  final String sourceTitle;

  const _SourceDetailSheet({required this.sourceTitle});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: const BoxDecoration(
        color: PharmColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
        border: Border(
           top: BorderSide(color: PharmColors.primary, width: 2),
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              const Icon(Icons.verified_rounded, color: PharmColors.primary, size: 20),
              const SizedBox(width: 10),
              Text(
                'VERIFIED_SOURCE',
                style: PharmTextStyles.overline.copyWith(
                  color: PharmColors.primary,
                  letterSpacing: 2.0,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            sourceTitle.toUpperCase(),
            style: PharmTextStyles.h3.copyWith(color: Colors.white, fontStyle: FontStyle.italic),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: PharmColors.background.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: PharmColors.cardBorder),
            ),
            child: Text(
              'Supporting excerpt from the knowledge base would appear here, providing context and verification for the neural response delivered by PharmVR AI.',
              style: PharmTextStyles.bodyMedium.copyWith(
                color: Colors.white.withValues(alpha: 0.7),
                height: 1.6,
              ),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildMeta('CATEGORY', 'GMP_PROTOCOL'),
              _buildMeta('TRUST_INDEX', '99.8%'),
              _buildMeta('PAGE', 'SEQ_142'),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
             width: double.infinity,
             child: ElevatedButton(
               style: ElevatedButton.styleFrom(
                 backgroundColor: PharmColors.primary,
                 foregroundColor: Colors.white,
                 padding: const EdgeInsets.symmetric(vertical: 16),
                 shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
               ),
               onPressed: () => Navigator.pop(context),
               child: const Text('DISMISS'),
             ),
          ),
          const SizedBox(height: 12),
        ],
      ),
    );
  }

  Widget _buildMeta(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: PharmTextStyles.overline.copyWith(fontSize: 8, color: Colors.white38)),
        const SizedBox(height: 4),
        Text(value, style: PharmTextStyles.label.copyWith(color: PharmColors.primary, fontWeight: FontWeight.bold)),
      ],
    );
  }
}
