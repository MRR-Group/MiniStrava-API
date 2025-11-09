<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px;">
<div style="max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <h2 style="color: #333;">Hello {{ $user->name ?? 'there' }}</h2>
    <p style="font-size: 16px; color: #555;">
        We received a request to reset your account password.
    </p>
    <p style="font-size: 16px; color: #555;">
        Your 6-digit reset code is:
    </p>
    <div style="font-size: 32px; font-weight: bold; background: #f0f0f0; padding: 15px 25px; text-align: center; border-radius: 8px; letter-spacing: 5px; color: #222;">
        {{ $code }}
    </div>
    <p style="font-size: 14px; color: #999; margin-top: 20px;">
        This code will expire in 60 minutes. If you didn't request a password reset, feel free to ignore this message.
    </p>
    <hr style="margin-top: 30px;">
    <p style="font-size: 12px; color: #bbb; text-align: center;">
        &copy; {{ date('Y') }} MRR Group. All rights reserved.
    </p>
</div>
</body>
</html>
