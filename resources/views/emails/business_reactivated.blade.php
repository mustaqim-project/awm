<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Bisnis Diaktifkan Kembali</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #111827;
            border-radius: 20px;
            border: 1px solid rgba(16, 185, 129, 0.25);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .header {
            background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);
            padding: 36px 32px 28px 32px;
            text-align: center;
        }

        .header .logo {
            height: 32px;
            margin-bottom: 14px;
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
            color: #d1fae5;
            font-size: 13.5px;
            font-weight: 500;
        }

        .content {
            padding: 32px;
        }

        .greeting {
            font-size: 16px;
            color: #f8fafc;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .intro-text {
            font-size: 14px;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 24px;
        }

        .success-box {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-left: 4px solid #10b981;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 26px;
        }

        .success-box h3 {
            margin: 0 0 6px 0;
            color: #34d399;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .success-box p {
            margin: 0;
            color: #ffffff;
            font-size: 14.5px;
            line-height: 1.5;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
            background-color: #1e293b;
            border-radius: 14px;
            border: 1px solid #334155;
            overflow: hidden;
        }

        .info-table td {
            padding: 12px 18px;
            font-size: 13.5px;
            border-bottom: 1px solid #334155;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table td.label {
            color: #94a3b8;
            width: 40%;
        }

        .info-table td.val {
            color: #f8fafc;
            font-weight: 600;
            text-align: right;
        }

        .restored-list {
            background-color: #1a2234;
            border-radius: 14px;
            border: 1px solid #2d3748;
            padding: 18px 20px;
            margin-bottom: 26px;
        }

        .restored-list h4 {
            margin: 0 0 10px 0;
            color: #e2e8f0;
            font-size: 13.5px;
            font-weight: 700;
        }

        .restored-list ul {
            margin: 0;
            padding-left: 20px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.6;
        }

        .restored-list li {
            margin-bottom: 6px;
        }

        .btn-primary {
            display: block;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14.5px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
        }

        .footer {
            padding: 24px 32px;
            background-color: #0b0f19;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #1e293b;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="Cooca Logo" class="logo">
            <h1>Workspace Bisnis Diaktifkan Kembali</h1>
            <p>Operasional & Transaksi Bisnis Telah Pulih Sepenuhnya</p>
        </div>

        <div class="content">
            <div class="greeting">
                Yth. {{ $ownerName ?: 'Pemilik Bisnis' }},
            </div>

            <p class="intro-text">
                Kabar baik! Workspace bisnis Anda <strong>{{ $business->name }}</strong> telah ditinjau dan
                <strong>berhasil diaktifkan kembali</strong> oleh tim administrator platform Cooca per
                {{ now()->translatedFormat('d F Y, H:i') }} WIB.
            </p>

            <div class="success-box">
                <h3>Status: Aktif Penuh</h3>
                <p>Seluruh pembatasan akses telah dicabut. Anda dan tim dapat kembali menggunakan seluruh fitur Cooca tanpa kendala.</p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Nama Workspace</td>
                    <td class="val">{{ $business->name }}</td>
                </tr>
                <tr>
                    <td class="label">ID Bisnis</td>
                    <td class="val" style="font-family: monospace; font-size: 12px;">{{ $business->id }}</td>
                </tr>
                <tr>
                    <td class="label">Status Operasional</td>
                    <td class="val" style="color: #34d399; font-weight: 700;">Aktif (Normal)</td>
                </tr>
                <tr>
                    <td class="label">Waktu Pemulihan</td>
                    <td class="val">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
            </table>

            <div class="restored-list">
                <h4>Fitur yang Sudah Dapat Digunakan Kembali:</h4>
                <ul>
                    <li><strong>Transaksi Kasir POS:</strong> Kasir dapat kembali membuka shift, melayani pelanggan, dan menerima pembayaran.</li>
                    <li><strong>Penjualan & Penagihan:</strong> Pembuatan invoice, surat jalan, dan penerimaan pesanan kembali aktif.</li>
                    <li><strong>Toko Online / Katalog Digital:</strong> Etalase publik aktif kembali dan siap melayani pesanan pelanggan.</li>
                    <li><strong>Pencatatan Keuangan & Stok:</strong> Kelola arus kas, stok opname, dan laporan keuangan seperti biasa.</li>
                </ul>
            </div>

            <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; margin-bottom: 22px;">
                Terima kasih atas kerja sama Anda dalam menjaga kepatuhan dan integritas ekosistem bisnis bersama Cooca. Silakan klik tombol di bawah untuk langsung menuju dashboard bisnis Anda.
            </p>

            <a href="https://cooca.id/login" class="btn-primary" target="_blank" rel="noopener noreferrer">
                Masuk ke Dashboard Bisnis
            </a>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px 0;">Cooca Engine &bull; Business Operating System All-in-One untuk UMKM Indonesia</p>
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem keamanan platform Cooca. Mohon tidak membalas email ini secara langsung.</p>
        </div>
    </div>
</body>

</html>
