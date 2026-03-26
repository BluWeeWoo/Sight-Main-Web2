# Lumi API Documentation

## Authentication Endpoints

### Doctor Login
```
POST /auth/doctor/login
Content-Type: application/x-www-form-urlencoded

email=doctor@lumi.com
password=password123

Response: Session-based authentication, redirects to /doctor/dashboard
```

### Admin Login
```
POST /auth/admin/login
Content-Type: application/x-www-form-urlencoded

email=admin@lumi.com
password=password123

Response: Session-based authentication, redirects to /admin/dashboard
```

### Logout
```
POST /auth/logout
Authorization: Session Cookie

Response: Clears session, redirects to /

Status Codes:
- 200 OK: Successfully logged out
- 302 Found: Redirect to home
```

---

## Doctor Endpoints

### Get Doctor Dashboard
```
GET /doctor/dashboard
Authorization: Doctor Role Required

Response:
{
  "doctor": {
    "id": 1,
    "name": "Dr. Martinez",
    "email": "doctor@lumi.com",
    "role": "doctor"
  },
  "patients": [
    {
      "id": "PT-20206-001",
      "name": "Emma Rodriguez",
      "avatar": "ER",
      "compliance": 75,
      "guardian": "Maria Rodriguez",
      "guardian_email": "maria@email.com"
    }
  ],
  "patientMetrics": {
    "duration": "2h 15m",
    "breaks": "12/min",
    "distance": "52cm",
    "strain_events": 3,
    "health_score": 78
  }
}
```

### Get Patient Details
```
GET /doctor/patient/{patientId}
Authorization: Doctor Role Required

Response:
{
  "id": "PT-20206-001",
  "name": "Emma Rodriguez",
  "email": "emma@email.com",
  "phone": "+234123456789",
  "age": 8,
  "diagnosis": "Myopia Management",
  "created_at": "2025-01-15"
}
```

### Get Patient Compliance Data
```
GET /doctor/patient/{patientId}/compliance
Authorization: Doctor Role Required

Response:
{
  "dates": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
  "compliance": [95, 92, 95, 92, 90, 78, 82],
  "average": 89.3
}
```

### Send Health Plan to Patient
```
POST /doctor/patient/{patientId}/health-plan
Authorization: Doctor Role Required
Content-Type: application/json

{
  "patient_id": "PT-20206-001",
  "plan": "20-20-20 rule implementation, 30 minutes daily outdoor time"
}

Response:
{
  "success": true,
  "message": "Health plan sent successfully"
}
```

### Get Patient Activity Log
```
GET /doctor/patient/{patientId}/activity
Authorization: Doctor Role Required

Response:
[
  {
    "type": "Eye Exam Completed",
    "date": "August 1, 2025",
    "icon": "eye"
  },
  {
    "type": "Health Metrics Updated",
    "date": "July 31, 2025",
    "icon": "chart"
  },
  {
    "type": "Compliance Report Sent",
    "date": "July 29, 2025",
    "icon": "clock"
  }
]
```

---

## Admin Endpoints

### Get Admin Dashboard
```
GET /admin/dashboard
Authorization: Admin Role Required

Response:
{
  "admin": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@lumi.com",
    "role": "admin"
  },
  "professionals": [
    {
      "id": 1,
      "name": "Doc McStuffins",
      "email": "doc@doctorssg.com",
      "clinic": "Martha Eye Center",
      "patients": 249,
      "status": "active",
      "last_active": "2 hours ago"
    }
  ],
  "stats": {
    "total_professionals": 5,
    "active_professionals": 3,
    "total_patients": 878,
    "suspended": 1
  }
}
```

### Get All Professionals
```
GET /admin/professionals
Authorization: Admin Role Required

Response:
[
  {
    "id": 1,
    "name": "Doc McStuffins",
    "email": "doc@doctorssg.com",
    "location": "Lagos, NG",
    "clinic": "Martha Eye Center",
    "patients": 249,
    "status": "active",
    "last_active": "2 hours ago"
  }
]
```

