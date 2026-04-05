import 'user_profile.dart';

class User {
  final dynamic id; // Laravel typical BigInt/UUID
  final String name;
  final String email;
  final String role;
  final UserProfile? profile;
  final Map<String, dynamic>? preferences;

  const User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.profile,
    this.preferences,
  });

  String? get avatarUrl => profile?.avatarUrl;
  String? get phone => profile?.phone;
  String? get university => profile?.university;
  String? get institution => profile?.institution;
  int? get semester => profile?.semester;
  String? get nim => profile?.nim;
  String? get organization => profile?.university ?? profile?.institution;

  User copyWith({
    dynamic id,
    String? name,
    String? email,
    String? role,
    UserProfile? profile,
    Map<String, dynamic>? preferences,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      profile: profile ?? this.profile,
      preferences: preferences ?? this.preferences,
    );
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'] as String,
      email: json['email'] as String,
      role: json['role'] as String? ?? 'Mahasiswa',
      profile: json['profile'] != null 
          ? UserProfile.fromJson(json['profile'] as Map<String, dynamic>) 
          : null,
      preferences: json['preferences'] is Map<String, dynamic> 
          ? json['preferences'] as Map<String, dynamic> 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'profile': profile?.toJson(),
      'preferences': preferences,
    };
  }

  factory User.mock() {
    return User(
      id: 1,
      name: 'Farhan Maulana',
      email: 'farhan@pharmvr.cloud',
      role: 'Mahasiswa',
      profile: UserProfile(
        university: 'University of Padjadjaran',
        nim: '2006543210',
        semester: 6,
      ),
    );
  }
}
