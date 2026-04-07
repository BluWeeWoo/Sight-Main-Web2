<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Account Created</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="width:620px; max-width:94%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#4b6059; padding:24px 28px; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px; line-height:1.2;">Your Professional Account is Ready</h1>
                            <p style="margin:8px 0 0; font-size:14px; opacity:0.9;">SIGHT Eye Health Management</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px; font-size:15px;">Hello {{ $name }},</p>
                            <p style="margin:0 0 16px; font-size:15px; color:#374151;">
                                Your clinician account has been created.
                                Use the temporary password below for your first login.
                            </p>

                            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin:0 0 18px;">
                                <p style="margin:0 0 8px; font-size:13px; color:#4b5563;">Email</p>
                                <p style="margin:0 0 12px; font-size:14px; color:#111827; font-weight:700;">{{ $email }}</p>
                                <p style="margin:0 0 8px; font-size:13px; color:#4b5563;">Temporary Password</p>
                                <p style="margin:0; font-size:14px; color:#111827; font-weight:700; font-family:monospace;">{{ $tempPassword }}</p>
                            </div>

                            <p style="margin:0 0 18px; font-size:14px; color:#4b5563;">
                                On your first successful login, you will be required to set a new password before accessing your dashboard.
                            </p>

                            <p style="text-align:center; margin:0;">
                                <a href="{{ $loginUrl }}" style="display:inline-block; background:#4b6059; color:#ffffff; text-decoration:none; font-weight:700; font-size:14px; padding:12px 22px; border-radius:8px;">
                                    Go to Login
                                </a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px; border-top:1px solid #e5e7eb; font-size:12px; color:#6b7280;">
                            SIGHT Eye Health Management
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
