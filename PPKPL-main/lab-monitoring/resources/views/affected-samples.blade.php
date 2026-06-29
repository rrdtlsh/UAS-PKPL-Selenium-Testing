<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sampel Terdampak | PPKPL Kelompok 5</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-page: #f0f2f5; --bg-card: #ffffff; --bg-sidebar: #1a2236;
            --bg-sidebar-hover: #252f45; --color-primary: #3b6cf4; --color-primary-dark: #2a54d4;
            --color-primary-light: #eef2ff; --color-green: #16a34a; --color-green-bg: #dcfce7;
            --color-green-text: #15803d; --color-yellow: #d97706; --color-yellow-bg: #fef3c7;
            --color-yellow-text: #b45309; --color-red: #dc2626; --color-red-bg: #fee2e2;
            --color-red-text: #b91c1c; --color-text: #1e293b; --color-text-muted: #64748b;
            --color-border: #e2e8f0; --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-card-hover: 0 4px 12px rgba(0,0,0,0.10); --radius-sm: 6px;
            --radius-md: 10px; --radius-lg: 14px; --font: 'Inter', sans-serif;
        }

        body { font-family: var(--font); background: var(--bg-page); color: var(--color-text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar { width: 240px; min-height: 100vh; background: var(--bg-sidebar); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; }
        .sidebar-logo { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .sidebar-logo span { font-size: 13px; font-weight: 600; color: #fff; letter-spacing: 0.02em; display: block; }
        .sidebar-logo small { font-size: 11px; color: rgba(255,255,255,0.45); font-weight: 400; }
        .sidebar-nav { padding: 14px 12px; flex: 1; }
        .nav-label { font-size: 10px; font-weight: 600; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); text-transform: uppercase; padding: 8px 8px 6px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: var(--radius-sm); color: rgba(255,255,255,0.65); font-size: 13.5px; font-weight: 500; cursor: pointer; transition: background 0.15s, color 0.15s; text-decoration: none; margin-bottom: 2px; }
        .nav-item:hover { background: var(--bg-sidebar-hover); color: #fff; }
        .nav-item.active { background: var(--color-primary); color: #fff; }
        .nav-icon { width: 18px; height: 18px; opacity: 0.9; flex-shrink: 0; }

        /* ── Main content ── */
        .main-wrap { margin-left: 240px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: var(--bg-card); border-bottom: 1px solid var(--color-border); padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 15px; font-weight: 600; color: var(--color-text); }
        .topbar-title span { color: var(--color-text-muted); font-weight: 400; font-size: 13px; margin-left: 6px; }
        .topbar-user { display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .user-name { font-size: 13px; font-weight: 500; color: var(--color-text); }

        .page-content { padding: 28px; flex: 1; }

        /* ── Two-column layout ── */
        .two-col { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 960px) { .two-col { grid-template-columns: 1fr; } }
        
        .card { background: var(--bg-card); border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--color-border); font-size: 14px; font-weight: 600; color: var(--color-text); display: flex; justify-content: space-between; align-items: center;}
        .card-subtitle { font-size: 12px; color: var(--color-text-muted); margin-top: 4px; font-weight: 400;}
        .card-body { padding: 22px; }

        /* Status Badges */
        .status-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 100px; text-transform: uppercase; }
        .badge-red { background: var(--color-red-bg); color: var(--color-red-text); }
        .badge-yellow { background: var(--color-yellow-bg); color: var(--color-yellow-text); }

        /* Incident List Item */
        .incident-item { padding: 14px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); margin-bottom: 12px; cursor: pointer; transition: 0.15s; background: #fafbfc; }
        .incident-item:hover { border-color: var(--color-primary); background: #fff; box-shadow: var(--shadow-card-hover); }
        .incident-item.selected { border-width: 2px; border-color: var(--color-primary); background: #fff; }
        
        .empty-state { padding: 36px 20px; text-align: center; color: var(--color-text-muted); font-size: 13px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid var(--color-border); font-size: 12px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; }
        td { padding: 14px 12px; border-bottom: 1px solid var(--color-border); font-size: 13.5px; }
        
        .btn-refresh { font-size: 12px; font-weight: 500; color: var(--color-primary); background: var(--color-primary-light); border: 1px solid #c7d7fd; border-radius: var(--radius-sm); padding: 5px 12px; cursor: pointer; transition: background 0.15s; }
        .btn-refresh:hover { background: #dce8ff; }
        .skeleton { background: linear-gradient(90deg, #e9ecef 25%, #f3f4f6 50%, #e9ecef 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: var(--radius-sm); height: 14px; margin-bottom: 8px; }
        @keyframes shimmer { to { background-position: -200% 0; } }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>Lab Monitoring</span>
            <small>PPKPL Kelompok 5 · 2026</small>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <a class="nav-item" href="/monitoring">
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
            <a class="nav-item active" href="/monitoring/affected-samples"> 
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Sampel Terdampak
            </a>
        </nav>
    </aside>

    <div class="main-wrap">
        <header class="topbar">
            <div>
                <div class="topbar-title">
                    Identifikasi Sampel Terdampak
                    <span>/ US 3.4</span>
                </div>
            </div>
            <div class="topbar-user">
                <div class="user-avatar">P</div>
                <div class="user-name">Petugas Demo</div>
            </div>
        </header>

        <main class="page-content">
            <div class="two-col">
                <!-- Kolom Kiri: Insiden List -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            Daftar Insiden
                            <div class="card-subtitle">Klik insiden untuk melihat sampel terdampak</div>
                        </div>
                        <button class="btn-refresh" onclick="fetchIncidents()">↻ Refresh</button>
                    </div>
                    <div class="card-body" id="incident-list-container">
                        <div class="skeleton" style="width:100%; height:40px;"></div>
                        <div class="skeleton" style="width:100%; height:40px;"></div>
                    </div>
                </div>

                <!-- Kolom Kanan: Affected Samples -->
                <div class="card" id="detail-card">
                    <div class="card-header">
                        Detail Sampel Terdampak
                    </div>
                    <div class="card-body" id="detail-container">
                        <div class="empty-state">
                            <span style="font-size: 32px;">👈</span><br>
                            Pilih insiden di panel sebelah kiri untuk menampilkan sampel mana saja yang perlu dievaluasi.
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function fetchIncidents() {
            const container = document.getElementById('incident-list-container');
            container.innerHTML = '<div class="skeleton" style="width:100%; height:40px;"></div><div class="skeleton" style="width:100%; height:40px;"></div>';
            
            try {
                const res = await fetch('/api/affected-samples');
                const json = await res.json();
                
                if (json.status === 'success') {
                    if (json.data.length === 0) {
                        container.innerHTML = '<div class="empty-state">Belum ada insiden kondisi yang tercatat.</div>';
                        return;
                    }

                    container.innerHTML = '';
                    json.data.forEach(inc => {
                        const dateStr = new Date(inc.occurred_at).toLocaleString('id-ID');
                        const isRed = inc.indicator_color === 'red';
                        const el = document.createElement('div');
                        el.className = 'incident-item';
                        el.id = `inc-${inc.incident_id}`;
                        el.onclick = () => loadAffectedSamples(inc.incident_id, el);
                        
                        el.innerHTML = `
                            <div style="font-size: 13px; font-weight: 600;">${inc.storage_room ?? 'Ruangan ' + inc.storage_room_id}</div>
                            <div style="font-size: 11.5px; color: var(--color-text-muted); margin-top: 4px;">${dateStr}</div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                <span class="status-badge badge-${inc.indicator_color}">${isRed ? 'KRITIS' : 'PERHATIAN'}</span>
                                <span style="font-size: 11px; font-weight: 600; color: var(--color-text-muted);">
                                    🔬 ${inc.affected_samples_count} Sampel
                                </span>
                            </div>
                        `;
                        container.appendChild(el);
                    });
                }
            } catch (err) {
                container.innerHTML = '<div class="empty-state">Gagal memuat insiden.</div>';
                console.error(err);
            }
        }

        async function loadAffectedSamples(incidentId, clickedEl) {
            // Highlight list item
            document.querySelectorAll('.incident-item').forEach(el => el.classList.remove('selected'));
            if(clickedEl) clickedEl.classList.add('selected');

            const container = document.getElementById('detail-container');
            container.innerHTML = '<div class="skeleton" style="width:80%"></div><div class="skeleton" style="width:100%"></div>';

            try {
                const res = await fetch(`/api/affected-samples/${incidentId}`);
                const json = await res.json();

                if (json.status === 'success') {
                    const inc = json.incident;
                    const samples = json.affected_samples;

                    let html = `
                        <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid var(--color-${inc.indicator_color});">
                            <h4 style="font-size: 14px; margin-bottom: 8px;">Konteks Insiden (${inc.storage_room})</h4>
                            <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5;">
                                Insiden ini terjadi akibat penyimpangan kondisi ruangan dengan rincian 
                                Suhu <strong>${inc.temperature}°C</strong> dan Kelembaban <strong>${inc.humidity}%</strong> 
                                pada <strong>${new Date(inc.occurred_at).toLocaleString('id-ID')}</strong>. 
                                Total sampel yang berpotensi terdampak adalah ${json.total_affected} sampel.
                            </p>
                        </div>
                    `;

                    if (samples.length === 0) {
                        html += '<div class="empty-state">Beruntung, tidak ada sampel yang berada di ruangan pada saat insiden terjadi.</div>';
                    } else {
                        html += `
                            <table>
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Sampel</th>
                                        <th>Tgl Masuk</th>
                                        <th>Status Terdampak</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        samples.forEach(s => {
                            const inDate = new Date(s.stored_at).toLocaleDateString('id-ID');
                            const statusLabel = s.status === 'active' 
                                ? '<span style="color:#d97706; font-weight:600;">Harus Dievaluasi</span>'
                                : '<span style="color:#64748b; font-weight:600;">Sudah Ditarik</span>';
                            
                            html += `
                                <tr>
                                    <td style="font-weight: 600; color: var(--color-primary);">${s.sample_code}</td>
                                    <td>${s.sample_name}</td>
                                    <td>${inDate}</td>
                                    <td>${statusLabel}</td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table>`;
                    }

                    container.innerHTML = html;
                }
            } catch (err) {
                container.innerHTML = '<div class="empty-state text-red">Terjadi kesalahan memuat detail sampel.</div>';
                console.error(err);
            }
        }

        // Init
        document.addEventListener('DOMContentLoaded', fetchIncidents);
    </script>
</body>
</html>
