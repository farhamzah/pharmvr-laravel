/// Centralized validation logic for all PharmVR forms.
/// All methods return `null` on success or a user-friendly error message.
class ValidatorBuilder {
  // --- Email ---
  static String? validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email address is required';
    }
    final trimmed = value.trim();
    final emailRegExp = RegExp(r'^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$');
    if (!emailRegExp.hasMatch(trimmed)) {
      return 'Please enter a valid email address';
    }
    return null;
  }

  // --- Password ---
  static String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters';
    }
    if (!RegExp(r'[A-Z]').hasMatch(value)) {
      return 'Password must contain at least one uppercase letter';
    }
    if (!RegExp(r'[a-z]').hasMatch(value)) {
      return 'Password must contain at least one lowercase letter';
    }
    if (!RegExp(r'[0-9]').hasMatch(value)) {
      return 'Password must contain at least one number';
    }
    if (!RegExp(r'[!@#\$&*~-]').hasMatch(value)) {
      return 'Password must contain at least one special character (!@#\$&*~-)';
    }
    return null;
  }

  // --- Confirm Password ---
  static String? Function(String?) validateConfirmPassword(String password) {
    return (String? value) {
      if (value == null || value.isEmpty) {
        return 'Please confirm your password';
      }
      if (value != password) {
        return 'Passwords do not match';
      }
      return null;
    };
  }

  // --- Required Field ---
  static String? validateRequired(String? value, String fieldName) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName is required';
    }
    return null;
  }

  // --- Name ---
  static String? validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Full name is required';
    }
    if (value.trim().length < 2) {
      return 'Name must be at least 2 characters';
    }
    if (value.trim().length > 100) {
      return 'Name cannot exceed 100 characters';
    }
    return null;
  }

  // --- Phone Number ---
  static String? validatePhone(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Phone number is required';
    }
    final digits = value.replaceAll(RegExp(r'[\s\-\+\(\)]'), '');
    if (digits.isEmpty) {
      return 'Enter a valid phone number';
    }
    if (!RegExp(r'^[\d\s\+\-\(\)]+$').hasMatch(value)) {
      return 'Phone number contains invalid characters';
    }
    return null;
  }

  // --- NIM (Student ID) ---
  static String? validateNim(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Student ID (NIM) is required';
    }
    return null;
  }

  // --- Semester ---
  static String? validateSemester(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Semester is required';
    }
    final number = int.tryParse(value.trim());
    if (number == null || number < 1 || number > 14) {
      return 'Enter a valid semester (1-14)';
    }
    return null;
  }

  // --- Generic Min Length ---
  static String? Function(String?) validateMinLength(int min, String fieldName) {
    return (String? value) {
      if (value == null || value.trim().isEmpty) {
        return '$fieldName is required';
      }
      if (value.trim().length < min) {
        return '$fieldName must be at least $min characters';
      }
      return null;
    };
  }

  // --- Chat Message (non-empty, trimmed) ---
  static String? validateChatMessage(String? value) {
    if (value == null || value.trim().isEmpty) {
      return null; // Silently prevent send, no error message needed
    }
    if (value.trim().length > 2000) {
      return 'Message is too long (max 2000 characters)';
    }
    return null;
  }
}


