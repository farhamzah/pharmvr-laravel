import 'package:flutter/material.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/features/ai_assistant/domain/models/ai_message.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_state_badge.dart';
import 'citation_card.dart';

class AiMessageBubble extends StatelessWidget {
  final AiMessage message;
  final void Function(String)? onCitationTap;
  final void Function(String)? onSuggestionTap;
  final VoidCallback? onRelatedTopicTap;
  final VoidCallback? onSupportedTopicsTap;

  const AiMessageBubble({
    super.key,
    required this.message,
    this.onCitationTap,
    this.onSuggestionTap,
    this.onRelatedTopicTap,
    this.onSupportedTopicsTap,
  });

  @override
  Widget build(BuildContext context) {
    // ... (rest of build remains mostly same until specific builders)
    final isAi = message.sender == AiSender.assistant;
    
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: isAi ? CrossAxisAlignment.start : CrossAxisAlignment.end,
        children: [
          Row(
            mainAxisAlignment: isAi ? MainAxisAlignment.start : MainAxisAlignment.end,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (isAi) ...[
                _buildAvatar(),
                const SizedBox(width: 12),
              ],
              Flexible(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                  decoration: BoxDecoration(
                    color: isAi ? PharmColors.surfaceLight : PharmColors.primary,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(20),
                      topRight: const Radius.circular(20),
                      bottomLeft: Radius.circular(isAi ? 4 : 20),
                      bottomRight: Radius.circular(isAi ? 20 : 4),
                    ),
                    border: isAi 
                      ? Border.all(color: PharmColors.cardBorder.withValues(alpha: 0.5))
                      : null,
                    boxShadow: [
                      if (!isAi)
                        BoxShadow(
                          color: PharmColors.primary.withValues(alpha: 0.2),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (isAi) AiStateBadge(responseMode: message.responseMode),
                      if (message.isRestricted && !isAi) _buildRestrictedLabel(),
                      Text(
                        message.text,
                        style: PharmTextStyles.bodyMedium.copyWith(
                          color: isAi ? Colors.white.withValues(alpha: 0.9) : Colors.white,
                          height: 1.5,
                          fontStyle: message.isRestricted ? FontStyle.italic : null,
                        ),
                      ),
                      if (isAi && message.citations != null && message.citations!.isNotEmpty) ...[
                        ...message.citations!.map((source) => CitationCard(
                          source: source,
                          onTap: () => onCitationTap?.call(source.title),
                        )),
                      ],
                      if (isAi && (message.responseMode == 'restricted' || message.responseMode == 'neutral'))
                        _buildUnsupportedActions(context),
                      if (isAi && message.suggestedFollowUps != null && message.suggestedFollowUps!.isNotEmpty) 
                        _buildSuggestedTopics(message.suggestedFollowUps!),
                    ],
                  ),
                ),
              ),
              if (!isAi) ...[
                const SizedBox(width: 12),
                _buildUserAvatar(),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAvatar() {
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: PharmColors.surface,
        border: Border.all(color: PharmColors.primary.withValues(alpha: 0.3)),
        boxShadow: [
          BoxShadow(
            color: PharmColors.primary.withValues(alpha: 0.1),
            blurRadius: 8,
          ),
        ],
      ),
      child: const Center(
        child: Icon(Icons.auto_awesome, color: PharmColors.primary, size: 18),
      ),
    );
  }

  Widget _buildUserAvatar() {
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: PharmColors.surfaceLight,
        border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
      ),
      child: const Center(
        child: Icon(Icons.person_outline, color: Colors.white70, size: 18),
      ),
    );
  }

  Widget _buildRestrictedLabel() {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.amber.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.amber.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.shield_outlined, color: Colors.amber, size: 12),
          const SizedBox(width: 6),
          Text(
            'DOMAIN_PROTECTION_ACTIVE',
            style: PharmTextStyles.overline.copyWith(
              color: Colors.amber,
              fontSize: 8,
              letterSpacing: 1.0,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildUnsupportedActions(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Divider(color: Colors.white10),
          const SizedBox(height: 8),
          Row(
            children: [
              if (onRelatedTopicTap != null)
                _buildActionButton(
                  context,
                  'Try related topic',
                  Icons.lightbulb_outline,
                  onRelatedTopicTap,
                ),
              const SizedBox(width: 8),
              if (onSupportedTopicsTap != null)
                _buildActionButton(
                  context,
                  'Supported topics',
                  Icons.list_alt,
                  onSupportedTopicsTap,
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton(BuildContext context, String label, IconData icon, VoidCallback? onTap) {
    if (onTap == null) return const SizedBox.shrink();
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: PharmColors.primary.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: PharmColors.primary.withValues(alpha: 0.2)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: PharmColors.primary, size: 12),
            const SizedBox(width: 6),
            Text(
              label.toUpperCase(),
              style: const TextStyle(color: PharmColors.primary, fontSize: 8, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSuggestedTopics(List<String> topics) {
    return Padding(
      padding: const EdgeInsets.only(top: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'NEURAL_SUGGESTIONS:',
            style: PharmTextStyles.overline.copyWith(
              color: Colors.white.withValues(alpha: 0.3),
              fontSize: 8,
              letterSpacing: 2.0,
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: topics.map((t) => InkWell(
              onTap: () => onSuggestionTap?.call(t),
              borderRadius: BorderRadius.circular(6),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
                ),
                child: Text(
                  t.toUpperCase(),
                  style: const TextStyle(color: Colors.white54, fontSize: 9, fontWeight: FontWeight.bold),
                ),
              ),
            )).toList(),
          ),
        ],
      ),
    );
  }
}
