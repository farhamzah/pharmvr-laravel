# VR AI: Headset API Contract (Quest 3 Ready)

This document defines the interface for AI-generated guidance within the Meta Quest 3 VR environment.

## 1. Authentication & Base URL
- **Base URL**: `https://api.pharmvr.com/api/v1`
- **Auth**: Device Access Token required in header: `X-Device-Token`.

---

## 2. AI Interaction Modes

### 2.1. Request Hint
Triggered when the user requests help or remains idle for too long.

- **Endpoint**: `POST /vr/ai/hint`
- **Request Body**:
```json
{
    "session_id": 450,
    "module_slug": "sterile-gowning",
    "current_step": "gloves_donning",
    "progress_percentage": 65,
    "recent_events": [
        { "event_type": "item_pickup", "item": "sterile_gloves" }
    ]
}
```
- **Response (200 OK)**:
```json
{
    "success": true,
    "data": {
        "interaction_id": 9001,
        "mode": "hint",
        "short_text": "Ambil sarung tangan dengan memegang bagian dalam manset.",
        "display_text": "Ambil sarung tangan dengan memegang Manset Dalam.",
        "speech_text": "Ingat, ambil sarung tangan dengan hanya menyentuh bagian dalam manset untuk menjaga sterilitas.",
        "severity": "info",
        "recommended_next_action": "Pickup gloves by inner cuff"
    }
}
```

### 2.2. Request Reminder
Triggered by system timers or passive checkpoints (e.g., hygiene reminder every 5 mins).

- **Endpoint**: `POST /vr/ai/reminder`
- **Request Body**:
```json
{
    "session_id": 450,
    "topic": "hygiene"
}
```
- **Response (200 OK)**:
```json
{
    "success": true,
    "data": {
        "mode": "reminder",
        "short_text": "Jangan lupa sanitasi sarung tangan Anda.",
        "speech_text": "Sanitasi tangan secara berkala sangat penting dalam area steril.",
        "severity": "info"
    }
}
```

### 2.3. Request Feedback (Real-time Evaluation)
Triggered when the user performs a significant action or a breach occurs.

- **Endpoint**: `POST /vr/ai/feedback`
- **Request Body**:
```json
{
    "session_id": 450,
    "event_type": "sterile_breach",
    "event": {
        "item": "sterile_table",
        "touch_point": "elbow"
    }
}
```
- **Response (200 OK)**:
```json
{
    "success": true,
    "data": {
        "mode": "feedback",
        "short_text": "Siku Anda menyentuh meja steril!",
        "speech_text": "Hati-hati, siku Anda menyentuh area steril. Ini adalah pelanggaran prosedur CPOB.",
        "severity": "warning",
        "recommended_next_action": "Sanitize area and restart step"
    }
}
```

---

## 3. Avatar-Ready Payload Notes

The response is designed to drive a 3D Avatar Guide:
- **`speech_text`**: Natural, conversational language intended for Text-to-Speech (TTS).
- **`short_text`**: Concise text (max 40 chars) for floating UI bubbles above the avatar's head.
- **`severity`**: Use this to drive Avatar animations:
  - `info`: Friendly, idle gesture.
  - `warning`: Concerned posture, pointing.
  - `danger`: Urgent gesture, red UI tint.

---

## 4. Unity Integration Notes (C#)

### DTO Mapping
```csharp
[Serializable]
public class VrAiResponse {
    public string mode;
    public string short_text;
    public string speech_text;
    public string severity; // info, warning, danger
    public string recommended_next_action;
}
```

### Recommended Implementation Flow
1. **Trigger**: Headset detects an event or idle state.
2. **XHR**: Send `POST` request with JSON body.
3. **Queue**: Add `speech_text` to the Audio Manager queue.
4. **Visuals**: Display `short_text` in a world-space Canvas near the Avatar.
5. **Animation**: Play `AnimationClip` mapped to the `severity`.
