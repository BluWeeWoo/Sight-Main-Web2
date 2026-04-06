# 🚀 Complete Setup Guide - Gmail PHPMailer + MySQL Database

## Overview
This guide will help you set up email sending via Gmail and connect your Laravel application to your MySQL database.

---

## STEP 1: Create Gmail App Password (CRITICAL)

> ⚠️ **Important**: You MUST do this first before anything else will work

### Instructions:

1. **Go to Google Account Settings**
   - Visit: https://myaccount.google.com/apppasswords
   - You may need to sign in first

2. **Enable 2-Step Verification** (if not already enabled)
   - Click **Security** → **2-Step Verification** → Follow the prompts

3. **Create App Password**
   - Go back to **App passwords** (https://myaccount.google.com/apppasswords)
   - Select `Mail` from the dropdown
   - Select `Windows Computer` from the second dropdown
   - Click **Generate**
   - Google will show you a 16-character password like: `xxxx xxxx xxxx xxxx`

4. **Copy the Password**
   - Copy the entire password INCLUDING THE SPACES

### Update .env File

Open `.env` in your project and update:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=prinznoel34@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"
```

Replace `xxxx xxxx xxxx xxxx` with your 16-character app password.

---

## STEP 2: Import Your MySQL Database

### Option A: Using phpMyAdmin (RECOMMENDED - Easiest)

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin
   - Username: `root` (leave password empty)
   - Click **Login**

2. **Create Database**
   - In the left sidebar, click **New**
   - Database name: `sight`
   - Collation: `utf8mb4_general_ci`
   - Click **Create**

3. **Import SQL File**
   - Click on the `sight` database in left sidebar
   - Click **Import** tab at the top
   - Click **Choose File**
   - Select: `sight (2).sql` from your Downloads folder
   - Click **Import**
   - ✅ You should see "Import successful"

### Option B: Using Command Line

```bash
cd C:\Users\Prinz\Downloads
mysql -u root < "sight (2).sql"
```

When prompted for password, just press **Enter** (no password).

---

## STEP 3: Run Laravel Migrations

This will add any missing fields to your database:

```bash
cd c:\xampp\htdocs\Sight-Main-Web2
php artisan migrate
```

You should see:
```
✓ 2026_04_03_054329_add_missing_fields_to_user_table
✓ Other migrations...
```

---

## STEP 4: Verify Everything Works

### A. Test Database Connection

```bash
php artisan tinker
```

Then type:
```php
DB::connection()->getDatabaseName()
```

You should see output: `sight`

If it works, type `exit` to quit.

### B. Test Email Configuration

```bash
php artisan tinker
```

Then type:
```php
use App\Services\PhpMailerService;
$mailer = new PhpMailerService();
$mailer->sendEmail('your-test-email@gmail.com', 'Test', 'Test Subject', '<h1>It Works!</h1>');
```

Replace `your-test-email@gmail.com` with an email you have access to.

If you get no error, the email was sent! ✓

---

## STEP 5: Test the Complete System

1. **Start the server** (if not already running):
   ```bash
   php artisan serve
   ```

2. **Log in to Admin Dashboard**
   - URL: http://localhost:8000/auth/login
   - Email: `admin@test.com`
   - Password: `Password123`

3. **Add a Professional**
   - Click **+ Add Professionals**
   - Fill in the form:
     - Name: Dr. John Smith
     - Email: your-email@gmail.com (use YOUR email to test)
     - Phone: +1234567890
     - Specialty: Ophthalmology
     - Clinic: Test Clinic
     - License: LIC-12345-2024
     - Location: New York, NY
   - Click **Add**
   - ✅ You should receive an invitation email within 30 seconds!

---

## Troubleshooting

### ❌ "SMTP connect() failed" Error

**Problem**: Email not sending

**Solutions**:
1. Check your Gmail app password is correct
2. Make sure you created an APP PASSWORD (not using your Gmail password)
3. Check your app password has spaces or remove them: `xxxxxxxxxxxxxx` (16 chars)
4. Verify `MAIL_PASSWORD` in `.env` is quoted: `MAIL_PASSWORD="xxxx xxxx xxxx xxxx"`
5. Clear config cache: `php artisan config:clear`

### ❌ "Can't connect to MySQL server"

**Problem**: Database connection failing

**Solutions**:
1. Make sure MySQL is running in XAMPP Control Panel
2. Check database name is `sight` (case-sensitive)
3. Check `.env` has `DB_DATABASE=sight`
4. Check `DB_USERNAME=root` and `DB_PASSWORD=` (empty)
5. Run: `php artisan config:clear`

### ❌ "Database sight doesn't exist"

**Problem**: Database not imported

**Solutions**:
1. Go to phpMyAdmin
2. Click **New** and create database `sight`
3. Import the `sight (2).sql` file again

### ❌ "Table 'sight.user' doesn't exist"

**Problem**: SQL schema wasn't fully imported

**Solutions**:
1. Delete the `sight` database
2. Create it again as described in STEP 2
3. Re-import the SQL file
4. Run `php artisan migrate`

---

## Configuration Summary

| Setting | Value |
|---------|-------|
| **Database Host** | 127.0.0.1 |
| **Database Name** | sight |
| **Database User** | root |
| **Database Password** | (empty) |
| **Gmail Account** | prinznoel34@gmail.com |
| **SMTP Server** | smtp.gmail.com |
| **SMTP Port** | 587 |

---

## What's Working Now ✓

- ✅ Admin dashboard (login required)
- ✅ Add professionals form
- ✅ Automatic password generation
- ✅ Email invitations via Gmail PHPMailer
- ✅ MySQL database storage
- ✅ User authentication
- ✅ Role-based access (admin/doctor/guardian)

---

## Next Steps

1. ✅ Professionals can now log in with their email + temporary password
2. ✅ Admins can manage professionals from dashboard
3. ✅ All data is stored in your MySQL database
4. ✅ Emails are sent via your Gmail account

---

## Need Help?

Check logs for errors:
```bash
tail -f storage/logs/laravel.log
```

Or contact me with the error message!
