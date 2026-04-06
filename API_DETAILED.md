# Sight Platform API Documentation

This document provides comprehensive specification for the Sight Platform API. The API is organized into three categories: **Shared**, **Web-Only**, and **Mobile-Only**.

---

## Base URL
- **Development**: `http://localhost:8000/api`
- **Production**: `https://sight-api.example.com/api`

## Authentication
Most endpoints require authentication via **Laravel Sanctum** bearer token:
```
Authorization: Bearer {token}
```

---

## 🌐 SHARED CLOUD API
Consumed by both the Laravel Web frontend and Flutter Mobile app.

### 1. POST /api/shared/login
Authenticates Guardians and Admins via email/password. Enforces 3-Strike lockout rule.

**Request:**
```json
{
  "email": "guardian@example.com",
  "password": "securePassword123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "token": "1|abcdef123456...",
  "user": {
    "id": 1,
    "email": "guardian@example.com",
    "role": "Guardian",
    "name": "John Doe"
  }
}
```

**Error Responses:**
- `401` - Invalid credentials
- `429` - Account locked (3 failed attempts)

**Security:**
- Failed passwords increment `failed_login_attempts`
- After 3 failures, account locks for 15 minutes
- Successful login resets counter

---

### 2. POST /api/shared/logout
Revokes the active JWT/Sanctum token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### 3. GET /api/shared/child/{child_id}/limits
Fetches current tracking rules and updated_at timestamp.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "limit_id": 1,
  "child_id": 5,
  "daily_limit_minutes": 120,
  "mode": "Relaxed",
  "is_active": true,
  "harmful_distance_threshold": 30,
  "critical_distance_threshold": 10,
  "auto_enforce_breaks": true,
  "updated_at": "2026-04-02T10:30:45Z"
}
```

---

### 4. GET /api/shared/child/{child_id}/metrics
Retrieves paginated eye health metrics for UI charts.

**Query Parameters:**
- `per_page` (int, 1-100, default: 50)
- `page` (int, default: 1)
- `from_date` (date, YYYY-MM-DD)
- `to_date` (date, YYYY-MM-DD, after from_date)

**Example:**
```
GET /api/shared/child/5/metrics?per_page=25&page=1&from_date=2026-04-01&to_date=2026-04-02
```

**Response (200):**
```json
{
  "data": [
    {
      "metric_id": 101,
      "child_id": 5,
      "avg_blink_rate": 18.5,
      "avg_distance": 35.2,
      "strain_events": 3,
      "timestamp": "2026-04-02T10:15:00Z",
      "screen_time_minutes": 30
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 4,
    "total_records": 100,
    "per_page": 25
  }
}
```

---

## 💻 WEB-ONLY CLOUD API
Strictly for the Laravel frontend portals.

### Guardian Controls

#### 1. POST /api/web/guardian/register
Creates a new Guardian account.

**Request:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "securePassword123",
  "password_confirmation": "securePassword123",
  "contact_number": "08012345678"
}
```

**Response (201):**
```json
{
  "message": "Guardian registered successfully",
  "token": "1|abcdef123456...",
  "user": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

---

#### 2. POST /api/web/guardian/child/add
Creates a child profile and generates a secure 6-digit login code.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Alex Doe",
  "birthdate": "2015-06-15"
}
```

**Response (201):**
```json
{
  "message": "Child profile created successfully",
  "child": {
    "child_id": 5,
    "name": "Alex Doe",
    "birthdate": "2015-06-15",
    "login_code": "382947"
  }
}
```

**Notes:**
- Default limits set: 120 minutes/day, Relaxed mode
- Virtual pet created automatically
- Login code is 6-digit, unique, and regenerated if collision occurs

---

#### 3. PUT /api/web/guardian/child/{child_id}/limits
Updates distance thresholds and screen time limits.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "daily_limit_minutes": 90,
  "mode": "Strict",
  "harmful_distance_threshold": 25,
  "critical_distance_threshold": 8,
  "auto_enforce_breaks": true
}
```

**Response (200):**
```json
{
  "message": "Limits updated successfully",
  "updated_at": "2026-04-02T10:45:30Z"
}
```

**Notes:**
- `updated_at` is automatically set to current timestamp
- All fields optional; only provided fields are updated
- Changes immediately affect mobile app (via FCM notification)

---

#### 4. POST /api/web/guardian/doctor/link
Sends a connection request to a specific clinician.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "doctor_id": 3,
  "child_id": 5
}
```

