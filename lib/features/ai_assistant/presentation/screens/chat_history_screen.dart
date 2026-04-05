import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/providers/chat_provider.dart';

class ChatHistoryScreen extends ConsumerWidget {
  const ChatHistoryScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sessionsState = ref.watch(aiSessionsProvider);

    return Scaffold(
      backgroundColor: PharmColors.background,
      appBar: AppBar(
        title: Text(
          'NEURAL ARCHIVE',
          style: PharmTextStyles.h4.copyWith(
            color: Colors.white,
            letterSpacing: 2.0,
            fontStyle: FontStyle.italic,
            fontWeight: FontWeight.w900,
          ),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: sessionsState.when(
        loading: () => const Center(child: CircularProgressIndicator(color: PharmColors.primary)),
        error: (err, stack) => _buildError(ref),
        data: (sessions) {
          if (sessions.isEmpty) {
            return _buildEmpty();
          }

          return ListView.separated(
            padding: const EdgeInsets.all(20),
            itemCount: sessions.length,
            separatorBuilder: (context, index) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final session = sessions[index];
              return _SessionTile(session: session);
            },
          );
        },
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.history_toggle_off, color: PharmColors.primary.withOpacity(0.2), size: 64),
          const SizedBox(height: 16),
          Text(
            'ARCHIVE_EMPTY',
            style: PharmTextStyles.overline.copyWith(
              color: PharmColors.primary.withOpacity(0.4),
              letterSpacing: 4.0,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'No previous neural transmissions found.',
            style: PharmTextStyles.caption.copyWith(color: Colors.white24),
          ),
        ],
      ),
    );
  }

  Widget _buildError(WidgetRef ref) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, color: Colors.redAccent, size: 48),
          const SizedBox(height: 16),
          const Text('SYNC_FAILURE: UNABLE TO RETRIEVE ARCHIVE', style: TextStyle(color: Colors.redAccent)),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => ref.read(aiSessionsProvider.notifier).refresh(),
            child: const Text('RETRY_SYNC'),
          ),
        ],
      ),
    );
  }
}

class _SessionTile extends StatelessWidget {
  final dynamic session;

  const _SessionTile({required this.session});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => context.push('/ai-assistant/chat/${session.id}'),
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: PharmColors.surface.withOpacity(0.6),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: PharmColors.cardBorder),
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: PharmColors.primary.withOpacity(0.1),
                ),
                child: const Icon(Icons.chat_bubble_outline, color: PharmColors.primary, size: 20),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      session.title.toUpperCase(),
                      style: PharmTextStyles.label.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      session.lastMessagePreview ?? 'No neural data recorded.',
                      style: PharmTextStyles.caption.copyWith(
                        color: Colors.white.withOpacity(0.4),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    DateFormat('MMM dd').format(session.updatedAt),
                    style: PharmTextStyles.overline.copyWith(
                      color: PharmColors.primary,
                      fontSize: 9,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Icon(Icons.chevron_right, color: Colors.white24, size: 16),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
