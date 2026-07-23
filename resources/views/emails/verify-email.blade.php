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
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #131b2e;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
            text-decoration: none;
            display: inline-block;
        }
        .logo-badge {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0;
            margin-bottom: 16px;
            text-align: center;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            transition: all 0.2s ease;
        }
        .divider {
            height: 1px;
            background-color: #1e293b;
            margin: 28px 0;
        }
        .fallback-text {
            font-size: 13px;
            color: #64748b;
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
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <a href="{{ config('app.url') }}" class="logo-text">
                    RESEARCH<span class="logo-badge">AVENIR</span>
                </a>
            </div>

            <h1>Aktivasi Akun Anda</h1>

            <p>Halo <strong>{{ $user->name }}</strong>,</p>

            <p>Terima kasih telah mendaftar di <strong>Research Avenir</strong>. Untuk menyelesaikan pendaftaran dan mulai mengakses katalog riset saham serta analisis pasar kami, silakan konfirmasi alamat email Anda dengan menekan tombol di bawah ini:</p>

            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Aktivkan Akun Saya</a>
            </div>

            <p style="font-size: 13px; color: #64748b;">Link aktivasi ini berlaku selama 60 menit. Jika Anda tidak merasa mendaftar di platform Research Avenir, Anda dapat mengabaikan email ini.</p>

            <div class="divider"></div>

            <div class="fallback-text">
                Jika Anda mengalami kendala pada tombol di atas, salin dan tempel URL berikut ke browser Anda:<br>
                <a href="{{ $url }}" class="fallback-link">{{ $url }}</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Research Avenir. Platform Equity Research & Stock Intelligence. All rights reserved.
        </div>
    </div>
</body>
</html>
