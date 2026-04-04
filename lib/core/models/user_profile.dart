class UserProfile {
  final String? firstName;
  final String? lastName;
  final String? phone;
  final String? avatarUrl;
  final String? bio;
  final DateTime? birthDate;
  final String? gender;
  final String? institution;
  final String? university;
  final int? semester;
  final String? nim;

  UserProfile({
    this.firstName,
    this.lastName,
    this.phone,
    this.avatarUrl,
    this.bio,
    this.birthDate,
    this.gender,
    this.institution,
    this.university,
    this.semester,
    this.nim,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      firstName: json['first_name'] as String?,
      lastName: json['last_name'] as String?,
      phone: json['phone'] as String?,
      avatarUrl: json['avatar_url'] as String?,
      bio: json['bio'] as String?,
      birthDate: json['birth_date'] != null ? DateTime.parse(json['birth_date'] as String) : null,
      gender: json['gender'] as String?,
      institution: json['institution'] as String?,
      university: json['university'] as String?,
      semester: json['semester'] as int?,
      nim: json['nim'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'first_name': firstName,
      'last_name': lastName,
      'phone': phone,
      'avatar_url': avatarUrl,
      'bio': bio,
      'birth_date': birthDate?.toIso8601String(),
      'gender': gender,
      'institution': institution,
      'university': university,
      'semester': semester,
      'nim': nim,
    };
  }
}
