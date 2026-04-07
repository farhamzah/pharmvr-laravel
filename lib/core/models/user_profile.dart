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

  const UserProfile({
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
      firstName: json['first_name']?.toString(),
      lastName: json['last_name']?.toString(),
      phone: json['phone']?.toString() ?? json['phone_number']?.toString(),
      avatarUrl: json['avatar_url']?.toString(),
      bio: json['bio']?.toString(),
      birthDate: json['birth_date'] != null ? DateTime.tryParse(json['birth_date'].toString()) : null,
      gender: json['gender']?.toString(),
      institution: json['institution']?.toString(),
      university: json['university']?.toString(),
      semester: json['semester'] != null ? int.tryParse(json['semester'].toString()) : null,
      nim: json['nim']?.toString(),
    );
  }

  UserProfile copyWith({
    String? firstName,
    String? lastName,
    String? phone,
    String? avatarUrl,
    String? bio,
    DateTime? birthDate,
    String? gender,
    String? institution,
    String? university,
    int? semester,
    String? nim,
  }) {
    return UserProfile(
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      phone: phone ?? this.phone,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      bio: bio ?? this.bio,
      birthDate: birthDate ?? this.birthDate,
      gender: gender ?? this.gender,
      institution: institution ?? this.institution,
      university: university ?? this.university,
      semester: semester ?? this.semester,
      nim: nim ?? this.nim,
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
