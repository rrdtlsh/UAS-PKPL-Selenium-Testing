<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Incident Report #{{ $incident->id }}</title>
    <style>
        body        { font-family: sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        h1          { font-size: 16px; margin-bottom: 4px; }
        h2          { font-size: 13px; margin: 18px 0 6px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .header     { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .meta       { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td    { padding: 4px 8px; vertical-align: top; }
        .meta td:first-child { font-weight: bold; width: 200px; }
        table.data  { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #333; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        table.data td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        table.data tr:nth-child(even) td { background: #f5f5f5; }
        .badge      { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; color: #fff; }
        .badge-2    { background: #dc2626; } /* Kritis / Merah */
        .badge-1    { background: #d97706; } /* Warning / Kuning */
        .badge-red  { background: #dc2626; }
        .badge-yellow { background: #d97706; }
        .badge-green { background: #155724; }
        .footer     { margin-top: 32px; text-align: right; font-size: 10px; color: #666; }
        .sign-area  { margin-top: 48px; }
        .sign-box   { display: inline-block; text-align: center; width: 200px; }
        .sign-line  { border-top: 1px solid #333; margin-top: 48px; }
    </style>
</head>
<body>

    {{-- ─── HEADER ─────────────────────────────────────────── --}}
    <div class="header">
        <h1>INCIDENT REPORT — STORAGE CONDITION DEVIATION</h1>
        <p>Sistem Pemantauan Kondisi Penyimpanan pada Uji Stabilitas Obat</p>
        <p style="color:#666;">Generated: {{ $generatedAt }}</p>
    </div>

    {{-- ─── INCIDENT SUMMARY ───────────────────────────────── --}}
    <h2>1. Incident Summary</h2>
    <table class="meta">
        <tr><td>Incident ID</td><td>: #{{ $incident->id }}</td></tr>
        <tr><td>Storage Room</td><td>: {{ $incident->storageRoom->room_name ?? 'N/A' }}</td></tr>
        <tr><td>Detection Time</td><td>: {{ $incident->created_at->format('d M Y H:i') }}</td></tr>
        <tr><td>Closed Time</td><td>: {{ strtolower($incident->status) === 'closed' || strtolower($incident->status) === 'tertutup' ? $incident->updated_at->format('d M Y H:i') : 'Ongoing' }}</td></tr>
        <tr><td>Deviation Level</td>
            <td>:
                <span class="badge badge-{{ $incident->deviation_level }}">
                    {{ $incident->deviation_label }}
                </span>
            </td>
        </tr>
        <tr><td>Status</td><td>: {{ $incident->status_label }}</td></tr>
        <tr><td>Reported By</td><td>: {{ $incident->creator->name ?? 'Sistem' }}</td></tr>
    </table>

    {{-- ─── CONDITION DATA TABLE ───────────────────────────── --}}
    <h2>2. Storage Condition Data (Deviation Period)</h2>
    @if($conditionData->isEmpty())
        <p><em>No condition data recorded within the deviation period.</em></p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Timestamp</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%RH)</th>
                    <th>Indicator</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($conditionData as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $record->created_at->format('d M Y H:i') }}</td>
                        <td>{{ number_format($record->temperature, 1) }}</td>
                        <td>{{ number_format($record->humidity, 1) }}</td>
                        <td>
                            <span class="badge badge-{{ $record->indicator_color }}">
                                {{ strtoupper($record->indicator_color) }}
                            </span>
                        </td>
                        <td>{{ optional($record->user)->name ?? 'Sistem' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ─── CORRECTIVE ACTIONS LOG ─────────────────────────── --}}
    <h2>3. Corrective Actions Log</h2>
    @if($correctiveActions->isEmpty())
        <p><em>No corrective actions recorded.</em></p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Recorded At</th>
                    <th>Action Description</th>
                    <th>Performed By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($correctiveActions as $i => $action)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $action->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $action->description }}</td>
                        <td>{{ optional($action->recorder)->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ─── QA MANAGER SIGNATURE ───────────────────────────── --}}
    <h2>4. Authorization</h2>
    <div class="sign-area">
        <div class="sign-box">
            <p>QA Manager,</p>
            <div class="sign-line"></div>
            <p><strong>{{ optional($qaManager)->name ?? 'Sistem / Admin' }}</strong></p>
            <p style="color:#666;">{{ now()->format('d F Y') }}</p>
        </div>
    </div>

    {{-- ─── FOOTER ──────────────────────────────────────────── --}}
    <div class="footer">
        <p>Document Reference: Incident_{{ $incident->id }}_{{ now()->format('Y-m-d') }}.pdf</p>
        <p>Generated by: {{ optional($qaManager)->name ?? 'System' }} — Confidential</p>
    </div>

</body>
</html>