**Response (201):**
```json
{
  "message": "Doctor link request sent",
  "link_id": 12
}
```

**States:**
- Link starts with `is_active = 0` (pending)
- Doctor must accept to activate (`is_active = 1`)

---

#### 5. DELETE /api/web/guardian/child/{child_id}
GDPR Compliance - triggers cascade delete of child's data.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Child profile deleted successfully"
}
```

**Cascades:**
- `child_profile`
- `guardian_child_link`
- `clinician_patient_link`
- `session_limits`
- `eye_health_metrics`
- `eye_health_score`
- `child_inventory`
- `virtual_pet`
- Associated `user` record

---

### Clinician Portal

#### 1. GET /api/web/doctor/{doctor_id}/requests
Lists pending guardian invites awaiting acceptance.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "requests": [
    {
      "link_id": 12,
      "child_id": 5,
      "child_name": "Alex Doe",
      "linkage_date": "2026-04-02T09:30:00Z",
      "status": "pending"
    }
  ]
}
```

---

#### 2. PUT /api/web/doctor/requests/{link_id}
Accepts or rejects a guardian link request.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "action": "accept"
}
```

**Response (200):**
- `accept`: `{"message": "Link accepted"}`
- `reject`: `{"message": "Link rejected"}`

---

#### 3. GET /api/web/doctor/{doctor_id}/patients
Lists all monitored children assigned to this doctor.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "patients": [
    {
      "link_id": 12,
      "child_id": 5,
      "name": "Alex Doe",
      "birthdate": "2015-06-15",
      "pet_state": "Healthy",
      "daily_limit": 120,
      "last_sync": "2026-04-02T10:15:00Z"
    }
  ]
}
```

---

#### 4. GET /api/web/doctor/patient/{child_id}/analytics
Fetches specialized medical data views.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "child_id": 5,
  "analytics": {
    "avg_blink_rate": 18.3,
    "avg_distance": 34.8,
    "total_strain_events": 45,
    "total_screen_time_minutes": 1850,
    "metrics_count": 62
  }
}
```

---

#### 5. GET /api/web/doctor/patient/{child_id}/report
Generates a consolidated JSON/PDF/CSV export.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "report": {
    "child_id": 5,
    "name": "Alex Doe",
    "birthdate": "2015-06-15",
    "generated_at": "2026-04-02T10:45:30Z",
    "metrics": [...],
    "scores": [...]
  }
}
```

**Headers:**
```
Content-Disposition: attachment; filename=report_5.json
```

---

### Admin Portal

#### 1. GET /api/web/admin/analytics
Returns system-wide active users and platform health.

**Headers:**
```
Authorization: Bearer {token}
Role: Admin
```

**Response (200):**
```json
{
  "platform_health": {
    "total_users": 156,
    "doctors": 12,
    "guardians": 45,
    "children": 98,
    "admins": 1
  }
}
```

---

#### 2. POST /api/web/admin/create-professional
Provisions a verified doctor account directly.

**Headers:**
```
Authorization: Bearer {token}
Role: Admin
```

**Request:**
```json
{
  "name": "Dr. Smith",
  "email": "dr.smith@clinic.com",
  "license_number": "MCN-2024-001",
  "password": "securePassword123"
}
```

**Response (201):**
```json
{
  "message": "Professional account created successfully",
  "token": "1|abcdef123456...",
  "doctor": {
    "doctor_id": 3,
    "user_id": 10,
    "name": "Dr. Smith",
    "email": "dr.smith@clinic.com",
    "license_number": "MCN-2024-001",
    "is_validated": true
  }
}
```

**Notes:**
- Admin-created doctors are automatically `is_validated = 1`
- No manual verification needed

---

## 📱 MOBILE-ONLY CLOUD API
Cloud endpoints for the Flutter app.

### 1. POST /api/mobile/child/login
Authenticates mobile device using 6-digit login code.

**Request:**
```json
{
  "login_code": "382947",
  "device_id": "device_uuid_here"
}
```

**Response (200):**
```json
{
  "message": "Child login successful",
  "token": "2|ghijkl789012...",
  "child": {
    "child_id": 5,
    "name": "Alex",
    "birthdate": "2015-06-15"
  }
}
```

