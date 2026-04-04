import 'package:flutter/foundation.dart';

@immutable
class CitedSource {
  final String title;
  final String category;
  final String? topic;
  final int? pageNumber;
  final String excerpt;
  final String trustLevel; // verified, trusted, internal
  final String? url;

  const CitedSource({
    required this.title,
    required this.category,
    this.topic,
    this.pageNumber,
    required this.excerpt,
    required this.trustLevel,
    this.url,
  });

  factory CitedSource.fromJson(Map<String, dynamic> json) {
    return CitedSource(
      title: (json['title'] ?? json['section_title'] ?? 'Unknown Source').toString(),
      category: json['category'] as String? ?? 'General',
      topic: json['topic'] as String?,
      pageNumber: json['page_number'] != null ? int.tryParse(json['page_number'].toString()) : null,
      excerpt: json['excerpt'] as String? ?? '',
      trustLevel: json['trust_level'] as String? ?? 'verified',
      url: json['url'] as String?,
    );
  }
}
