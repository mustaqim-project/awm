<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi (OTP) COOCA</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b0f19;
            color: #e2e8f0;
            margin: 0;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #111827;
            border-radius: 20px;
            border: 1px solid rgba(0, 194, 255, 0.25);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .header {
            background: linear-gradient(135deg, #0052cc 0%, #007aff 50%, #00c2ff 100%);
            padding: 36px 32px 28px 32px;
            text-align: center;
        }

        .header .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .header p {
            margin: 6px 0 0 0;
            color: #e0f2fe;
            font-size: 13.5px;
            font-weight: 500;
        }

        .content {
            padding: 32px;
        }

        .greeting {
            font-size: 16px;
            color: #f8fafc;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .lead-text {
            font-size: 14px;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 24px;
        }

        .fallback-alert {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .fallback-alert-title {
            color: #fbbf24;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .fallback-alert-desc {
            color: #d1d5db;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
        }

        .otp-box-wrapper {
            text-align: center;
            margin: 28px 0;
        }

        .otp-box-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 10px;
        }

        .otp-box {
            display: inline-block;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #007aff;
            border-radius: 16px;
            padding: 16px 36px;
            font-family: 'SF Mono', Monaco, Consolas, 'Courier New', monospace;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 0.28em;
            color: #38bdf8;
            box-shadow: 0 4px 20px rgba(0, 122, 255, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            text-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
        }

        .otp-expiry {
            font-size: 12px;
            color: #f59e0b;
            font-weight: 600;
            margin-top: 10px;
        }

        .security-notice {
            background-color: #1e293b;
            border-left: 3px solid #00c2ff;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 24px 0 12px 0;
            font-size: 12.5px;
            color: #cbd5e1;
            line-height: 1.55;
        }

        .security-notice strong {
            color: #f8fafc;
        }

        .footer {
            background-color: #0d121f;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .footer a {
            color: #38bdf8;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <span class="badge">Keamanan Akun Resmi COOCA</span>
            <h1>Kode Verifikasi (OTP)</h1>
            <p>{{ $action ?? 'Verifikasi Keamanan Akses Akun' }}</p>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <div class="greeting">Halo {{ $recipientName }},</div>
            <p class="lead-text">
                Berikut adalah 6-digit kode OTP resmi Anda untuk melanjutkan <strong>{{ strtolower($action ?? 'verifikasi akun') }}</strong> di platform COOCA Business Operating System.
            </p>

            @if ($isFallbackFromWaba)
                <div class="fallback-alert">
                    <div>
                        <div class="fallback-alert-title">⚠️ Cadangan Pengiriman Otomatis (WABA Fallback)</div>
                        <p class="fallback-alert-desc">
                            Pengiriman via WhatsApp resmi ke nomor <strong>{{ $phone ?? 'Anda' }}</strong> sedang mengalami antrean atau kendala jaringan WABA. Demi kelancaran operasional bisnis Anda, kode verifikasi ini dialihkan langsung secara otomatis ke alamat email ini.
                        </p>
                    </div>
                </div>
            @endif

            <div class="otp-box-wrapper">
                <div class="otp-box-label">Kode Verifikasi Sekali Pakai (OTP)</div>
                <div class="otp-box">{{ $otpCode }}</div>
                <div class="otp-expiry">⏱️ Berlaku selama {{ $expiresInMinutes }} menit ke depan</div>
            </div>

            <div class="security-notice">
                <strong>🔒 Peringatan Keamanan Sangat Penting:</strong><br>
                JANGAN PERNAH membagikan kode OTP di atas kepada siapa pun, termasuk pihak yang mengatasnamakan tim bantuan atau manajemen COOCA. Staf resmi COOCA tidak pernah meminta kode OTP melalui sambungan telepon, pesan chat, atau media sosial.
            </div>

            <p style="font-size: 12px; color: #64748b; margin-top: 20px; line-height: 1.5;">
                Jika Anda tidak sedang mencoba masuk atau mendaftar di COOCA, abaikan email ini. Akun Anda tetap aman selama kode OTP ini tidak dibagikan kepada pihak lain.
            </p>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p style="margin: 0 0 6px 0;">
                Email ini dikirimkan secara otomatis oleh sistem keamanan <strong>COOCA Indonesia</strong>.<br>
                SaaS Business Operating System All-in-One untuk UMKM Indonesia.
            </p>
            <p style="margin: 0;">
                Butuh bantuan operasional? Kunjungi <a href="https://cooca.id/docs">Pusat Bantuan & Panduan COOCA</a> atau hubungi <a href="mailto:support@cooca.id">support@cooca.id</a>.
            </p>
        </div>
    </div>
</body>

</html>
