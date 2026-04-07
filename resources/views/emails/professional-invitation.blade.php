<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #527267; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .credentials { background: white; padding: 20px; border-left: 4px solid #527267; margin: 20px 0; }
        .credentials-label { color: #666; font-size: 12px; text-transform: uppercase; }
        .credentials-value { font-size: 16px; font-weight: bold; color: #333; margin: 5px 0 15px 0; font-family: monospace; }
        .button { background: #527267; color: white; text-decoration: none; padding: 12px 30px; border-radius: 4px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Sight</h1>
            <p>Professional Account Created</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $professional->display_name }}</strong>,</p>

            <p>Your professional account has been successfully created in the Sight Eye Health Management System. Your account details are below:</p>

            <div class="credentials">
                <div class="credentials-label">Email Address</div>
                <div class="credentials-value">{{ $professional->email }}</div>

                <div class="credentials-label">Temporary Password</div>
                <div class="credentials-value">{{ $tempPassword }}</div>

                <p style="font-size: 14px; color: #d9534f; margin-top: 15px;">
                    ⚠️ <strong>Important:</strong> Please change your password immediately upon first login. Your temporary password should not be shared or reused.
                </p>
            </div>

            <p><strong>Your Account Details:</strong></p>
            <ul>
                <li><strong>Specialty:</strong> {{ $professional->specialty }}</li>
                <li><strong>Clinic:</strong> {{ $professional->clinic }}</li>
                <li><strong>License Number:</strong> {{ $professional->license_number }}</li>
                <li><strong>Location:</strong> {{ $professional->location }}</li>
            </ul>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="button">Log In to Your Account</a>
            </p>

            <p>If you have any questions or need assistance, please contact the system administrator.</p>

            <p>Best regards,<br><strong>Sight Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Sight Eye Health Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
