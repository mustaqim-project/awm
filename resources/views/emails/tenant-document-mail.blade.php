<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectTitle ?? 'Dokumen Bisnis' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            margin: 0;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e5e5ea;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .header {
            padding: 24px 32px;
            border-bottom: 1px solid #f2f2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #1d1d1f;
            margin: 0;
        }
        .header-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e5f1ff;
            color: #007aff;
            display: inline-block;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
            font-size: 14px;
            color: #3a3a3c;
        }
        .content p {
            margin: 0 0 16px 0;
        }
        .content strong {
            color: #1d1d1f;
        }
        .footer {
            padding: 20px 32px;
            background: #fbfbfd;
            border-top: 1px solid #f2f2f7;
            text-align: center;
            font-size: 12px;
            color: #8e8e93;
        }
        .footer a {
            color: #007aff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="header-title">{{ $businessName ?? 'COOCA Business' }}</h1>
            @if(!empty($documentNumber))
                <span class="header-badge">{{ $documentNumber }}</span>
            @endif
        </div>

        <div class="content">
            {!! $bodyHtml !!}
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px 0;">Email ini dikirim secara resmi melalui sistem <strong>{{ $businessName ?? 'COOCA' }}</strong>.</p>
            <p style="margin: 0;">Silakan balas email ini apabila Anda memiliki pertanyaan atau konfirmasi.</p>
        </div>
    </div>
</body>
</html>
