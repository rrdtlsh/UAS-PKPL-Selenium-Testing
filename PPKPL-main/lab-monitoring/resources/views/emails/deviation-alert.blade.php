<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Deviasi Kondisi</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px; }
        .container { max-width:600px; margin:auto; background:#fff; border-radius:8px;
                     overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background:#c0392b; color:#fff; padding:24px 32px; }
        .header h1 { margin:0; font-size:20px; }
        .header p  { margin:4px 0 0; font-size:13px; opacity:.85; }
        .body { padding:32px; }
        .ticket-badge { display:inline-block; background:#c0392b; color:#fff;
                        padding:4px 14px; border-radius:20px; font-size:13px;
                        font-weight:bold; margin-bottom:16px; }
        .data-table { width:100%; border-collapse:collapse; margin:20px 0; }
        .data-table th { background:#f8f8f8; text-align:left; padding:10px 14px;
                         font-size:13px; color:#555; border-bottom:2px solid #eee; }
        .data-table td { padding:10px 14px; font-size:14px; border-bottom:1px solid #eee; }
        .value-critical { color:#c0392b; font-weight:bold; }
        .limit-ok       { color:#27ae60; }
        .footer { background:#f8f8f8; padding:20px 32px; font-size:12px; color:#999;
                  border-top:1px solid #eee; }
        .action-btn { display:inline-block; margin-top:20px; padding:12px 28px;
                      background:#c0392b; color:#fff; text-decoration:none;
                      border-radius:6px; font-size:14px; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="header">
        <h1>⚠️ Peringatan Deviasi Kondisi Kritis</h1>
        <p>Sistem Pemantauan Kondisi Penyimpanan — Uji Stabilitas Obat</p>
    </div>

    {{-- ── Body ───────────────────────────────────────────────────── --}}
    <div class="body">
        <span class="ticket-badge">TIKET INSIDEN #{{ $ticket->id }}</span>

        <p>Yth. Manajer Laboratorium,</p>
        <p>
            Sistem telah mendeteksi <strong>deviasi kondisi Level 2 (Kritis)</strong>
            pada ruang penyimpanan berikut. Tiket insiden telah dibuat secara otomatis
            dan memerlukan tindakan korektif segera.
        </p>

        {{-- Tabel Informasi Ruang --}}
        <table class="data-table">
            <tr>
                <th colspan="3">📍 Informasi Ruang &amp; Waktu</th>
            </tr>
            <tr>
                <td>Ruang Penyimpanan</td>
                <td colspan="2"><strong>{{ $room->room_name }}</strong></td>
            </tr>
            <tr>
                <td>Waktu Terdeteksi</td>
                <td colspan="2">{{ $conditionData->created_at->format('d/m/Y H:i:s') }} WIB</td>
            </tr>
            <tr>
                <td>Status Tiket</td>
                <td colspan="2"><strong style="color:#c0392b;">TERBUKA (Open)</strong></td>
            </tr>
        </table>

        {{-- Tabel Data Deviasi --}}
        <table class="data-table">
            <tr>
                <th>Parameter</th>
                <th>Nilai Terukur</th>
                <th>Batas Toleransi</th>
            </tr>
            <tr>
                <td>🌡️ Suhu</td>
                <td class="value-critical">{{ number_format($conditionData->temperature, 1) }} °C</td>
                <td class="limit-ok">≤ {{ number_format($room->temp_limit, 1) }} °C</td>
            </tr>
            <tr>
                <td>💧 Kelembaban</td>
                <td class="value-critical">{{ number_format($conditionData->humidity, 1) }} %</td>
                <td class="limit-ok">≤ {{ number_format($room->humidity_limit, 1) }} %</td>
            </tr>
        </table>

        <p style="color:#c0392b; font-weight:bold;">
            ⚡ Tindakan segera diperlukan. Harap periksa kondisi ruang dan lakukan
            tindakan korektif sesuai SOP yang berlaku.
        </p>

        <p style="font-size:12px; color:#888; margin-top:24px;">
            Email ini dikirim otomatis oleh Sistem Pemantauan Kondisi Penyimpanan.
            Jangan balas email ini.
        </p>
    </div>

    {{-- ── Footer ──────────────────────────────────────────────────── --}}
    <div class="footer">
        <p>
            © {{ date('Y') }} Sistem Uji Stabilitas Obat &nbsp;|&nbsp;
            Dihasilkan pada {{ now()->format('d/m/Y H:i:s') }} WIB
        </p>
        <p>
            Dokumen ini bersifat konfidensial dan hanya ditujukan kepada
            Manajer Laboratorium yang bersangkutan.
        </p>
    </div>

</div>
</body>
</html>