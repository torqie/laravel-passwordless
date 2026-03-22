<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Magic Login Link</title>
</head>
<body style="font-family: sans-serif; color: #1a1a1a; background: #f5f5f5; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <tr>
                        <td>
                            <h1 style="font-size: 22px; margin-bottom: 16px;">Sign in to {{ config('app.name') }}</h1>
                            <p style="font-size: 15px; line-height: 1.6; margin-bottom: 24px;">
                                Click the button below to sign in. This link is valid for
                                <strong>{{ $expiresMins }} minutes</strong> and can only be used once.
                            </p>
                            <p style="text-align: center; margin-bottom: 32px;">
                                <a href="{{ $url }}"
                                   style="display: inline-block; background: #0070f3; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-size: 15px; font-weight: 600;">
                                    Sign In
                                </a>
                            </p>
                            <p style="font-size: 13px; color: #666; margin-bottom: 8px;">
                                Or copy and paste this URL into your browser:
                            </p>
                            <p style="font-size: 12px; word-break: break-all; color: #0070f3;">
                                {{ $url }}
                            </p>
                            <hr style="border: none; border-top: 1px solid #eee; margin: 32px 0;">
                            <p style="font-size: 12px; color: #999;">
                                If you didn't request this link, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