**Notes:**
- `device_id` is optional but recommended
- One token per child per session

---

### 2. POST /api/mobile/child/{child_id}/sync/metrics
Ingests 30-minute curated SQLite logs from phone.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "metrics": [
    {
      "avg_blink_rate": 18.5,
      "avg_distance": 35.2,
      "strain_events": 3,
      "screen_time_minutes": 30,
      "timestamp": "2026-04-02 10:15:00"
    }
  ]
}
```

**Response (200):**
```json
{
  "message": "Metrics synced successfully",
  "inserted_records": 1
}
```

**Notes:**
- Batch upload supported
- `last_sync` on child_profile updated automatically
- Timestamps must be in `Y-m-d H:i:s` format

---

### 3. PUT /api/mobile/child/{child_id}/sync/pet
Backs up virtual pet progress with timestamp comparison.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "xp_points": 1250,
  "currency": 450,
  "current_streak_days": 12,
  "last_streak_date": "2026-04-02",
  "pet_state": "Healthy",
  "device_timestamp": "2026-04-02 10:15:00"
}
```

**Response (200) - Updated:**
```json
{
  "message": "Pet synced successfully",
  "synced": true
}
```

**Response (200) - Not Updated (Cloud Data Newer):**
```json
{
  "message": "Cloud data is newer, not updating",
  "synced": false,
  "pet": {
    "xp_points": 1300,
    "currency": 500,
    "current_streak_days": 13,
    "pet_state": "Healthy",
    "updated_at": "2026-04-02T11:00:00Z"
  }
}
```

**Logic:**
- Only updates if `device_timestamp` >= `cloud_updated_at`
- Prevents overriding newer cloud data

---

### 4. POST /api/mobile/child/{child_id}/sync/calibration
Uploads unique TFLite facial mesh baseline.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "calibration_baseline": "{\"landmarks\": [...], \"reference_distance\": 35.2}"
}
```

**Response (200):**
```json
{
  "message": "Calibration baseline synced successfully"
}
```

**Notes:**
- Must be valid JSON
- Persists across device changes
- Used for distance calculations

---

### 5. POST /api/mobile/device/register-token
Saves FCM token for silent push notifications when rules change.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "child_id": 5,
  "fcm_token": "firebase_cloud_messaging_token_here"
}
```

**Response (200):**
```json
{
  "message": "FCM token registered successfully"
}
```

**Use Cases:**
- Rule updates from guardian
- Session limit changes
- Doctor linkage notifications

---

### 6. POST /api/mobile/device/ping
Lightweight heartbeat to update last_active.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "child_id": 5
}
```

**Response (200):**
```json
{
  "message": "Ping received",
  "timestamp": "2026-04-02T10:45:30Z"
}
```

**Purpose:**
- Guardian dashboard sees `last_sync` = now
- Track device activity without full sync

---

### 7. GET /api/mobile/config
Returns app version requirements (no auth required).

**Response (200):**
```json
{
  "minimum_version": "1.0.0",
  "latest_version": "1.0.5",
  "force_update": false,
  "api_version": "1.0",
  "server_time": "2026-04-02T10:45:30Z"
}
```

**Use Cases:**
- Version compatibility check on app launch
- Force update mechanism
- API version mismatch detection

---

## Error Handling

All endpoints return consistent error responses:

**400 - Bad Request:**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"]
  }
}
```

**401 - Unauthorized:**
```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

**403 - Forbidden:**
```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

**404 - Not Found:**
```json
{
  "status": "error",
  "message": "Resource not found"
}
```

**422 - Validation Error:**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {...}
}
```

**429 - Rate Limited (Too Many Requests):**
```json
{
  "status": "error",
  "message": "Account temporarily locked due to failed login attempts",
  "locked_until": "2026-04-02T11:15:30Z"
}
```

---

## Rate Limiting & Security

- **Login attempts**: 3 strikes → 15-minute lockout
- **FCM tokens**: Optional but recommended for mobile
- **Metrics batch size**: Up to 100 records per request
- **Token expiration**: Configure in `.env` (`SANCTUM_EXPIRATION`)

---

## Changelog

### v1.0 (2026-04-02)
- Initial API specification
- Shared, Web-only, and Mobile-only endpoints
- 3-strike security lockout
- GDPR-compliant deletion

---

