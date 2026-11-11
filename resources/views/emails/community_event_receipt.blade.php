<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationType === 'reminder' ? 'Pengingat Event' : 'Bukti Pembayaran & Tiket Event' }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 24px; color: #1E293B; }
        .card { max-width: 600px; margin: 0 auto; background: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #E2E8F0; }
        .header { background: linear-gradient(135deg, #0F172A, #1E293B); padding: 32px 24px; text-align: center; color: #FFFFFF; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .badge-success { background: #10B981; color: #FFFFFF; }
        .badge-reminder { background: #F59E0B; color: #FFFFFF; }
        .content { padding: 32px 24px; }
        .ticket-box { background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center; }
        .ticket-code { font-family: monospace; font-size: 24px; font-weight: 800; color: #0284C7; letter-spacing: 2px; margin: 8px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
        .info-label { color: #64748B; }
        .info-value { font-weight: 600; color: #0F172A; text-align: right; }
        .btn { display: inline-block; padding: 14px 28px; background: #0284C7; color: #FFFFFF !important; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; margin-top: 20px; }
        .footer { padding: 24px; text-align: center; font-size: 12px; color: #94A3B8; background: #F8FAFC; border-top: 1px solid #E2E8F0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            @if ($notificationType === 'reminder')
                <span class="badge badge-reminder">PENGINGAT JADWAL EVENT</span>
                <h1 style="margin: 12px 0 0; font-size: 22px; font-weight: 700;">Halo, {{ $user?->name ?? 'Peserta' }}!</h1>
                <p style="margin: 6px 0 0; font-size: 13px; color: #94A3B8;">Event yang Anda ikuti akan segera dimulai. Siapkan tiket Anda!</p>
            @else
                <span class="badge badge-success">PEMBAYARAN LUNAS</span>
                <h1 style="margin: 12px 0 0; font-size: 22px; font-weight: 700;">Bukti Pembayaran &amp; Tiket Resmi</h1>
                <p style="margin: 6px 0 0; font-size: 13px; color: #94A3B8;">Terima kasih atas partisipasi Anda di platform komunitas COOCA.</p>
            @endif
        </div>

        <div class="content">
            <h2 style="font-size: 18px; color: #0F172A; margin-top: 0;">{{ $event?->title }}</h2>
            <p style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 20px;">
                {{ $event?->summary ?: 'Terima kasih telah melakukan pendaftaran. Berikut rincian resmi tiket dan bukti transaksi Anda.' }}
            </p>

            <div class="ticket-box">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 1px;">KODE TIKET DIGITAL RESMI</span>
                <div class="ticket-code">{{ $registration->ticket_code }}</div>
                <span style="font-size: 12px; color: #10B981; font-weight: 600;">&bull; Status: {{ strtoupper($registration->payment_status ?? 'PAID') }}</span>
            </div>

            <div style="margin-top: 20px;">
                <div class="info-row">
                    <span class="info-label">Nama Peserta</span>
                    <span class="info-value">{{ $user?->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $user?->email ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal &amp; Waktu</span>
                    <span class="info-value">{{ $event?->start_time?->translatedFormat('l, d F Y - H:i') ?? '-' }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lokasi / Tempat</span>
                    <span class="info-value">{{ $event?->location_name ?? 'Online / hybrid' }}</span>
                </div>
                @if ($event?->is_online && $event?->online_url)
                    <div class="info-row">
                        <span class="info-label">Tautan Online Meeting</span>
                        <span class="info-value"><a href="{{ $event->online_url }}" target="_blank" style="color: #0284C7;">Masuk Sesi Online</a></span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Total Pembayaran</span>
                    <span class="info-value" style="color: #10B981; font-size: 15px;">{{ $registration->getFormattedAmountPaid() }}</span>
                </div>
                <div class="info-row" style="border-bottom: none;">
                    <span class="info-label">Waktu Pembayaran</span>
                    <span class="info-value">{{ $registration->paid_at?->translatedFormat('d M Y, H:i') ?? 'Lunas' }} WIB</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ $ticketUrl }}" target="_blank" class="btn">
                    Buka E-Tiket Digital &amp; QR Check-in
                </a>
                <p style="font-size: 11px; color: #94A3B8; margin-top: 10px;">Tunjukkan QR Code pada tiket saat kedatangan di lokasi event.</p>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">Email ini dikirim otomatis oleh sistem <strong>COOCA Community Network</strong>.</p>
            <p style="margin: 4px 0 0;">Jika ada pertanyaan atau kendala, silakan hubungi tim Support COOCA via WhatsApp resmi.</p>
        </div>
    </div>
</body>
</html>

