<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ config('app.name', 'App') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #f4f4f5;
            --surface:     #ffffff;
            --border:      #e4e4e7;
            --border-focus:#3b82f6;
            --text:        #18181b;
            --muted:       #71717a;
            --accent:      #3b82f6;
            --accent-hover:#2563eb;
            --accent-text: #ffffff;
            --error-bg:    #fef2f2;
            --error-border:#fca5a5;
            --error-text:  #b91c1c;
            --radius:      10px;
            --shadow:      0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg:          #09090b;
                --surface:     #18181b;
                --border:      #27272a;
                --border-focus:#3b82f6;
                --text:        #fafafa;
                --muted:       #a1a1aa;
                --error-bg:    #1c0a0a;
                --error-border:#7f1d1d;
                --error-text:  #fca5a5;
                --shadow:      0 1px 3px rgba(0,0,0,.4), 0 4px 16px rgba(0,0,0,.3);
            }
        }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            color: var(--text);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .card {
            width: 100%;
            max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px 36px;
        }

        /* Icon */
        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: color-mix(in srgb, var(--accent) 12%, transparent);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .icon-wrap svg { color: var(--accent); }

        /* Heading */
        .card-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 6px;
            color: var(--text);
        }
        .card-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 28px;
            line-height: 1.55;
        }

        /* Error list */
        .errors {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
            list-style: none;
        }
        .errors li {
            font-size: 13px;
            color: var(--error-text);
            line-height: 1.5;
        }
        .errors li + li { margin-top: 4px; }

        /* Form */
        .field { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 6px;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            -webkit-appearance: none;
        }
        input[type="email"]:focus,
        input[type="text"]:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
        }
        input::placeholder { color: var(--muted); opacity: 1; }

        /* Submit button */
        .btn {
            display: block;
            width: 100%;
            height: 40px;
            padding: 0 16px;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            color: var(--accent-text);
            background: var(--accent);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, transform .1s;
            -webkit-appearance: none;
        }
        .btn:hover  { background: var(--accent-hover); }
        .btn:active { transform: scale(.985); }
        .btn:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 24px 0;
        }

        /* Footer link */
        .footer-link {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }
        .footer-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        .footer-link a:hover { text-decoration: underline; }

        /* Success state */
        .success-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: color-mix(in srgb, #22c55e 12%, transparent);
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .success-icon svg { color: #22c55e; }

        /* OTP code input */
        .code-input {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.35em;
            font-family: 'SF Mono', 'Fira Code', 'Fira Mono', 'Roboto Mono', monospace;
            height: 56px;
        }

        /* Hint text below input */
        .hint {
            font-size: 12px;
            color: var(--muted);
            margin-top: 6px;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        @yield('content')
    </div>
</div>
</body>
</html>

