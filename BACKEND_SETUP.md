# Backend Implementation Guide - Sight Platform

This guide walks you through setting up and deploying the Sight Platform backend API.

## 📋 Prerequisites

- PHP 8.2+
- Laravel 12.0+
- MySQL 10.4+
- Composer
- Node.js & NPM (for frontend assets)

---

## 🚀 Setup Instructions

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Environment Configuration

Create/update `.env` file:

```env
APP_NAME="Sight Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sight
DB_USERNAME=root
DB_PASSWORD=

# Sanctum Token Expiration (in minutes)
SANCTUM_EXPIRATION=1440

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@sightapp.com

# Firebase (for FCM notifications)
FIREBASE_PROJECT_ID=
FIREBASE_PRIVATE_KEY_ID=
FIREBASE_PRIVATE_KEY=
FIREBASE_CLIENT_EMAIL=
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create Database

Using MySQL CLI or phpMyAdmin:

```sql
CREATE DATABASE sight CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Migrations

This will create all necessary tables:

```bash
php artisan migrate
```

**Important:** The migrations include:
- Core Laravel tables (users, password_resets, sessions)
- Profile tables (admin, guardian, doctor, child)
- Health metrics tables (eye_health_metrics, eye_health_score)
- Session limits, prescriptions, and pet management

### 6. (Optional) Seed Database

```bash
php artisan db:seed
```

This populates sample data (create seeders in `database/seeders/`).

### 7. Generate Sanctum Encryption Keys

```bash
php artisan sanctum:install
```

---

## 🔑 Key Files Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── SharedApiController.php      # /api/shared/*
│   │       ├── GuardianApiController.php    # /api/web/guardian/*
│   │       ├── DoctorApiController.php      # /api/web/doctor/*
│   │       ├── AdminApiController.php       # /api/web/admin/*
│   │       └── MobileApiController.php      # /api/mobile/*
│   ├── Middleware/
│   │   ├── WebApiMiddleware.php             # Web-only API protection
│   │   └── RoleMiddleware.php               # Role-based access control
│   └── Resources/
│       └── ApiResponse.php                  # Standard API responses
├── Models/
│   ├── User.php
│   ├── ChildProfile.php
│   ├── GuardianProfile.php
│   ├── DoctorProfile.php
│   ├── AdminProfile.php
│   ├── SessionLimits.php
│   ├── EyeHealthMetrics.php
│   ├── EyeHealthScore.php
│   ├── VirtualPet.php
│   ├── ClinicianPatientLink.php
│   ├── Prescription.php
│   ├── ChildInventory.php
│   └── AuditLog.php
routes/
├── api.php                                  # All API routes
└── web.php                                  # Web routes
```

---

## 🎯 API Endpoints Overview

### Authentication
- `POST /api/shared/login` - Login for guardians & admins
- `POST /api/shared/logout` - Logout (requires token)

### Shared Endpoints (Web & Mobile)
- `GET /api/shared/child/{id}/limits` - Get session limits
- `GET /api/shared/child/{id}/metrics` - Get metrics with pagination

### Guardian Portal
- `POST /api/web/guardian/register` - Register new guardian
- `POST /api/web/guardian/child/add` - Add child with 6-digit code
- `PUT /api/web/guardian/child/{id}/limits` - Update limits
- `POST /api/web/guardian/doctor/link` - Request doctor access
- `DELETE /api/web/guardian/child/{id}` - Delete child (GDPR)

### Doctor Portal
- `GET /api/web/doctor/{id}/requests` - Pending guardian requests
- `PUT /api/web/doctor/requests/{id}` - Accept/reject requests
- `GET /api/web/doctor/{id}/patients` - List assigned children
- `GET /api/web/doctor/patient/{id}/analytics` - Medical analytics
- `GET /api/web/doctor/patient/{id}/report` - Export report

### Admin Portal
- `GET /api/web/admin/analytics` - System-wide stats
- `POST /api/web/admin/create-professional` - Create verified doctor

### Mobile Endpoints
- `POST /api/mobile/child/login` - Login via 6-digit code
- `POST /api/mobile/child/{id}/sync/metrics` - Sync health metrics
- `PUT /api/mobile/child/{id}/sync/pet` - Sync pet progress
- `POST /api/mobile/child/{id}/sync/calibration` - Upload calibration
- `POST /api/mobile/device/register-token` - Register FCM token
- `POST /api/mobile/device/ping` - Heartbeat update
- `GET /api/mobile/config` - Get app configuration

**Full documentation:** See [API_DETAILED.md](./API_DETAILED.md)

---

## 🔐 Security Features

### 3-Strike Lockout
Implemented in `SharedApiController::login()`
- Tracks `failed_login_attempts` on User model
- Locks account for 15 minutes after 3 failed attempts
- Automatically unlocks on successful login

### Token-Based Authentication
- Uses Laravel Sanctum for token generation
- All protected endpoints require `Authorization: Bearer {token}` header
- Tokens automatically expire based on `SANCTUM_EXPIRATION`

### Role-Based Access Control
- User roles: Admin, Guardian, Doctor, Child
- Middleware checks role before allowing access
- GDPR-compliant cascade delete for child data

---

## 🧪 Testing

### Manual Testing with Postman/Insomnia

1. **Guardian Registration**
   ```
   POST http://localhost:8000/api/web/guardian/register
   Content-Type: application/json
   
   {
     "name": "Jane Doe",
     "email": "jane@example.com",
     "password": "Password123",
     "password_confirmation": "Password123"
   }
   ```

2. **Login**
   ```
   POST http://localhost:8000/api/shared/login
   Content-Type: application/json
   
   {
     "email": "jane@example.com",
     "password": "Password123"
   }
   ```
   Copy the returned `token` and use in subsequent requests.

3. **Add Child**
   ```
   POST http://localhost:8000/api/web/guardian/child/add
   Authorization: Bearer {token}
   Content-Type: application/json
   
   {
     "name": "Alex",
     "birthdate": "2015-06-15"
   }
   ```

### Automated Testing

```bash
php artisan test
```

Tests are located in `tests/Feature/` and `tests/Unit/`

---

## 🚀 Running the Server

### Development Mode

```bash
php artisan serve
```

Runs on `http://localhost:8000` by default.

