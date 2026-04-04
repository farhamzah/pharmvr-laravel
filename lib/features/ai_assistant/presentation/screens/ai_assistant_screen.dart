import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:pharmvrpro/core/theme/pharm_colors.dart';
import 'package:pharmvrpro/core/theme/pharm_text_styles.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/glowing_cta_button.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_suggestion_chip.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/category_card.dart';
import 'package:pharmvrpro/features/ai_assistant/presentation/widgets/ai_mode_card.dart';

class AiAssistantScreen extends ConsumerStatefulWidget {
  const AiAssistantScreen({super.key});

  @override
  ConsumerState<AiAssistantScreen> createState() => _AiAssistantScreenState();
}

class _AiAssistantScreenState extends ConsumerState<AiAssistantScreen> {
  String? _selectedMode;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(
          'AI ASSISTANT',
          style: PharmTextStyles.h4.copyWith(
            color: Theme.of(context).textTheme.displayLarge?.color,
            letterSpacing: 2.0,
            fontStyle: FontStyle.italic,
            fontWeight: FontWeight.w900,
          ),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.history_rounded, color: PharmColors.primary),
            onPressed: () => context.push('/ai-assistant/history'),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: RadialGradient(
            center: const Alignment(0, -0.4),
            radius: 1.2,
            colors: [
              PharmColors.primary.withValues(alpha: 0.05),
              Colors.transparent,
            ],
          ),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Column(
            children: [
              const SizedBox(height: 10),
              
              // ── Hero Section ──
              _buildHeroIcon(),
              const SizedBox(height: 24),
              
              Text(
                'PHARMVR AI ASSISTANT',
                style: PharmTextStyles.h2.copyWith(
                  color: Theme.of(context).textTheme.displayLarge?.color,
                  letterSpacing: 1.5,
                  fontWeight: FontWeight.w900,
                  fontStyle: FontStyle.italic,
                  fontSize: 22,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Proprietary pharmaceutical intelligence for GMP standards, CPOB protocols, and manufacturing excellence.',
                style: PharmTextStyles.bodyMedium.copyWith(
                  color: Theme.of(context).textTheme.bodyMedium?.color?.withOpacity(0.6),
                  height: 1.6,
                ),
                textAlign: TextAlign.center,
              ),
              
              const SizedBox(height: 32),

              _buildSectionHeader('SELECT NEURAL PROTOCOL'),
              const SizedBox(height: 4),
              Text(
                'Choose a specialized AI mode to focus the retrieval logic.',
                style: PharmTextStyles.caption.copyWith(color: Theme.of(context).textTheme.bodySmall?.color?.withOpacity(0.4)),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),

              // ── Assistant Modes ──
              AiModeCard(
                title: 'GMP/CPOB Expert',
                subtitle: 'Conceptual and regulatory compliance guidance.',
                icon: Icons.verified_user_rounded,
                isSelected: _selectedMode == 'gmp_expert',
                onTap: () {
                  if (_selectedMode == 'gmp_expert') {
                    _startChat(context, null);
                  } else {
                    setState(() => _selectedMode = 'gmp_expert');
                  }
                },
              ),
              AiModeCard(
                title: 'Training Support',
                subtitle: 'Summaries, examples, and educational support.',
                icon: Icons.school_rounded,
                isSelected: _selectedMode == 'training_support',
                onTap: () {
                  if (_selectedMode == 'training_support') {
                    _startChat(context, null);
                  } else {
                    setState(() => _selectedMode = 'training_support');
                  }
                },
              ),
              AiModeCard(
                title: 'Lab Procedures',
                subtitle: 'SOP steps and procedural operational logic.',
                icon: Icons.menu_book_rounded,
                isSelected: _selectedMode == 'lab_procedures',
                onTap: () {
                  if (_selectedMode == 'lab_procedures') {
                    _startChat(context, null);
                  } else {
                    setState(() => _selectedMode = 'lab_procedures');
                  }
                },
              ),

              const SizedBox(height: 12),
              Text(
                'Choose a mode, then ask your own question or use a suggested starter.',
                style: PharmTextStyles.caption.copyWith(
                  color: PharmColors.primary.withValues(alpha: 0.5),
                  fontSize: 10,
                  fontStyle: FontStyle.italic,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 32),
              
              // ── Suggestion Chips ──
              _buildSectionHeader(
                _selectedMode == null 
                  ? 'SUGGESTED QUESTIONS' 
                  : 'TRY ASKING ${_selectedMode!.replaceAll('_', ' ').toUpperCase()}'
              ),
              const SizedBox(height: 16),
              AnimatedSwitcher(
                duration: const Duration(milliseconds: 300),
                child: Wrap(
                  key: ValueKey(_selectedMode),
                  spacing: 8,
                  runSpacing: 10,
                  alignment: WrapAlignment.center,
                  children: _suggestions.map((s) => AiSuggestionChip(
                    label: s,
                    onTap: () => _startChat(context, s),
                  )).toList(),
                ),
              ),
              
              const SizedBox(height: 48),
              
              // ── Primary CTA ──
              GlowingCtaButton(
                text: _selectedMode == null ? 'Start General Chat' : 'Initialize Mode Session',
                icon: Icons.electric_bolt_rounded,
                onTap: () => _startChat(context, null),
              ),
              
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  void _startChat(BuildContext context, String? prompt) {
    final modeParam = _selectedMode != null ? '&mode=$_selectedMode' : '';
    final promptParam = prompt != null ? 'prompt=$prompt' : '';
    
    // Combine params
    String path = '/ai-assistant/chat/new';
    if (promptParam.isNotEmpty || modeParam.isNotEmpty) {
      path += '?';
      if (promptParam.isNotEmpty) {
        path += promptParam;
        if (modeParam.isNotEmpty) path += modeParam;
      } else {
        path += modeParam.replaceFirst('&', '');
      }
    }
    
    context.push(path);
  }

  Widget _buildSectionHeader(String title) {
    return Row(
      children: [
        Container(
          width: 24,
          height: 1,
          color: PharmColors.primary.withValues(alpha: 0.3),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Text(
            title,
            style: PharmTextStyles.overline.copyWith(
              color: PharmColors.primary,
              letterSpacing: 3.0,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        Expanded(
          child: Container(
            height: 1,
            color: PharmColors.primary.withValues(alpha: 0.3),
          ),
        ),
      ],
    );
  }

  // ... (Hero Icon logic remains same, but using package imports already)
  Widget _buildHeroIcon() {
    return Stack(
      alignment: Alignment.center,
      children: [
        // Outer glow
        Container(
          width: 140,
          height: 140,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: PharmColors.primary.withValues(alpha: 0.1),
                blurRadius: 40,
                spreadRadius: 10,
              ),
            ],
          ),
        ),
        // Inner Ring
        Container(
          width: 100,
          height: 100,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(
              color: PharmColors.primary.withValues(alpha: 0.2),
              width: 1,
            ),
          ),
        ),
        // Icon Container
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Theme.of(context).colorScheme.surface,
            border: Border.all(
              color: PharmColors.primary.withValues(alpha: 0.4),
              width: 2,
            ),
            boxShadow: [
              BoxShadow(
                color: PharmColors.primary.withValues(alpha: 0.2),
                blurRadius: 15,
                spreadRadius: 2,
              ),
            ],
          ),
          child: const Center(
            child: Icon(
              Icons.auto_awesome,
              size: 40,
              color: PharmColors.primary,
            ),
          ),
        ),
      ],
    );
  }

  List<String> get _suggestions {
    switch (_selectedMode) {
      case 'gmp_expert':
        return [
          'What is CPOB 2024?',
          'Compliance validation',
          'Audit trail requirements',
        ];
      case 'training_support':
        return [
          'Summary of gowning',
          'Training exam examples',
          'Educational case study',
        ];
      case 'lab_procedures':
        return [
          'Scale calibration SOP',
          'Sample labeling rules',
          'Laboratory safety steps',
        ];
      default:
        return [
          'What is GMP?',
          'Cleanroom gowning',
          'CPOB overview',
          'Line clearance',
        ];
    }
  }
}
