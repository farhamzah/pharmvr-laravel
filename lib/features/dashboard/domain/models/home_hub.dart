import '../../../news/domain/models/news_article.dart';

class HomeHubData {
  final Map<String, dynamic> userGreeting;
  final String? bannerUrl;
  final VrStatusHeader vrStatusHeader;
  final HeroModuleCard? heroModuleCard;
  final Map<String, dynamic> progressSummary;
  final Map<String, dynamic> featuredLearningPreview;
  final List<NewsArticle> latestNewsPreview;
  final List<dynamic> smartActions;

  HomeHubData({
    required this.userGreeting,
    this.bannerUrl,
    required this.vrStatusHeader,
    this.heroModuleCard,
    required this.progressSummary,
    required this.featuredLearningPreview,
    required this.latestNewsPreview,
    required this.smartActions,
  });

  factory HomeHubData.fromJson(Map<String, dynamic> json) {
    final summary = json['progress_summary'] as Map<String, dynamic>? ?? {};
    return HomeHubData(
      userGreeting: json['user_greeting'] as Map<String, dynamic>? ?? {},
      bannerUrl: json['banner_url'] as String?,
      vrStatusHeader: VrStatusHeader.fromJson(json['vr_status_header'] as Map<String, dynamic>? ?? {}),
      heroModuleCard: json['hero_module_card'] != null 
          ? HeroModuleCard.fromJson(json['hero_module_card'] as Map<String, dynamic>) 
          : null,
      progressSummary: {
        'total_modules': (summary['total_modules'] as num?)?.toInt() ?? 0,
        'completed_modules': (summary['completed_modules'] as num?)?.toInt() ?? 0,
        'progress_percentage': (summary['progress_percentage'] as num?)?.toInt() ?? 0,
        'vr_sessions_count': (summary['vr_sessions_count'] as num?)?.toInt() ?? (summary['sessions_count'] as num?)?.toInt() ?? 0,
        'total_xp': (summary['total_xp'] as num?)?.toInt() ?? 0,
      },
      featuredLearningPreview: json['featured_learning_preview'] as Map<String, dynamic>? ?? {},
      latestNewsPreview: (json['latest_news_preview'] as List<dynamic>?)
              ?.map((e) => NewsArticle.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      smartActions: json['smart_actions'] as List<dynamic>? ?? [],
    );
  }
}

class VrStatusHeader {
  final bool isPaired;
  final String deviceType;
  final String connectionStatus;
  final String pairedStatus;
  final String headsetName;
  final String? lastSeen;
  final bool readyToEnter;
  final Map<String, dynamic>? activeSession;
  final List<String> launchReadinessHints;
  final String recommendedNextAction;
  final String recommendedNextRoute;

  VrStatusHeader({
    required this.isPaired,
    required this.deviceType,
    required this.connectionStatus,
    required this.pairedStatus,
    required this.headsetName,
    this.lastSeen,
    required this.readyToEnter,
    this.activeSession,
    required this.launchReadinessHints,
    required this.recommendedNextAction,
    required this.recommendedNextRoute,
  });

  factory VrStatusHeader.fromJson(Map<String, dynamic> json) {
    return VrStatusHeader(
      isPaired: json['is_paired'] as bool? ?? false,
      deviceType: json['device_type'] as String? ?? 'meta_quest_3',
      connectionStatus: json['connection_status'] as String? ?? 'offline',
      pairedStatus: json['paired_status'] as String? ?? 'inactive',
      headsetName: json['headset_name'] as String? ?? 'No Device Paired',
      lastSeen: json['last_seen'] as String?,
      readyToEnter: json['ready_to_enter'] as bool? ?? false,
      activeSession: json['active_session'] as Map<String, dynamic>?,
      launchReadinessHints: List<String>.from(json['launch_readiness_hints'] ?? []),
      recommendedNextAction: json['recommended_next_action'] as String? ?? 'Connect VR',
      recommendedNextRoute: json['recommended_next_route'] as String? ?? '/vr/connect',
    );
  }
}

class HeroModuleCard {
  final int id;
  final String code;
  final String title;
  final String description;
  final String estimatedDuration;
  final String difficulty;
  final bool isReady;
  final String actionLabel;
  final List<String> actions;
  final String? currentStep;

  HeroModuleCard({
    required this.id,
    required this.code,
    required this.title,
    required this.description,
    required this.estimatedDuration,
    required this.difficulty,
    required this.isReady,
    required this.actionLabel,
    required this.actions,
    this.currentStep,
  });

  factory HeroModuleCard.fromJson(Map<String, dynamic> json) {
    return HeroModuleCard(
      id: json['id'] as int? ?? 0,
      code: json['code'] as String? ?? '',
      title: json['title'] as String? ?? 'No Module Selected',
      description: json['description'] as String? ?? '',
      estimatedDuration: json['estimated_duration'] as String? ?? '0 min',
      difficulty: json['difficulty'] as String? ?? 'Beginner',
      isReady: json['is_ready'] as bool? ?? false,
      actionLabel: json['action_label'] as String? ?? 'Learn More',
      actions: List<String>.from(json['actions'] ?? []),
      currentStep: json['current_step'] as String?,
    );
  }
}
