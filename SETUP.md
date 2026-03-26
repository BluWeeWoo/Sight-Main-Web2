# Lumi - Eye Health Management System Setup

## Overview
This is the web management system for the Lumi mobile app - a clinician and admin platform for managing patient eye health monitoring.

## Project Structure

### Views (resources/views/)
- **welcome.blade.php** - Landing page with Doctor Login and Admin Login buttons
- **auth/doctor-login.blade.php** - Doctor login page
- **auth/admin-login.blade.php** - Admin login page
- **doctor/dashboard.blade.php** - Doctor dashboard with patient monitoring, charts, and metrics
- **admin/dashboard.blade.php** - Admin dashboard for professional management and system settings

### Controllers (app/Http/Controllers/)
- **AuthController.php** - Handles login and logout for both doctors and admins
- **DoctorController.php** - Manages doctor dashboard and patient interactions
- **AdminController.php** - Manages admin dashboard, professionals, and system settings

### Middleware (app/Http/Middleware/)
- **CheckRole.php** - Role-based access control middleware for protecting doctor and admin routes

### Routes (routes/web.php)
- `/` - Welcome page
- `/auth/doctor/login` - Doctor login page
- `/auth/admin/login` - Admin login page
- `/doctor/dashboard` - Doctor dashboard (requires doctor role)
- `/admin/dashboard` - Admin dashboard (requires admin role)
- Respective API endpoints for managing professionals, patients, and settings

### Database
- Migration: `2025_03_26_000000_add_role_to_users_table.php` - Adds role, phone, clinic, location, and status columns to users table

## Default Login Credentials

### Admin Account
- **Email:** admin@lumi.com
- **Password:** password123
- **Role:** admin

### Doctor Account
- **Email:** doctor@lumi.com
- **Password:** password123
- **Role:** doctor

## Features Implemented

### Welcome Page
- Beautiful landing page with two login options
- Links to Doctor and Admin login pages

### Doctor Portal
- **Dashboard** - Overview of patient monitoring
- **Patient List** - Sidebar showing all assigned patients
- **Patient Details** - Selected patient information and metrics
- **Health Metrics** - Duration, breaks, distance tracking
- **Compliance Trends** - 7-day compliance chart
- **Screen Time Analysis** - Daily screen time and breaks chart
- **Health Score Trend** - Weekly health score progression
- **Send Health Plan** - Button to send personalized recommendations

### Admin Portal
- **Dashboard Overview** - Stats showing total professionals, active count, total patients, suspended
- **Professionals Management**
  - Searchable list of all doctors/professionals
  - View professional details (name, clinic, location, patient count, status)
  - Filter by status (All, Active, Inactive, Suspended)
  - Edit and Delete functionality
  - Add new professionals
- **Admin Settings**
  - Account settings (name, email, phone)
  - Change password
  - Security settings (2FA, Login alerts)
  - System settings
  - Manage other administrators

## Setup Instructions

### 1. Prerequisites
- PHP 8.1+
- Laravel 11+
- MySQL/MariaDB
- Composer

### 2. Database Setup
The migration has been created and executed. If you need to reset:
```bash
php artisan migrate:fresh --seed
```

### 3. Environment Configuration
Ensure your `.env` file is properly configured:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sight (or your database name)
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Running the Application
```bash
# Start the Laravel development server
php artisan serve

# The application will be available at http://localhost:8000
```

## Usage

### For Doctors
1. Navigate to http://localhost:8000
2. Click "Doctor Login"
3. Login with email: `doctor@lumi.com` and password: `password123`
4. Access patient dashboard with monitoring charts and metrics
5. Send health plans to patients/guardians

### For Admins
1. Navigate to http://localhost:8000
2. Click "Admin Login"
3. Login with email: `admin@lumi.com` and password: `password123`
4. Manage professionals, view statistics
5. Configure system settings and manage administrators

## Key Routes

| Route | Method | Description | Auth |
|-------|--------|-------------|------|
| `/` | GET | Welcome page | No |
| `/auth/doctor/login` | GET | Doctor login form | No |
| `/auth/doctor/login` | POST | Submit doctor login | No |
| `/auth/admin/login` | GET | Admin login form | No |
| `/auth/admin/login` | POST | Submit admin login | No |
| `/auth/logout` | POST | Logout | Yes |
| `/doctor/dashboard` | GET | Doctor dashboard | Doctor |
| `/admin/dashboard` | GET | Admin dashboard | Admin |
| `/admin/professionals` | GET | List professionals | Admin |
| `/admin/professionals` | POST | Add professional | Admin |
| `/admin/professionals/{id}` | PUT | Edit professional | Admin |
| `/admin/professionals/{id}` | DELETE | Delete professional | Admin |

## Styling
All views use custom CSS with:
- Responsive design
- Modern color scheme (Teal #1abc9c for doctors, Navy #2c3e50 for admins)
- Chart.js for data visualization
- Flexbox and CSS Grid layouts

## Future Enhancements
- Email notifications for health plans
- Real-time patient data synchronization with mobile app
- Advanced analytics and reporting
- PDF export of health reports
- Two-factor authentication implementation
- Patient appointment scheduling
- Video consultation integration
- Mobile app API endpoints

## Support
For more information about the Lumi mobile app integration, refer to the main application documentation.
