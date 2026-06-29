<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring Kondisi Lab | PPKPL Kelompok 5</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-page: #f0f2f5;
            --bg-card: #ffffff;
            --bg-sidebar: #1a2236;
            --bg-sidebar-hover: #252f45;
            --color-primary: #3b6cf4;
            --color-primary-dark: #2a54d4;
            --color-primary-light: #eef2ff;
            --color-green: #16a34a;
            --color-green-bg: #dcfce7;
            --color-green-text: #15803d;
            --color-yellow: #d97706;
            --color-yellow-bg: #fef3c7;
            --color-yellow-text: #b45309;
            --color-red: #dc2626;
            --color-red-bg: #fee2e2;
            --color-red-text: #b91c1c;
            --color-text: #1e293b;
            --color-text-muted: #64748b;
            --color-border: #e2e8f0;
            --color-input-focus: #3b6cf4;
            --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-card-hover: 0 4px 12px rgba(0,0,0,0.10);
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --font: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font);
            background: var(--bg-page);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-logo span {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.02em;
            display: block;
        }
        .sidebar-logo small {
            font-size: 11px;
            color: rgba(255,255,255,0.45);
            font-weight: 400;
        }
        .sidebar-nav { padding: 14px 12px; flex: 1; }
        .nav-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            padding: 8px 8px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,0.65);
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--bg-sidebar-hover); color: #fff; }
        .nav-item.active { background: var(--color-primary); color: #fff; }
        .nav-icon { width: 18px; height: 18px; opacity: 0.9; flex-shrink: 0; }

        /* ── Main content ── */
        .main-wrap {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top bar ── */
        .topbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--color-border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 15px; font-weight: 600; color: var(--color-text); }
        .topbar-title span { color: var(--color-text-muted); font-weight: 400; font-size: 13px; margin-left: 6px; }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--color-primary-light);
            color: var(--color-primary);
            font-size: 12px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .user-name { font-size: 13px; font-weight: 500; color: var(--color-text); }

        /* ── Page content ── */
        .page-content { padding: 28px; flex: 1; }

        /* ── Stats row ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            box-shadow: var(--shadow-card);
        }
        .stat-label { font-size: 11.5px; font-weight: 500; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .stat-value { font-size: 26px; font-weight: 700; color: var(--color-text); line-height: 1; }
        .stat-sub { font-size: 12px; color: var(--color-text-muted); margin-top: 4px; }
        .stat-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }

        /* ── Two-column layout ── */
        .two-col { display: grid; grid-template-columns: 400px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 960px) { .two-col { grid-template-columns: 1fr; } }

        /* ── Card ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .card-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title { font-size: 14px; font-weight: 600; color: var(--color-text); }
        .card-subtitle { font-size: 12px; color: var(--color-text-muted); margin-top: 2px; }
        .card-body { padding: 22px; }

        /* ── Form ── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 7px;
        }
        .form-label .req { color: var(--color-red); margin-left: 2px; }
        .form-control {
            width: 100%;
            padding: 9px 13px;
            font-size: 14px;
            font-family: var(--font);
            color: var(--color-text);
            background: #fff;
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
            appearance: none;
        }
        .form-control:hover { border-color: #b0bec5; }
        .form-control:focus { border-color: var(--color-input-focus); box-shadow: 0 0 0 3px rgba(59,108,244,0.12); }
        .form-control.is-invalid { border-color: var(--color-red); }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,0.12); }
        .invalid-feedback { font-size: 12px; color: var(--color-red); margin-top: 5px; display: none; }
        .invalid-feedback.show { display: block; }

        .input-group { position: relative; }
        .input-group .form-control { padding-right: 42px; }
        .input-suffix {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-muted);
            pointer-events: none;
        }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            padding: 11px;
            font-size: 14px;
            font-weight: 600;
            font-family: var(--font);
            color: #fff;
            background: var(--color-primary);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: var(--color-primary-dark); }
        .btn-submit:active { transform: scale(0.99); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

        /* ── Alert ── */
        .alert {
            padding: 11px 14px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            display: none;
            align-items: center;
            gap: 8px;
        }
        .alert.show { display: flex; }
        .alert-success { background: var(--color-green-bg); color: var(--color-green-text); border: 1px solid #bbf7d0; }
        .alert-danger { background: var(--color-red-bg); color: var(--color-red-text); border: 1px solid #fecaca; }

        /* ── History list ── */
        .history-list { display: flex; flex-direction: column; gap: 10px; }
        .history-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 16px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            background: #fafbfc;
            transition: box-shadow 0.15s;
        }
        .history-item:hover { box-shadow: var(--shadow-card-hover); background: #fff; }
        .indicator-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot-green { background: var(--color-green); }
        .dot-yellow { background: var(--color-yellow); }
        .dot-red { background: var(--color-red); }
        .history-info { flex: 1; min-width: 0; }
        .history-room { font-size: 13.5px; font-weight: 600; color: var(--color-text); }
        .history-meta { font-size: 11.5px; color: var(--color-text-muted); margin-top: 2px; }
        .history-values { text-align: right; flex-shrink: 0; }
        .history-readings { font-size: 13px; font-weight: 600; color: var(--color-text); }
        .status-badge {
            display: inline-block;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 3px 9px;
            border-radius: 100px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .badge-green { background: var(--color-green-bg); color: var(--color-green-text); }
        .badge-yellow { background: var(--color-yellow-bg); color: var(--color-yellow-text); }
        .badge-red { background: var(--color-red-bg); color: var(--color-red-text); }

        /* ── Empty state ── */
        .empty-state {
            padding: 36px 20px;
            text-align: center;
            color: var(--color-text-muted);
            font-size: 13px;
        }
        .empty-icon { font-size: 36px; margin-bottom: 10px; opacity: 0.4; }

        /* ── Spinner ── */
        .spinner {
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Refresh button ── */
        .btn-refresh {
            font-size: 12px;
            font-weight: 500;
            color: var(--color-primary);
            background: var(--color-primary-light);
            border: 1px solid #c7d7fd;
            border-radius: var(--radius-sm);
            padding: 5px 12px;
            cursor: pointer;
            font-family: var(--font);
            transition: background 0.15s;
        }
        .btn-refresh:hover { background: #dce8ff; }

        /* ── Skeleton loader ── */
        .skeleton {
            background: linear-gradient(90deg, #e9ecef 25%, #f3f4f6 50%, #e9ecef 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: var(--radius-sm);
            height: 14px;
            margin-bottom: 8px;
        }
        @keyframes shimmer { to { background-position: -200% 0; } }

        /* ── Modal Notifikasi ── */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none; justify-content: center; align-items: center;
            z-index: 1000;
            backdrop-filter: blur(2px);
        }
        .modal-overlay.show { display: flex; }
        .modal-content {
            background: var(--bg-card);
            width: 450px; max-width: 90%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card-hover);
            display: flex; flex-direction: column;
            max-height: 80vh;
        }
        .modal-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--color-border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-title { font-size: 15px; font-weight: 600; color: var(--color-text); }
        .btn-close {
            background: none; border: none; font-size: 22px; cursor: pointer; color: var(--color-text-muted);
            line-height: 1; padding: 0;
        }
        .btn-close:hover { color: var(--color-red); }
        .modal-body {
            padding: 0; overflow-y: auto; flex: 1;
        }
        .notif-item {
            padding: 16px 22px; border-bottom: 1px solid var(--color-border);
            transition: background 0.15s;
        }
        .notif-item:hover { background: #f8fafc; }
        .notif-item:last-child { border-bottom: none; }
        .notif-msg { font-size: 13.5px; color: var(--color-text); font-weight: 500; margin-bottom: 6px; line-height: 1.4; }
        .notif-time { font-size: 11px; color: var(--color-text-muted); display: flex; align-items: center; gap: 4px; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>Lab Monitoring</span>
            <small>PPKPL Kelompok 5 · 2026</small>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <a class="nav-item active" href="#">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Dashboard Monitoring
            </a>
            <a class="nav-item" href="#">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Notifikasi
            </a>
            <a class="nav-item" href="#">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Data Ruangan
            </a>
            <a class="nav-item" href="/monitoring/incidents"> 
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Daftar Insiden
            </a>
            <a class="nav-item" href="/monitoring/affected-samples"> 
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Sampel Terdampak
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-wrap">

        <!-- Top Bar -->
        <header class="topbar">
            <div>
                <div class="topbar-title">
                    Dashboard Pemantauan
                    <span>/ Kondisi Penyimpanan</span>
                </div>
            </div>
            <div class="topbar-user">
                <div class="user-avatar">P</div>
                <div class="user-name">Petugas Demo</div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Ruangan</div>
                    <div class="stat-value" id="stat-rooms">—</div>
                    <div class="stat-sub">ruang penyimpanan aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Normal</div>
                    <div class="stat-value" style="color: var(--color-green)" id="stat-green">—</div>
                    <div class="stat-sub"><span class="stat-dot" style="background:var(--color-green)"></span>kondisi aman</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Perhatian</div>
                    <div class="stat-value" style="color: var(--color-yellow)" id="stat-yellow">—</div>
                    <div class="stat-sub"><span class="stat-dot" style="background:var(--color-yellow)"></span>deviasi ringan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Kritis</div>
                    <div class="stat-value" style="color: var(--color-red)" id="stat-red">—</div>
                    <div class="stat-sub"><span class="stat-dot" style="background:var(--color-red)"></span>perlu tindakan</div>
                </div>
            </div>

            <!-- Two column: form + history -->
            <div class="two-col">

                <!-- Input Form Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Input Data Kondisi</div>
                            <div class="card-subtitle">US 3.1 — Input suhu & kelembaban ruangan</div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div id="form-alert" class="alert"></div>

                        <form id="monitoring-form" novalidate>
                            <input type="hidden" id="inputted_by" value="1">

                            <div class="form-group">
                                <label class="form-label" for="storage_room_id">
                                    Ruang Penyimpanan <span class="req">*</span>
                                </label>
                                <select id="storage_room_id" class="form-control" required>
                                    <option value="">— Memuat ruangan... —</option>
                                </select>
                                <div class="invalid-feedback" id="err-room">Pilih ruangan terlebih dahulu.</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="temperature">
                                    Suhu Saat Ini <span class="req">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="temperature" class="form-control"
                                        placeholder="Contoh: 25.5" step="0.1" min="-50" max="100" required>
                                    <span class="input-suffix">°C</span>
                                </div>
                                <div class="invalid-feedback" id="err-temp">Masukkan suhu yang valid (−50 hingga 100°C).</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="humidity">
                                    Kelembaban <span class="req">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="humidity" class="form-control"
                                        placeholder="Contoh: 60.0" step="0.1" min="0" max="100" required>
                                    <span class="input-suffix">%RH</span>
                                </div>
                                <div class="invalid-feedback" id="err-hum">Masukkan kelembaban yang valid (0–100%).</div>
                            </div>

                            <button type="submit" class="btn-submit" id="submit-btn">
                                <span id="submit-label">Simpan Data Kondisi</span>
                                <div class="spinner" id="submit-spinner"></div>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- History Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Riwayat Monitoring</div>
                            <div class="card-subtitle">Data terbaru dari semua ruangan</div>
                        </div>
                        <button class="btn-refresh" onclick="fetchHistory()">↻ Muat ulang</button>
                    </div>
                    <div class="card-body" style="padding: 16px;">
                        <div id="history-container">
                            <div class="skeleton" style="width:80%"></div>
                            <div class="skeleton" style="width:60%"></div>
                            <div class="skeleton" style="width:70%"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-overlay" id="notif-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title">Notifikasi Sistem</div>
                        <button class="btn-close" onclick="closeNotifModal()">&times;</button>
                    </div>
                    <div class="modal-body" id="notif-container">
                        <div style="padding: 22px;">
                            <div class="skeleton" style="width:100%"></div>
                            <div class="skeleton" style="width:80%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ── Fetch & populate storage rooms dropdown ──
        async function fetchRooms() {
            try {
                const res = await fetch('/api/storage-rooms');
                const json = await res.json();
                if (json.status === 'success') {
                    const select = document.getElementById('storage_room_id');
                    select.innerHTML = '<option value="">— Pilih Ruangan —</option>';
                    json.data.forEach(room => {
                        select.innerHTML += `<option value="${room.id}">${room.room_name} (Batas: ${room.temp_limit}°C / ${room.humidity_limit}%)</option>`;
                    });
                    document.getElementById('stat-rooms').textContent = json.data.length;
                }
            } catch (err) {
                console.error('Gagal memuat ruangan:', err);
            }
        }

        // ── Fetch & render monitoring history ──
        async function fetchHistory() {
            const container = document.getElementById('history-container');
            container.innerHTML = '<div class="skeleton" style="width:80%"></div><div class="skeleton" style="width:60%"></div><div class="skeleton" style="width:70%"></div>';
            try {
                const res = await fetch('/api/condition-data');
                const json = await res.json();
                if (json.status === 'success') {
                    updateStats(json.data);
                    if (json.data.length === 0) {
                        container.innerHTML = `<div class="empty-state"><div class="empty-icon">📋</div>Belum ada data terekam.<br>Silakan input kondisi di form kiri.</div>`;
                        return;
                    }
                    const list = document.createElement('div');
                    list.className = 'history-list';
                    json.data.forEach(item => {
                        const color = item.indicator_color;
                        const labelMap = { green: 'NORMAL', yellow: 'PERHATIAN', red: 'KRITIS' };
                        const dateStr = new Date(item.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                        const actionBtn = (color !== 'green' && item.incident_ticket)
? `<a href="/monitoring/incidents/${item.incident_ticket.id}" 
     style="text-decoration:none; font-size:11px; color:var(--color-primary); font-weight:600; margin-top:8px; display:block;">
     ➔ LIHAT TIKET
   </a>` 
: '';
                        const el = document.createElement('div');
                        el.className = 'history-item';
                        el.innerHTML = `
                            <div class="indicator-dot dot-${color}"></div>
                            <div class="history-info">
                                <div class="history-room">${item.room.room_name}</div>
                                <div class="history-meta">oleh ${item.user.name} · ${dateStr}</div>
                                ${actionBtn} </div>
                            <div class="history-values">
                                <div class="history-readings">${item.temperature}°C / ${item.humidity}%</div>
                                <span class="status-badge badge-${color}">${labelMap[color]}</span>
                            </div>
                        `;
                        list.appendChild(el);
                    });
                    container.innerHTML = '';
                    container.appendChild(list);
                }
            } catch (err) {
                container.innerHTML = `<div class="empty-state"><div class="empty-icon">⚠️</div>Gagal memuat data.<br>Pastikan server backend berjalan.</div>`;
                console.error('Gagal memuat riwayat:', err);
            }
        }

        // ── Update summary stats ──
        function updateStats(data) {
            const green = data.filter(d => d.indicator_color === 'green').length;
            const yellow = data.filter(d => d.indicator_color === 'yellow').length;
            const red = data.filter(d => d.indicator_color === 'red').length;
            document.getElementById('stat-green').textContent = green;
            document.getElementById('stat-yellow').textContent = yellow;
            document.getElementById('stat-red').textContent = red;
        }

        // ── Form validation helpers ──
        function showError(inputId, errId, show) {
            document.getElementById(inputId).classList.toggle('is-invalid', show);
            document.getElementById(errId).classList.toggle('show', show);
        }

        function validateForm() {
            let valid = true;
            const room = document.getElementById('storage_room_id').value;
            const temp = parseFloat(document.getElementById('temperature').value);
            const hum = parseFloat(document.getElementById('humidity').value);

            showError('storage_room_id', 'err-room', !room);
            if (!room) valid = false;

            const tempInvalid = isNaN(temp) || temp < -50 || temp > 100;
            showError('temperature', 'err-temp', tempInvalid);
            if (tempInvalid) valid = false;

            const humInvalid = isNaN(hum) || hum < 0 || hum > 100;
            showError('humidity', 'err-hum', humInvalid);
            if (humInvalid) valid = false;

            return valid;
        }

        // ── Alert helper ──
        function showAlert(type, message) {
            const el = document.getElementById('form-alert');
            el.className = `alert alert-${type} show`;
            el.textContent = message;
            setTimeout(() => { el.className = 'alert'; }, 5000);
        }

        // ── Handle form submit ──
        document.getElementById('monitoring-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!validateForm()) return;

            const btn = document.getElementById('submit-btn');
            const label = document.getElementById('submit-label');
            const spinner = document.getElementById('submit-spinner');
            btn.disabled = true;
            label.textContent = 'Menyimpan...';
            spinner.style.display = 'block';

            const body = {
                storage_room_id: document.getElementById('storage_room_id').value,
                inputted_by: document.getElementById('inputted_by').value,
                temperature: document.getElementById('temperature').value,
                humidity: document.getElementById('humidity').value,
            };

            try {
                const res = await fetch('/api/condition-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(body)
                });
                const data = await res.json();

                if (data.status === 'success') {
                    const colorLabel = { green: 'NORMAL ✓', yellow: 'PERHATIAN ⚠', red: 'KRITIS ✕' };
                    showAlert('success', `Data berhasil disimpan. Status: ${colorLabel[data.data.indicator_color]}`);
                    document.getElementById('monitoring-form').reset();
                    ['storage_room_id','temperature','humidity'].forEach(id => {
                        document.getElementById(id).classList.remove('is-invalid');
                    });
                    fetchHistory();
                } else if (res.status === 422 && data.errors) {
                    // Backend validation errors
                    if (data.errors.storage_room_id) {
                        showError('storage_room_id', 'err-room', true);
                        document.getElementById('err-room').textContent = data.errors.storage_room_id[0];
                    }
                    if (data.errors.temperature) {
                        showError('temperature', 'err-temp', true);
                        document.getElementById('err-temp').textContent = data.errors.temperature[0];
                    }
                    if (data.errors.humidity) {
                        showError('humidity', 'err-hum', true);
                        document.getElementById('err-hum').textContent = data.errors.humidity[0];
                    }
                    if (data.errors.inputted_by) {
                        showAlert('danger', 'Error Sistem: User tidak ditemukan (' + data.errors.inputted_by[0] + ')');
                    }
                } else {
                    showAlert('danger', 'Gagal menyimpan data: ' + (data.message || 'Terjadi kesalahan.'));
                }
            } catch (err) {
                showAlert('danger', 'Tidak dapat terhubung ke server. Coba lagi.');
                console.error('Submit error:', err);
            } finally {
                btn.disabled = false;
                label.textContent = 'Simpan Data Kondisi';
                spinner.style.display = 'none';
            }
        });

        // ── Init ──
        fetchRooms();
        fetchHistory();

        // ── FITUR NOTIFIKASI ──
        
        // 1. Pilih elemen menu notifikasi (menu ke-2 di sidebar)
        const notifMenu = document.querySelectorAll('.nav-item')[1]; 
        const notifModal = document.getElementById('notif-modal');
        const notifContainer = document.getElementById('notif-container');

        // 2. Buka modal saat menu diklik
        notifMenu.addEventListener('click', function(e) {
            e.preventDefault();
            notifModal.classList.add('show');
            fetchNotifications(); // Ambil data saat modal dibuka
        });

        // 3. Fungsi tutup modal
        function closeNotifModal() {
            notifModal.classList.remove('show');
        }

        // 4. Tutup modal jika user mengklik area abu-abu di luar kotak
        notifModal.addEventListener('click', function(e) {
            if (e.target === notifModal) closeNotifModal();
        });

        // 5. Fetch API Notifikasi
        async function fetchNotifications() {
            notifContainer.innerHTML = '<div style="padding: 22px;"><div class="skeleton" style="width:100%"></div><div class="skeleton" style="width:80%"></div></div>';
            
            try {
                const res = await fetch('/api/notifications');
                const json = await res.json();
                
                if (json.status === 'success') {
                    if (json.data.length === 0) {
                        notifContainer.innerHTML = `<div class="empty-state"><div class="empty-icon">📭</div>Belum ada notifikasi baru.</div>`;
                        return;
                    }
                    
                    notifContainer.innerHTML = '';
                    json.data.forEach(item => {
                        // Sesuaikan "item.message" dengan nama kolom teks di tabel notifications Anda.
                        // Jika Anda menggunakan kolom "data", ganti menjadi item.data
                        const pesan = item.message || item.data || "Peringatan deviasi suhu tercatat."; 
                        
                        const waktu = new Date(item.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                        
                        notifContainer.innerHTML += `
                            <div class="notif-item">
                                <div class="notif-msg">⚠️ ${pesan}</div>
                                <div class="notif-time">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ${waktu}
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (err) {
                notifContainer.innerHTML = `<div class="empty-state" style="color: var(--color-red);">Gagal memuat notifikasi. Coba lagi nanti.</div>`;
                console.error('Error fetching notifications:', err);
            }
        }
    </script>
</body>
</html>
