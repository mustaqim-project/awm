<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Penangguhan Akun Bisnis</title>
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
            border: 1px solid rgba(239, 68, 68, 0.25);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .header {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 50%, #ef4444 100%);
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
            color: #fee2e2;
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

        .reason-box {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-left: 4px solid #ef4444;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 26px;
        }

        .reason-box h3 {
            margin: 0 0 6px 0;
            color: #f87171;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .reason-box p {
            margin: 0;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
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

        .impact-list {
            background-color: #1a2234;
            border-radius: 14px;
            border: 1px solid #2d3748;
            padding: 18px 20px;
            margin-bottom: 26px;
        }

        .impact-list h4 {
            margin: 0 0 10px 0;
            color: #e2e8f0;
            font-size: 13.5px;
            font-weight: 700;
        }

        .impact-list ul {
            margin: 0;
            padding-left: 20px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.6;
        }

        .impact-list li {
            margin-bottom: 6px;
        }

        .btn-support {
            display: block;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14.5px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
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
            <h1>Pemberitahuan Penangguhan Akun Bisnis</h1>
            <p>Operasional Transaksi Dinonaktifkan Sementara</p>
        </div>

        <div class="content">
            <div class="greeting">
                Yth. {{ $ownerName ?: 'Pemilik Bisnis' }},
            </div>

            <p class="intro-text">
                Kami informasikan bahwa akun workspace bisnis <strong>{{ $business->name }}</strong> telah
                ditangguhkan (suspended) oleh administrator platform Cooca per
                {{ $business->suspended_at ? $business->suspended_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB.
            </p>

            <div class="reason-box">
                <h3>Alasan Penangguhan:</h3>
                <p>"{{ $reason }}"</p>
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
                    <td class="val" style="color: #f87171; font-weight: 700;">Ditangguhkan (Suspended)</td>
                </tr>
                <tr>
                    <td class="label">Waktu Efektif</td>
                    <td class="val">
                        {{ $business->suspended_at ? $business->suspended_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB
                    </td>
                </tr>
            </table>

            <div class="impact-list">
                <h4>Kebijakan Selama Masa Penangguhan:</h4>
                <ul>
                    <li><strong>Transaksi Baru Dinonaktifkan:</strong> Seluruh transaksi POS kasir, pembuatan invoice penjualan, pesanan masuk, dan pencatatan pengeluaran dibekukan sementara.</li>
                    <li><strong>Toko Online / Etalase Publik Ditutup:</strong> Pelanggan tidak dapat melakukan checkout pada toko online Anda selama masa penangguhan.</li>
                    <li><strong>Data & Histori Aman:</strong> Seluruh data riwayat transaksi, inventori produk, kontak pelanggan, dan jurnal pembukuan Anda tetap tersimpan dengan aman di database.</li>
                </ul>
            </div>

            <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; margin-bottom: 22px;">
                Jika Anda telah menyelesaikan penyesuaian yang diperlukan, memiliki pertanyaan, atau ingin mengajukan permohonan pengaktifan kembali, silakan hubungi tim Customer Support kami dengan menyertakan ID Bisnis di atas.
            </p>

            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Support COOCA, saya ingin mengajukan permohonan pemulihan untuk bisnis ' . $business->name . ' (ID: ' . $business->id . '). Alasan penangguhan: ' . $reason) }}"
               class="btn-support" target="_blank" rel="noopener noreferrer">
                Hubungi Support COOCA via WhatsApp
            </a>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px 0;">Cooca Engine &bull; Business Operating System All-in-One untuk UMKM Indonesia</p>
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem keamanan platform Cooca. Mohon tidak membalas email ini secara langsung.</p>
        </div>
    </div>
</body>

</html>
