<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun Research Avenir</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0b0f19;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-img {
            height: 38px;
            width: auto;
            display: inline-block;
            vertical-align: middle;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0;
            margin-bottom: 18px;
            text-align: center;
            letter-spacing: -0.3px;
        }
        p {
            font-size: 15px;
            line-height: 1.65;
            color: #9ca3af;
            margin-bottom: 20px;
        }
        .user-name {
            color: #f3f4f6;
            font-weight: 600;
        }
        .btn-container {
            text-align: center;
            margin: 36px 0;
        }
        .btn {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            padding: 14px 36px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
            letter-spacing: 0.2px;
        }
        .divider {
            height: 1px;
            background-color: #1f2937;
            margin: 32px 0 24px 0;
        }
        .fallback-text {
            font-size: 13px;
            line-height: 1.5;
            color: #6b7280;
            word-break: break-all;
        }
        .fallback-link {
            color: #10b981;
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <a href="{{ config('app.url') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                    <img src="{{ url('new/logo.png') }}" alt="Avenir Research" class="logo-img">
                </a>
            </div>

            <h1>Aktivasi Akun Anda</h1>

            <p>Halo <span class="user-name">{{ $user->name }}</span>,</p>

            <p>Terima kasih telah mendaftar di <strong>Research Avenir</strong>. Untuk menyelesaikan pendaftaran dan mulai mengakses katalog riset saham serta analisis pasar kami, silakan konfirmasi alamat email Anda dengan menekan tombol di bawah ini:</p>

            <div class="btn-container">
                <a href="{{ $url }}" class="btn" target="_blank">Aktivkan Akun Saya</a>
            </div>

            <p style="font-size: 13px; color: #6b7280; text-align: center; margin-bottom: 0;">Link aktivasi ini berlaku selama 60 menit. Jika Anda tidak merasa mendaftar di platform Research Avenir, Anda dapat mengabaikan email ini.</p>

            <div class="divider"></div>

            <div class="fallback-text">
                Jika Anda mengalami kendala pada tombol di atas, salin dan tempel URL berikut ke browser Anda:<br>
                <a href="{{ $url }}" class="fallback-link" target="_blank">{{ $url }}</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Research Avenir. Platform Equity Research & Stock Intelligence. All rights reserved.
        </div>
    </div>
</body>
</html>