### With Hot Reload (Vite)

```bash
npm run dev
```

In another terminal:

```bash
php artisan serve
```

### Full Development Stack

```bash
npm run build:dev  # or from composer.json
```

---

## 📦 Database Migrations

To see all migrations:
```bash
php artisan migrate:status
```

To rollback last migration:
```bash
php artisan migrate:rollback
```

To rollback specific migration:
```bash
php artisan migrate:rollback --step=1
```

To migrate fresh (warning: destructive):
```bash
php artisan migrate:fresh --seed
```

---

## 🔧 Environment Variables by Environment

### Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sight-api.example.com

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_USERNAME=prod_user
DB_PASSWORD=strong_password

SANCTUM_EXPIRATION=1440  # 24 hours
```

### Staging
Similar to production but with:
```env
APP_ENV=staging
APP_DEBUG=true
```

---

## 📊 Database Schema

### Users Table
- `id` - Primary Key
- `name`, `email` - User info
- `password_hash` - Hashed password
- `role` - Enum: Admin, Guardian, Doctor, Child
- `failed_login_attempts` - For 3-strike lockout
- `locked_until` - Lockout timestamp

### Profiles
- `GuardianProfile` - Links User to Guardian
- `DoctorProfile` - Links User to Doctor + License
- `ChildProfile` - Links User to Child + Login Code
- `AdminProfile` - Links User to Admin

### Relationships
- Guardian → Many Children (via `guardian_child_link`)
- Doctor → Many Children (via `clinician_patient_link`)
- Child → Metrics, Scores, Pet, Inventory, Limits

---

## 🐛 Troubleshooting

### "SQLSTATE[HY000]: General error: 1005"
**Cause**: Foreign key constraint issues
**Solution**: Ensure all referenced tables exist and migrations run in order

### "Column not found" error
**Cause**: Migration didn't run or column name mismatch
**Solution**: 
```bash
php artisan migrate:refresh  # Fresh start
php artisan migrate           # Re-run migrations
```

### Sanctum token not working
**Cause**: Sanctum not installed or middleware not configured
**Solution**:
```bash
php artisan sanctum:install
```

### 405 Method Not Allowed
**Cause**: Route not defined or HTTP method mismatch
**Check**: `routes/api.php` for correct route definition

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Full API Documentation](./API_DETAILED.md)

---

## 📝 Next Steps

1. ✅ Database setup and migrations
2. ✅ API endpoints implementation
3. ⏳ Firebase Cloud Messaging integration (for push notifications)
4. ⏳ Email notifications (guardian/doctor alerts)
5. ⏳ Report generation (PDF/CSV export)
6. ⏳ Analytics dashboard
7. ⏳ Admin audit logging

---

