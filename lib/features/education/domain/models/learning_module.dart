class LearningModule {
  final int id;
  final String code;
  final int? trainingModuleId;
  final String title;
  final String slug;
  final String type; // module, video, document
  final String sourceType; // upload, external
  final String category;
  final String level;
  final String description;
  final String? shortSummary;
  final String? prerequisites;
  final String? relatedMaterials;
  final String? aiContext;
  final String? thumbnailUrl;
  final String? fileUrl;
  final String? videoUrl;
  final String? videoId;
  final int? durationMinutes;
  final int? pagesCount;
  final String? ctaLabel;
  final Map<String, dynamic>? learningPath;
  final Map<String, dynamic>? recommendedAction;
  final JourneyProgress? journey;

  LearningModule({
    required this.id,
    required this.code,
    this.trainingModuleId,
    required this.title,
    required this.slug,
    required this.type,
    this.sourceType = 'external',
    required this.category,
    required this.level,
    required this.description,
    this.shortSummary,
    this.prerequisites,
    this.relatedMaterials,
    this.aiContext,
    this.thumbnailUrl,
    this.fileUrl,
    this.videoUrl,
    this.videoId,
    this.durationMinutes,
    this.pagesCount,
    this.ctaLabel,
    this.learningPath,
    this.recommendedAction,
    this.journey,
  });

  factory LearningModule.fromJson(Map<String, dynamic> json) {
    return LearningModule(
      id: json['id'] as int,
      code: json['code'] as String? ?? 'N/A',
      trainingModuleId: json['training_module_id'] as int?,
      title: json['title'] as String? ?? 'Untitled',
      slug: json['slug'] as String? ?? '',
      type: json['type'] as String? ?? 'module',
      sourceType: json['source_type'] as String? ?? 'external',
      category: json['category'] as String? ?? json['related_topic'] as String? ?? 'General',
      level: json['level'] as String? ?? 'Beginner',
      description: json['description'] as String? ?? '',
      shortSummary: json['short_summary'] as String?,
      prerequisites: json['prerequisites'] as String?,
      relatedMaterials: json['related_materials'] as String?,
      aiContext: json['ai_context'] as String?,
      thumbnailUrl: json['thumbnail_url'] as String?,
      fileUrl: json['file_url'] as String?,
      videoUrl: json['video_url'] as String?,
      videoId: json['video_id'] as String?,
      durationMinutes: json['duration_minutes'] as int?,
      pagesCount: json['pages_count'] as int?,
      ctaLabel: json['cta_label'] as String?,
      learningPath: json['learning_path'] as Map<String, dynamic>?,
      recommendedAction: json['recommended_action'] as Map<String, dynamic>?,
      journey: json['journey'] != null ? JourneyProgress.fromJson(json['journey'] as Map<String, dynamic>) : null,
    );
  }

  String? get effectiveThumbnailUrl {
    if (thumbnailUrl != null && thumbnailUrl!.isNotEmpty) {
      return thumbnailUrl;
    }
    if (type == 'video' && videoId != null && videoId!.isNotEmpty) {
      return 'https://i.ytimg.com/vi/$videoId/hqdefault.jpg';
    }
    // Default fallback for module types that might be missing thumbnails
    if (type == 'module') {
      return 'https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?auto=format&fit=crop&w=800&q=80';
    }
    return null;
  }
}

class JourneyStep {
  final String status; // locked, available, passed, failed, completed
  final String label;

  JourneyStep({required this.status, required this.label});

  factory JourneyStep.fromJson(Map<String, dynamic> json) {
    return JourneyStep(
      status: json['status'] as String? ?? 'locked',
      label: json['label'] as String? ?? '',
    );
  }
}

class JourneyProgress {
  final JourneyStep preTest;
  final JourneyStep vrSim;
  final JourneyStep postTest;
  final String? lastActiveStep;

  JourneyProgress({
    required this.preTest,
    required this.vrSim,
    required this.postTest,
    this.lastActiveStep,
  });

  factory JourneyProgress.fromJson(Map<String, dynamic> json) {
    return JourneyProgress(
      preTest: JourneyStep.fromJson(json['pre_test'] as Map<String, dynamic>),
      vrSim: JourneyStep.fromJson(json['vr_sim'] as Map<String, dynamic>),
      postTest: JourneyStep.fromJson(json['post_test'] as Map<String, dynamic>),
      lastActiveStep: json['last_active_step'] as String?,
    );
  }
}
