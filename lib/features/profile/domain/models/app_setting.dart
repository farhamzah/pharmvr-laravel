class AppSetting {
  final String aboutMission;
  final String aboutDescription;
  final String privacyPolicyUrl;
  final String termsOfServiceUrl;
  final String officialWebsiteUrl;

  AppSetting({
    required this.aboutMission,
    required this.aboutDescription,
    required this.privacyPolicyUrl,
    required this.termsOfServiceUrl,
    required this.officialWebsiteUrl,
  });

  factory AppSetting.fromJson(Map<String, dynamic> json) {
    return AppSetting(
      aboutMission: json['about_mission'] ?? '',
      aboutDescription: json['about_description'] ?? '',
      privacyPolicyUrl: json['privacy_policy_url'] ?? '',
      termsOfServiceUrl: json['terms_of_service_url'] ?? '',
      officialWebsiteUrl: json['official_website_url'] ?? '',
    );
  }

  factory AppSetting.empty() {
    return AppSetting(
      aboutMission: '',
      aboutDescription: '',
      privacyPolicyUrl: '',
      termsOfServiceUrl: '',
      officialWebsiteUrl: '',
    );
  }
}
