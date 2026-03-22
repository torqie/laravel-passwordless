<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Code</title>
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
                                Use the code below to sign in. It is valid for
                                <strong>{{ $expiresMins }} minutes</strong> and can only be used once.
                            </p>
                            <p style="text-align: center; margin: 32px 0;">
                                <span style="display: inline-block; font-size: 36px; font-weight: 700; letter-spacing: 0.2em; background: #f0f4ff; color: #0070f3; padding: 16px 32px; border-radius: 8px; font-family: monospace;">
                                    {{ $code }}
                                </span>
                            </p>
                            <hr style="border: none; border-top: 1px solid #eee; margin: 32px 0;">
                            <p style="font-size: 12px; color: #999;">
                                If you didn't request this code, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

