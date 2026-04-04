class NewsArticle {
  final int id;
  final String title;
  final String slug;
  final String excerpt;
  final String? content;
  final String? bannerUrl;
  final String category;
  final DateTime publishedAt;
  final String author;
  final int readingTime;
  
  // External News Properties
  final String contentType;
  final bool isPinned;
  final String? originalUrl;
  final String? sourceName;
  final String? sourceLogo;
  final String? aiSummary;
  final List<String>? aiTags;
  final String? topicCategory;

  bool get isExternal => contentType == 'external';
  bool get isInternal => contentType == 'internal';

  NewsArticle({
    required this.id,
    required this.title,
    required this.slug,
    required this.excerpt,
    this.content,
    this.bannerUrl,
    required this.category,
    required this.publishedAt,
    required this.author,
    required this.readingTime,
    this.contentType = 'internal',
    this.isPinned = false,
    this.originalUrl,
    this.sourceName,
    this.sourceLogo,
    this.aiSummary,
    this.aiTags,
    this.topicCategory,
  });

  factory NewsArticle.fromJson(Map<String, dynamic> json) {
    // Helper to parse reading time from string like "5 min read" or integer
    int parseReadingTime(dynamic value) {
      if (value is int) return value;
      if (value is String) {
        final match = RegExp(r'(\d+)').firstMatch(value);
        if (match != null) {
          return int.parse(match.group(1)!);
        }
      }
      return 5; // Default reading time
    }

    return NewsArticle(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title'] as String? ?? 'No Title',
      slug: json['slug'] as String? ?? '',
      excerpt: (json['excerpt'] as String?) ?? (json['summary'] as String?) ?? '',
      content: json['content'] as String?,
      bannerUrl: (json['banner_url'] as String?) ?? (json['image_url'] as String?),
      category: json['category'] as String? ?? 'General',
      publishedAt: json['published_at'] != null 
          ? DateTime.tryParse(json['published_at'] as String) ?? DateTime.now()
          : DateTime.now(),
      author: json['author'] as String? ?? 'PharmVR Team',
      readingTime: parseReadingTime(json['read_time'] ?? json['reading_time'] ?? 5),
      contentType: json['content_type'] as String? ?? 'internal',
      isPinned: json['is_pinned'] as bool? ?? false,
      originalUrl: json['original_url'] as String?,
      sourceName: json['source_name'] as String?,
      sourceLogo: json['source_logo'] as String?,
      aiSummary: json['ai_summary'] as String?,
      aiTags: (json['ai_tags'] as List<dynamic>?)?.map((e) => e.toString()).toList(),
      topicCategory: json['topic_category'] as String?,
    );
  }
}
