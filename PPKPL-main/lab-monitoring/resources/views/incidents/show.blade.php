@extends('layouts.app')

@section('title', 'Detail Insiden #' . $incident->id)

@section('content')
<div class="container">

    {{-- ─── PAGE HEADER ─────────────────────────────────────── --}}
    <div class="page-header">
        <h1 class="page-title">Detail Insiden #{{ $incident->id }}</h1>
        <p class="page-subtitle">
            {{ $incident->storageRoom->room_name }} —
            {{ $incident->deviation_start->format('d M Y') }}
        </p>
    </div>

    {{-- ─── STATUS BADGE ────────────────────────────────────── --}}
    <div class="status-bar">
        <span class="badge badge-status badge-status--{{ $incident->status }}">
            {{ strtoupper($incident->status) }}
        </span>
        <span class="badge badge-deviation badge-deviation--{{ $incident->deviation_level }}">
            Deviasi: {{ strtoupper($incident->deviation_level) }}
        </span>
    </div>

    {{-- ─── ERROR ALERT (JS-injected) ──────────────────────── --}}
    <div id="export-error-alert" class="alert alert-error" style="display:none;" role="alert">
        <span id="export-error-message"></span>
    </div>

    {{-- ─── INCIDENT SUMMARY CARD ───────────────────────────── --}}
    <div class="card">
        <div class="card-header">Ringkasan Insiden</div>
        <div class="card-body">
            <table class="meta-table">
                <tr>
                    <td class="label">Judul</td>
                    <td>{{ $incident->title }}</td>
                </tr>
                <tr>
                    <td class="label">Deskripsi</td>
                    <td>{{ $incident->description }}</td>
                </tr>
                <tr>
                    <td class="label">Ruang Penyimpanan</td>
                    <td>{{ $incident->storageRoom->room_name }}</td>
                </tr>
                <tr>
                    <td class="label">Periode Deviasi</td>
                    <td>
                        {{ $incident->deviation_start->format('d M Y, H:i') }} —
                        {{ $incident->deviation_end
                            ? $incident->deviation_end->format('d M Y, H:i')
                            : 'Belum selesai' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Catatan Penutupan</td>
                    <td>{{ $incident->closing_notes ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">QA Manager</td>
                    <td>{{ optional($incident->assignedQA)->name ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ─── EXPORT PDF BUTTON ───────────────────────────────── --}}
    <div class="action-bar">

        @if ($incident->isClosed())
            {{--
                AC-1: Ticket is "closed" → Export button is ACTIVE.
                Uses a direct anchor tag to trigger the download via GET.
            --}}
            <a
                id="btn-export-pdf"
                href="{{ route('incidents.export-pdf', $incident->id) }}"
                class="btn btn-primary"
                aria-label="Ekspor laporan insiden ke PDF"
            >
                <span class="btn-icon">⬇</span>
                Ekspor PDF
            </a>
        @else
            {{--
                AC-2: Ticket is NOT "closed" → Export button is DISABLED.
                The error message below explains the restriction to the user.
            --}}
            <button
                id="btn-export-pdf"
                class="btn btn-primary btn-disabled"
                disabled
                aria-disabled="true"
                title="Laporan hanya dapat diekspor setelah tiket insiden ditutup"
            >
                <span class="btn-icon">⬇</span>
                Ekspor PDF
            </button>
            <p class="hint-text hint-text--warning">
                ⚠ Laporan hanya dapat diekspor setelah tiket insiden ditutup.
            </p>
        @endif

        <a href="{{ route('incidents.index') }}" class="btn btn-secondary">
            ← Kembali ke Daftar Insiden
        </a>
    </div>

</div>

{{-- ─── INLINE STYLES (Production: move to public/css/app.css) ─── --}}
<style>
    .container         { max-width: 900px; margin: 0 auto; padding: 24px 16px; }
    .page-header       { margin-bottom: 12px; }
    .page-title        { font-size: 22px; margin: 0 0 4px; }
    .page-subtitle     { color: #666; margin: 0; }
    .status-bar        { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .badge             { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .badge-status--closed      { background: #d4edda; color: #155724; }
    .badge-status--open        { background: #fff3cd; color: #856404; }
    .badge-status--in_progress { background: #cce5ff; color: #004085; }
    .badge-deviation--red      { background: #f8d7da; color: #721c24; }
    .badge-deviation--yellow   { background: #fff3cd; color: #856404; }
    .badge-deviation--green    { background: #d4edda; color: #155724; }
    .card              { border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
    .card-header       { background: #f8f9fa; padding: 12px 16px; font-weight: bold; border-bottom: 1px solid #dee2e6; }
    .card-body         { padding: 16px; }
    .meta-table        { width: 100%; border-collapse: collapse; }
    .meta-table td     { padding: 7px 8px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
    .meta-table .label { font-weight: bold; width: 200px; color: #495057; }
    .action-bar        { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
    .btn               { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; text-decoration: none; cursor: pointer; border: none; }
    .btn-primary       { background: #1a73e8; color: #fff; }
    .btn-primary:hover { background: #1558b0; }
    .btn-secondary     { background: #6c757d; color: #fff; }
    .btn-secondary:hover { background: #565e64; }
    .btn-disabled      { background: #adb5bd; color: #fff; cursor: not-allowed; opacity: 0.7; }
    .hint-text         { font-size: 12px; margin: 0; }
    .hint-text--warning { color: #856404; }
    .alert             { padding: 12px 16px; border-radius: 5px; margin-bottom: 16px; }
    .alert-error       { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>
@endsection