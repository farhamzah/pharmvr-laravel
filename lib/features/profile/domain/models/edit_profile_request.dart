class EditProfileRequest {
  final String fullName;
  final String email;
  final String phoneNumber;
  final String university;
  final String semester;
  final String nim;

  final String? imagePath;

  const EditProfileRequest({
    required this.fullName,
    required this.email,
    required this.phoneNumber,
    required this.university,
    required this.semester,
    required this.nim,
    this.imagePath,
  });

  Map<String, dynamic> toJson() {
    return {
      'full_name': fullName,
      'email': email,
      'phone_number': phoneNumber,
      'university': university,
      'semester': semester,
      'nim': nim,
    };
  }
}
