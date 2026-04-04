# PharmAI: App-side API Contract (Flutter-Ready)

This document defines the interface for the PharmAI chat functionality on the mobile application.

## 1. Authentication & Base URL
- **Base URL**: `https://api.pharmvr.com/api/v1`
- **Auth**: Bearer Token (Laravel Sanctum) required for all endpoints.

---

## 2. Conversation Flow

### 2.1. List Conversations
Retrieve all chat sessions for the authenticated user.

- **Endpoint**: `GET /ai/conversations`
- **Response (200 OK)**:
```json
{
    "success": true,
    "message": "Conversations retrieved successfully.",
    "data": [
        {
            "id": 105,
            "title": "GMP Basics & CPOB",
            "last_message_at": "2026-03-12 04:30:00",
            "status": "active",
            "created_at": "2026-03-10 10:00:00"
        }
    ],
    "errors": null
}
```

### 2.2. Create Conversation
Initialize a new chat session.

- **Endpoint**: `POST /ai/conversations`
- **Request Body**:
```json
{
    "title": "Cleanroom Protocols Inquiry"
}
```
- **Response (201 Created)**:
```json
{
    "success": true,
    "message": "Conversation created successfully.",
    "data": {
        "id": 106,
        "title": "Cleanroom Protocols Inquiry",
        "status": "active"
    },
    "errors": null
}
```

### 2.3. Get Conversation Detail (History)
Retrieve all messages in a specific conversation.

- **Endpoint**: `GET /ai/conversations/{id}`
- **Response (200 OK)**:
```json
{
    "success": true,
    "message": "Conversation details retrieved.",
    "data": {
        "id": 106,
        "title": "Cleanroom Protocols Inquiry",
        "messages": [
            {
                "id": 1,
                "role": "user",
                "content": "Apa itu kelas kebersihan A?",
                "created_at": "..."
            },
            {
                "id": 2,
                "role": "assistant",
                "content": "Kelas A adalah area untuk kegiatan berisiko tinggi...",
                "created_at": "..."
            }
        ]
    },
    "errors": null
}
```

### 2.4. Send Message & Get Response
Send a user message and receive the AI's response in a single transaction.

- **Endpoint**: `POST /ai/conversations/{id}/messages`
- **Request Body**:
```json
{
    "message": "Bagaimana cara validasi sistem udara?"
}
```
- **Response (201 Created)**:
```json
{
    "success": true,
    "message": "AI response generated.",
    "data": {
        "id": 3,
        "role": "assistant",
        "content": "Validasi sistem udara (HVAC) melibatkan kualifikasi instalasi (IQ)...",
        "created_at": "2024-03-12 05:15:00",
        "metadata": {
            "tokens": 450,
            "latency_ms": 1200
        }
    },
    "errors": null
}
```

---

## 3. Shared Response Conventions

### Standard Wrapper
All responses follow the standard PharmVR API wrapper:
- [success](file:///e:/Flutter/pharmvrpro/backend/app/Traits/ApiResponse.php#9-28): true or false
- [message](file:///e:/Flutter/pharmvrpro/backend/app/Models/PharmaiConversation.php#28-32): human-readable status message.
- `data`: Payload (Object or Array)
- `meta`: Optional pagination or telemetry.
- `errors`: List of validation errors or null.

### Error Handling
- **401 Unauthorized**: Missing or expired token.
- **403 Forbidden**: Trying to access a conversation belonging to another user.
- **422 Unprocessable Content**: Validation failed (e.g., empty message).

---

## 4. Flutter Implementation Notes

### DTO Mapping (Dart)
```dart
class PharmaiMessage {
  final int id;
  final String role; // 'user' or 'assistant'
  final String content;
  final DateTime createdAt;

  PharmaiMessage({required this.id, required this.role, required this.content, required this.createdAt});

  factory PharmaiMessage.fromJson(Map<String, dynamic> json) => PharmaiMessage(
    id: json['id'],
    role: json['role'],
    content: json['content'],
    createdAt: DateTime.parse(json['created_at']),
  );
}
```

### UX Recommendations
1. **Streaming Placeholder**: Since AI responses can take 1-3 seconds, show a "typing..." indicator in the chat list after sending.
2. **Stateless Fallback**: For quick "Search" style AI help, use the stateless `POST /ai/chat` endpoint to avoid cluttering conversation history.
