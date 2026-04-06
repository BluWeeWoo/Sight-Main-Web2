# Complete Setup Guide - Gmail & MySQL Database

## Step 1: Create Gmail App Password (Required)

1. Go to [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
   - You may need to sign in and enable 2-Step Verification first
2. Select `Mail` and `Windows Computer`
3. Google will generate a **16-character password** (with spaces)
4. Copy this password exactly
5. Update your `.env` file:
   ```
   MAIL_PASSWORD=xxxx xxxx xxxx xxxx
   ```
   Replace `xxxx xxxx xxxx xxxx` with your actual app password (keep the spaces or remove them, both work)

## Step 2: Import Your MySQL Database

### Option A: Use phpMyAdmin (Recommended)
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **"New"** in the left sidebar
3. Enter database name: `sight`
4. Click **Create**
5. Select the `sight` database
6. Click **Import** tab
7. Click **"Choose File"** and select the `sight (2).sql` file you provided
8. Click **Import**

### Option B: Use Command Line
```bash
mysql -u root -p < "C:\Users\Prinz\Downloads\sight (2).sql"
```
(Leave the password empty when prompted - just press Enter)

## Step 3: Verify Database Connection

Run this command to test your database connection:
```bash
php artisan tinker
DB::connection()->getPdo();
exit
```

If no errors appear, your database is connected! ✓

## Step 4: Clear and Test Email

After setting up Gmail app password:

```bash
php artisan config:clear
```

Then try adding a professional from the Admin Dashboard to test email sending.

## Your Current Configuration

**Gmail:**
- Email: prinznoel34@gmail.com
- Password: [App Password from Step 1]
- Server: smtp.gmail.com:587

**Database:**
- Type: MySQL
- Host: localhost (127.0.0.1)
- Database: sight
- Username: root
- Password: (empty - default XAMPP)

## Troubleshooting

### "Connection refused" error?
- Make sure MySQL service is running in XAMPP
- Go to XAMPP Control Panel → Start MySQL

### "Access denied for user 'root'" error?
- Your MySQL might have a password. Email me and I'll help update the .env

### Email not sending?
1. Check your Gmail app password is correct (no typos)
2. Check MAIL_PASSWORD in .env has the spaces or is formatted correctly
3. Check logs: `storage/logs/laravel.log`
4. Make sure you created the app password (not using your Gmail password directly)

### "Database does not exist" error?
- The `sight` database wasn't imported successfully
- Try importing again via phpMyAdmin

## What's Next?

After completing these 4 steps:
1. Admin can add professionals ✓
2. Invitation emails will be sent via Gmail ✓
3. Database will store all data in your MySQL server ✓
4. Everything is ready for testing!

Need help? Check the logs with:
```bash
tail -f storage/logs/laravel.log
```
