# Email & Authentication Setup Guide

## Current Status
✅ Email system is configured for **test/development mode** (logs to `storage/logs/laravel.log`)
✅ New professionals can be added with automatic invitation emails
✅ Temporary passwords are generated and sent to professionals

## Switching to Gmail (Production)

### Step 1: Enable Gmail API and Create App Password

1. Go to [https://myaccount.google.com/security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** if not already enabled
3. Create an **App Password**:
   - Go to Security settings
   - Under "App passwords", select Mail and Windows Computer
   - Google will generate a 16-character password
4. Copy this password - you'll need it for `.env`

### Step 2: Update .env

In your `.env` file, update:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_FROM_ADDRESS="noreply@sight.local"
MAIL_FROM_NAME="Sight App"
```

Replace:
- `your-gmail@gmail.com` with your actual Gmail address
- `xxxx xxxx xxxx xxxx` with the 16-character app password (without spaces when pasting)

### Step 3: Test Email Sending

Run:
```bash
php artisan tinker
Mail::raw('Test email', function($m) { $m->to('test@example.com')->subject('Test'); });
exit
```

## Current Development Mode (Test)

All emails are logged to: `storage/logs/laravel.log`

To view the password sent to a professional, check this log file. Find the last entry with the professional's email address.

## Professional Onboarding Flow

1. **Admin adds professional** → Form in Admin Dashboard
2. **System generates**:
   - Random temporary password (12 characters)
   - Professional user account in database
3. **Email sent** with:
   - Login URL
   - Temporary password
   - Account details
4. **Professional logs in** with email + temporary password
5. **Professional must change password** on first login (recommended)

## Testing the System

To test with fake/placeholder domains:

1. In Admin Dashboard → Add New Professional
2. Fill in:
   - Name: Dr. John Smith
   - Email: john.smith@clinic.example.com
   - Phone: +1234567890
   - Specialty: Ophthalmology
   - Clinic: Smith Eye Clinic
   - License: LIC-12345-2024
   - Location: New York, NY

3. Click "Add" → Professional is created
4. Check `storage/logs/laravel.log` for the temporary password

## Notes

- Temporary passwords are shown ONLY in the email (development: in logs)
- Passwords are hashed in the database using bcrypt
- Email verification is optional (currently set to null)
- To enforce email verification, add checks in authentication logic

## Troubleshooting

### Emails not sending?
1. Check MAIL_MAILER is correct in .env
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify Gmail app password is correct (no spaces, exact copy)

### "Unique constraint failed" error?
- Email already exists in database
- Admin tried to add same professional twice

### Professional can't log in?
- Check they're using the temporary password from the email
- Check email field matches exactly
- Check role is set to 'doctor' in database
