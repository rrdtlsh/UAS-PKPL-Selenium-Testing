# pemantauan_kondisi_output.md

Prompt: "Kamu adalah Senior Backend Developer yang ahli di Laravel 10 dan PHP 8. Implementasikan User Story 3.1 — Pemantauan Kondisi Penyimpanan via Dashboard Input — untuk sistem monitoring laboratorium farmasi. Buat semua file yang diperlukan sesuai arsitektur MVC + Service Layer."

• Context File: "dokumen PRD Kelompok 5 — Sistem Pemantauan Kondisi Penyimpanan (Dashboard) pada Uji Stabilitas Obat; skema database: tabel users, storage_rooms, condition_data, notifications; SKILL.md laravel-blade-vanillajs"

Skills: "laravel-blade-vanillajs — best practices Laravel 10, Blade, Vanilla JS, SQLite, MVC + Service Layer, Eloquent ORM, Form Request validation, Artisan scheduler"

Task: Generate code for the following user story: "As a Petugas Pemeliharaan Fasilitas, I want to input temperature and humidity data for each storage room via dashboard, so that storage conditions are documented in real-time and deviations are detected early."

Input: @parameter storage_room_id: int, @parameter inputted_by: int, @parameter temperature: float, @parameter humidity: float

Output: @return type_data JSON @return_type array
// @return { status: string, message: string, data: ConditionData }
// @return indicator_color: ENUM('green'|'yellow'|'red') — dihitung otomatis oleh ConditionDataService
// @return Boolean true — notifikasi terkirim jika indicator_color = 'red' ATAU jika ruangan tidak diinput > 6 jam

• Rules:
// validation — StoreConditionDataRequest
// storage_room_id  : required | exists:storage_rooms,id
// inputted_by      : required | exists:users,id
// temperature      : required | numeric | min:-50 | max:100
// humidity         : required | numeric | min:0   | max:100
// Custom error messages dalam Bahasa Indonesia
//
// indicator_color logic — ConditionDataService::evaluateIndicatorColor()
// green  = temperature <= temp_limit   AND humidity <= humidity_limit
// yellow = temperature > temp_limit hingga +10%  OR  humidity > humidity_limit hingga +10%
// red    = temperature atau humidity melebihi batas lebih dari 10%
//
// scheduler logic — CheckOverdueMonitoringJob::handle()
// Jalankan setiap jam via Artisan scheduler (->hourly())
// Jika storage_room tidak memiliki condition_data dalam 6 jam terakhir pada jam operasional,
// kirim notifikasi ke petugas (role: 'petugas') dan Manajer Laboratorium (role: 'admin')
// Simpan log notifikasi ke tabel notifications { user_id, message, is_read: false }

• What Changed:
// [NEW] app/Models/ConditionData.php              — Eloquent Model, $fillable, belongsTo StorageRoom & User
// [NEW] app/Models/StorageRoom.php                — Eloquent Model, $fillable, hasMany ConditionData
// [NEW] app/Services/ConditionDataService.php     — evaluateIndicatorColor() logic
// [NEW] app/Jobs/CheckOverdueMonitoringJob.php    — scheduler job, cek 6 jam, trigger notifikasi
// [NEW] app/Http/Controllers/ConditionDataController.php — index(), store()
// [NEW] app/Http/Controllers/StorageRoomController.php   — index()
// [NEW] app/Http/Requests/StoreConditionDataRequest.php  — validasi + custom messages ID
// [NEW] database/migrations/create_storage_rooms_table.php
// [NEW] database/migrations/create_condition_data_table.php  — ENUM indicator_color, FK cascade
// [NEW] database/migrations/create_notifications_table.php   — FK users
// [NEW] database/seeders/StorageRoomSeeder.php    — 3 ruangan: Vaksin, Kimia, Umum
// [NEW] routes/api.php                            — GET & POST /api/condition-data, GET /api/storage-rooms
// [NEW] routes/console.php                        — schedule CheckOverdueMonitoringJob ->hourly()

Commit Message: "feat(US-3.1): implement storage condition monitoring dashboard with indicator color logic and 6-hour overdue notification scheduler"
