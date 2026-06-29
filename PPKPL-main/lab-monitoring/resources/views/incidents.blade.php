<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Insiden | PPKPL Kelompok 5</title>
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
            --color-border: #e2e8f0; --color-input-focus: #3b6cf4;
            --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
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

        /* ── Cards & UI ── */
        .two-col { display: grid; grid-template-columns: 400px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 960px) { .two-col { grid-template-columns: 1fr; } }
        .card { background: var(--bg-card); border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--color-border); font-size: 14px; font-weight: 600; color: var(--color-text); }
        .card-body { padding: 22px; }

        /* ── Status & Forms ── */
        .status-badge { display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 100px; text-transform: uppercase; }
        .badge-open { background: var(--color-red-bg); color: var(--color-red-text); }
        .badge-dalam_penanganan { background: var(--color-yellow-bg); color: var(--color-yellow-text); }
        .badge-closed { background: var(--color-green-bg); color: var(--color-green-text); }

        .form-control { width: 100%; padding: 12px; font-size: 14px; font-family: var(--font); color: var(--color-text); border: 1.5px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: 0.15s; }
        .form-control:focus { border-color: var(--color-input-focus); box-shadow: 0 0 0 3px rgba(59,108,244,0.12); }
        .btn-submit { width: 100%; padding: 12px; font-size: 14px; font-weight: 600; color: #fff; background: var(--color-primary); border: none; border-radius: var(--radius-sm); cursor: pointer; transition: 0.15s; display: flex; align-items: center; justify-content: center; gap: 8px;}
        .btn-submit:hover { background: var(--color-primary-dark); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

        /* ── Log Items (Khusus halaman insiden) ── */
        .history-item { display: flex; align-items: center; gap: 14px; padding: 15px; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: #fafbfc; transition: 0.15s; margin-bottom: 12px; }
        .history-item:hover { box-shadow: var(--shadow-card-hover); background: #fff; }
        .log-item { padding: 14px; border-left: 3px solid var(--color-primary); background: #f8fafc; border-radius: 0 var(--radius-sm) var(--radius-sm) 0; margin-bottom: 12px; }
        
        .empty-state { padding: 36px 20px; text-align: center; color: var(--color-text-muted); font-size: 13px; }
        
        /* Spinner */
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
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
            <a class="nav-item active" href="/monitoring/incidents"> 
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

    <div class="main-wrap">
        
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 15px;">
                @if(isset($ticket))
                    <a href="/monitoring/incidents" style="text-decoration: none; color: var(--color-text-muted); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                @endif

                <div class="topbar-title">
                    Manajemen Insiden 
                    <span>/ {{ isset($ticket) ? 'Penanganan Tiket #'.$ticket->id : 'Semua Tiket' }}</span>
                </div>
            </div>
            
            <div class="topbar-user">
                <div class="user-avatar">P</div>
                <div class="user-name">Petugas Demo</div>
            </div>
        </header>

        <main class="page-content">
    @if(isset($ticket))
        {{-- Area Tombol Aksi --}}
        <div style="margin-bottom: 15px; display: flex; gap: 10px;">
            
            {{-- Tombol Selesaikan: Muncul jika belum closed --}}
            @if($ticket->status !== 'closed')
                <button onclick="closeTicket({{ $ticket->id }})"
                    style="background-color: #16a34a; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    ✅ Selesaikan Insiden
                </button>
            @else
                <span style="color: #16a34a; font-weight: bold;">
                    ✔ Tiket sudah ditutup
                </span>
            @endif

            {{-- Tombol Ekspor PDF: Hanya aktif jika status closed --}}
            @if($ticket->status === 'closed')
                <a href="{{ route('incidents.export-pdf', ['id' => $ticket->id]) }}"
                   style="background-color: #2563eb; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: sans-serif;">
                    📄 Ekspor PDF
                </a>
            @else
                <button disabled 
                        style="background-color: #9ca3af; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-weight: bold; font-family: sans-serif; cursor: not-allowed;"
                        title="Laporan hanya dapat diekspor setelah tiket insiden ditutup.">
                    🔒 Ekspor PDF (Terkunci)
                </button>
            @endif
        </div>

        <div class="two-col">
            {{-- Bagian Kiri: Info Tiket & Form --}}
            <div>
                <div class="card">
                    <div class="card-header">Informasi Tiket</div>
                    <div class="card-body">
                        <p><strong>Status:</strong> 
                            <span class="status-badge badge-{{ $ticket->status }}">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Form input catatan: Muncul jika belum closed --}}
                @if($ticket->status !== 'closed')
                    <div class="card">
                        <div class="card-header">Catat Tindakan Korektif</div>
                        <div class="card-body">
                            <textarea id="desc" class="form-control" rows="5" placeholder="Jelaskan tindakan perbaikan..."></textarea>
                            <button type="button" onclick="saveAction({{ $ticket->id }})" id="btn-save" class="btn-submit" style="margin-top:14px;">
                                <span id="btn-text">Simpan Tindakan</span>
                                <div class="spinner" id="btn-spinner"></div>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Bagian Kanan: Riwayat --}}
            <div class="card">
                <div class="card-header">Riwayat Tindakan</div>
                        <div class="card-body">
                            @forelse($ticket->correctiveActions as $action)
                                <div class="log-item">
                                    <div style="font-size:13.5px; font-weight:600; color:var(--color-text);">{{ $action->recorder->name }}</div>
                                    <div style="font-size:11.5px; color:var(--color-text-muted); margin-bottom:8px;">{{ $action->created_at->format('d M Y, H:i') }}</div>
                                    <p style="font-size:13.5px; color:var(--color-text); line-height:1.5;">{{ $action->description }}</p>
                                </div>
                            @empty
                                <div class="empty-state">Belum ada riwayat tindakan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            {{-- LOGIKA 2: TAMPILAN DAFTAR (LIST SEMUA) --}}
            @else
                <div class="card">
                    <div class="card-header">Daftar Semua Insiden</div>
                    <div class="card-body" style="padding: 16px;">
                        @forelse($tickets as $t)
                            <div class="history-item">
                                <div style="flex:1">
                                    <div style="font-weight:600; font-size:14px; color:var(--color-text);">Tiket #{{ $t->id }} - {{ $t->conditionData->room->room_name ?? 'N/A' }}</div>
                                    <div style="font-size:12px; color:var(--color-text-muted); margin-top:4px;">Dilaporkan oleh: {{ $t->creator->name ?? 'System' }} · {{ $t->created_at->format('d M Y H:i') }}</div>
                                    <a href="/monitoring/incidents/{{ $t->id }}" style="display:inline-block; color:var(--color-primary); font-size:12px; font-weight:600; margin-top:8px; text-decoration:none;">➔ LIHAT DETAIL PENANGANAN</a>
                                </div>
                                <div style="text-align: right;">
                                    <span class="status-badge badge-{{ $t->status }}">{{ $t->status }}</span>
                                    <button onclick="deleteTicket({{ $t->id }})" style="background:none; border:none; color:red; cursor:pointer; font-size:16px; margin-left:10px;" title="Hapus Tiket">🗑️</button>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div style="font-size: 32px; opacity: 0.5; margin-bottom: 10px;">📋</div>
                                Tidak ada tiket insiden yang tercatat di database.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </main>
    </div>

    <script>
        // Cari fungsi saveAction di bagian <script> incidents.blade.php

async function saveAction(id) {
    const descInput = document.getElementById('desc');
    const btn = document.getElementById('btn-save');
    const btnText = document.getElementById('btn-text');
    const spinner = document.getElementById('btn-spinner');

    if (!descInput.value.trim()) {
        alert('Deskripsi wajib diisi!');
        return;
    }

    // Visual loading
    btn.disabled = true;
    btnText.innerText = 'Menyimpan...';
    spinner.style.display = 'block';

    try {
        const response = await fetch(`/api/incident-tickets/${id}/actions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                description: descInput.value // Nama properti ini harus sama dengan di Controller/Route
            })
        });

        const result = await response.json();

        if (response.ok) {
            // Berhasil tersimpan
            location.reload(); 
        } else {
            alert('Gagal: ' + (result.message || 'Terjadi kesalahan sistem'));
            resetBtn();
        }
    } catch (error) {
        console.error(error);
        alert('Gagal terhubung ke server.');
        resetBtn();
    }

    function resetBtn() {
        btn.disabled = false;
        btnText.innerText = 'Simpan Tindakan';
        spinner.style.display = 'none';
    }
}

        async function deleteTicket(id) {
            if(!confirm('Yakin ingin menghapus tiket ini?')) return;

            const res = await fetch(`/api/incident-tickets/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                }
            });

            if(res.ok) {
                alert('Tiket sudah lenyap!');
                location.reload();
            }
        }
function closeTicket(id) {
    if (!confirm('Yakin ingin menutup tiket ini?')) return;

    fetch(`/api/incident-tickets/${id}/close`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Tiket berhasil ditutup');
            location.reload(); // refresh supaya tombol PDF aktif
        } else {
            alert('Gagal menutup tiket');
        }
    })
    .catch(() => alert('Error koneksi'));
}
</script>
    </script>
</body>
</html>