### Add New Professional
```
POST /admin/professionals
Authorization: Admin Role Required
Content-Type: application/json

{
  "name": "Dr. New Doctor",
  "email": "newdoctor@lumi.com",
  "phone": "+234912345678",
  "clinic": "New Clinic",
  "location": "Lagos, NG",
  "password": "password123",
  "password_confirmation": "password123"
}

Response:
{
  "success": true,
  "message": "Professional added successfully",
  "professional": {
    "id": 6,
    "name": "Dr. New Doctor",
    "email": "newdoctor@lumi.com",
    "role": "doctor",
    "status": "active"
  }
}

Status Codes:
- 200 OK: Professional created successfully
- 422 Unprocessable Entity: Validation error
```

### Edit Professional
```
PUT /admin/professionals/{id}
Authorization: Admin Role Required
Content-Type: application/json

{
  "name": "Dr. Updated Name",
  "email": "updated@lumi.com",
  "phone": "+234987654321",
  "clinic": "Updated Clinic",
  "location": "Abuja, NG",
  "status": "active"
}

Response:
{
  "success": true,
  "message": "Professional updated successfully",
  "professional": {
    "id": 1,
    "name": "Dr. Updated Name",
    "email": "updated@lumi.com",
    "role": "doctor",
    "status": "active"
  }
}
```

### Delete Professional
```
DELETE /admin/professionals/{id}
Authorization: Admin Role Required

Response:
{
  "success": true,
  "message": "Professional deleted successfully"
}

Status Codes:
- 200 OK: Professional deleted successfully
- 404 Not Found: Professional not found
```

### Update Admin Settings
```
POST /admin/settings
Authorization: Admin Role Required
Content-Type: application/json

{
  "name": "Updated Admin Name",
  "email": "newemail@lumi.com",
  "phone": "+234123456789",
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}

Response:
{
  "success": true,
  "message": "Settings updated successfully"
}
```

### Add Another Admin
```
POST /admin/admin/add
Authorization: Admin Role Required
Content-Type: application/json

{
  "name": "New Admin",
  "email": "newadmin@lumi.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Response:
{
  "success": true,
  "message": "Admin added successfully",
  "admin": {
    "id": 5,
    "name": "New Admin",
    "email": "newadmin@lumi.com",
    "role": "admin"
  }
}
```

### Remove Admin
```
DELETE /admin/admin/{id}
Authorization: Admin Role Required

Response:
{
  "success": true,
  "message": "Admin removed successfully"
}

Status Codes:
- 200 OK: Admin removed successfully
- 422 Unprocessable Entity: Cannot remove last admin
```

### Get Dashboard Statistics
```
GET /admin/stats
Authorization: Admin Role Required

Response:
{
  "total_professionals": 5,
  "active_professionals": 3,
  "total_patients": 878,
  "suspended": 1
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthorized"
}
```

### 403 Forbidden (Wrong Role)
```json
{
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "message": "Not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Status Codes Reference

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 302 | Found - Redirect |
| 401 | Unauthorized - Invalid credentials |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## Authentication Notes

- Session-based authentication currently implemented
- For mobile app integration, convert to token-based (Laravel Sanctum):
  ```bash
  php artisan install:api
  ```
- Add `@auth('sanctum')` middleware to API routes

---

## Future Enhancements

1. **Token Authentication** - For mobile app API access
2. **Webhook Support** - Real-time patient data updates
3. **Pagination** - For large data sets
4. **Filtering** - Advanced search capabilities
5. **Rate Limiting** - API protection
6. **File Upload** - Patient reports and documents
7. **Export Features** - PDF and CSV exports

---

## Testing the API

### Using cURL
```bash
# Doctor Login
curl -X POST http://localhost:8000/auth/doctor/login \
  -d "email=doctor@lumi.com&password=password123"

# Get Doctor Dashboard (after login)
curl -X GET http://localhost:8000/doctor/dashboard \
  -b "XSRF-TOKEN=your_token; laravel_session=your_session"
```

### Using Postman
1. Import the API routes
2. Set Cookie handling for sessions
3. Test each endpoint

---

**Last Updated:** March 26, 2025
**API Version:** 1.0
**Lumi System**
