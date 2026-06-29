---
name: "US 3.4 – Identifikasi Sampel Terdampak"
description: >
  Fitur ini memungkinkan sistem untuk mengidentifikasi dan mencatat sampel uji
  yang terdampak oleh suatu insiden penyimpanan berdasarkan periode waktu deviasi
  (`deviation_start` – `deviation_end`). Setiap sampel terdampak direkam dalam
  tabel `affected_samples` dan dikaitkan langsung dengan `Incident` yang aktif.
  Logika penyaringan sepenuhnya ditangani oleh `AffectedSampleService`, menjaga
  Controller tetap tipis sesuai prinsip *thin controller*.
---

## Project Setup

### Dependensi

- **PHP** ≥ 8.1
- **Laravel** 10.x
- **Database** SQLite (file: `database/database.sqlite`)
- **DomPDF** (`barryvdh/laravel-dompdf`) — digunakan oleh `IncidentReportService`
  untuk ekspor PDF laporan insiden yang menyertakan daftar sampel terdampak

### Menjalankan Migrasi

```bash
php artisan migrate
```

Pastikan migrasi `create_affected_samples_table.php` dan `create_incidents_table.php`
sudah tersedia sebelum menjalankan perintah di atas.

### Service Provider Binding (opsional)

Jika menggunakan explicit binding, tambahkan di `AppServiceProvider::register()`:

```php
$this->app->bind(
    \App\Services\AffectedSampleService::class,
    \App\Services\AffectedSampleService::class,
);
```

Laravel secara default melakukan auto-resolution melalui container, sehingga
langkah ini hanya diperlukan apabila implementasi akan diganti dengan interface.

---

## Web Layer

### Controller: `AffectedSampleController`

**Lokasi:** `app/Http/Controllers/AffectedSampleController.php`

Controller bertanggung jawab menerima request, meneruskannya ke Service, dan
mengembalikan JSON response. Controller **tidak** mengandung logika penyaringan.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffectedSampleRequest;
use App\Models\Incident;
use App\Services\AffectedSampleService;
use Illuminate\Http\JsonResponse;

class AffectedSampleController extends Controller
{
    public function __construct(
        private readonly AffectedSampleService $sampleService,
    ) {}

    /**
     * Ambil semua sampel yang terdampak pada insiden tertentu.
     */
    public function index(int $incidentId): JsonResponse
    {
        $incident = Incident::findOrFail($incidentId);
        $samples  = $this->sampleService->getAffectedSamples($incident);

        return response()->json([
            'status' => 'success',
            'data'   => $samples,
        ]);
    }

    /**
     * Catat sampel terdampak baru pada insiden yang sedang aktif.
     */
    public function store(StoreAffectedSampleRequest $request, int $incidentId): JsonResponse
    {
        $incident = Incident::findOrFail($incidentId);
        $sample   = $this->sampleService->recordAffectedSample($incident, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Sampel terdampak berhasil dicatat.',
            'data'    => $sample,
        ], 201);
    }
}
```

### Form Request: `StoreAffectedSampleRequest`

**Lokasi:** `app/Http/Requests/StoreAffectedSampleRequest.php`

Seluruh validasi input terpusat di Form Request agar Controller dan Service
tetap bersih dari logika validasi.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffectedSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autentikasi dikendalikan oleh middleware route
    }

    public function rules(): array
    {
        return [
            'sample_code'   => 'required|string|max:100',
            'product_name'  => 'required|string|max:150',
            'batch_number'  => 'required|string|max:50',
            'quantity'      => 'required|integer|min:1',
            'notes'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sample_code.required'  => 'Kode sampel wajib diisi.',
            'sample_code.max'       => 'Kode sampel tidak boleh melebihi 100 karakter.',
            'product_name.required' => 'Nama produk wajib diisi.',
            'product_name.max'      => 'Nama produk tidak boleh melebihi 150 karakter.',
            'batch_number.required' => 'Nomor batch wajib diisi.',
            'batch_number.max'      => 'Nomor batch tidak boleh melebihi 50 karakter.',
            'quantity.required'     => 'Jumlah sampel wajib diisi.',
            'quantity.integer'      => 'Jumlah sampel harus berupa bilangan bulat.',
            'quantity.min'          => 'Jumlah sampel minimal adalah 1.',
        ];
    }
}
```

### Definisi Route

**Lokasi:** `routes/api.php`

```php
use App\Http\Controllers\AffectedSampleController;

Route::prefix('incidents/{incidentId}/samples')->group(function () {
    Route::get('/',    [AffectedSampleController::class, 'index']);
    Route::post('/',   [AffectedSampleController::class, 'store']);
});
```

---

## Service Layer

### `AffectedSampleService`

**Lokasi:** `app/Services/AffectedSampleService.php`

Service class merupakan satu-satunya tempat logika bisnis berada. Metode utama
`getAffectedSamples()` menyaring sampel berdasarkan waktu insiden dengan
memanfaatkan kolom `deviation_start` dan `deviation_end` pada model `Incident`.
Pendekatan ini konsisten dengan pola yang sudah diterapkan di
`IncidentReportService::buildConditionTable()`.

```php
<?php

namespace App\Services;

use App\Models\AffectedSample;
use App\Models\Incident;
use Illuminate\Support\Collection;

/**
 * AffectedSampleService
 *
 * Bertanggung jawab atas identifikasi dan pencatatan sampel uji yang
 * terdampak oleh suatu insiden penyimpanan berdasarkan periode waktu deviasi.
 *
 * Filter utama: sampel dicatat selama periode deviation_start – deviation_end.
 * Jika deviation_end belum terisi (insiden masih aktif), filter menggunakan
 * waktu sekarang (now()) sebagai batas akhir.
 */
class AffectedSampleService
{
    /**
     * Ambil semua sampel yang sudah direkam untuk insiden tertentu.
     *
     * Sampel disaring berdasarkan created_at yang jatuh di dalam
     * rentang [deviation_start, deviation_end] milik Incident.
     *
     * @param  Incident   $incident  Objek insiden yang sudah di-load
     * @return Collection            Koleksi AffectedSample terurut kronologis
     */
    public function getAffectedSamples(Incident $incident): Collection
    {
        $start = $incident->deviation_start;
        $end   = $incident->deviation_end ?? now();

        return AffectedSample::where('incident_id', $incident->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Catat satu entri sampel terdampak baru pada insiden yang diberikan.
     *
     * Metode ini hanya boleh dipanggil selama insiden masih berstatus
     * 'open' atau 'in_progress'. Controller wajib memvalidasi status
     * sebelum memanggil metode ini.
     *
     * @param  Incident              $incident   Insiden aktif target pencatatan
     * @param  array<string, mixed>  $validated  Data tervalidasi dari Form Request
     * @return AffectedSample                    Model yang baru disimpan
     */
    public function recordAffectedSample(Incident $incident, array $validated): AffectedSample
    {
        return AffectedSample::create([
            'incident_id'  => $incident->id,
            'sample_code'  => $validated['sample_code'],
            'product_name' => $validated['product_name'],
            'batch_number' => $validated['batch_number'],
            'quantity'     => $validated['quantity'],
            'notes'        => $validated['notes'] ?? null,
        ]);
    }
}
```

### Alur Identifikasi

```
Request masuk
    │
    ▼
AffectedSampleController::index($incidentId)
    │  memanggil
    ▼
AffectedSampleService::getAffectedSamples(Incident $incident)
    │  query filter:
    │   WHERE incident_id = ?
    │   AND   created_at BETWEEN deviation_start AND (deviation_end ?? now())
    │   ORDER BY created_at ASC
    ▼
Collection<AffectedSample>  ──►  JsonResponse
```

---

## Data Layer

### Migration: `create_affected_samples_table`

**Lokasi:** `database/migrations/create_affected_samples_table.php`

```php
Schema::create('affected_samples', function (Blueprint $table) {
    $table->id();
    $table->foreignId('incident_id')
          ->constrained('incidents')
          ->onDelete('cascade');   // hapus sampel otomatis jika insiden dihapus
    $table->string('sample_code',  100);
    $table->string('product_name', 150);
    $table->string('batch_number',  50);
    $table->integer('quantity')->default(0);
    $table->text('notes')->nullable();
    $table->timestamps();          // created_at digunakan sebagai basis filter waktu
});
```

### Migration: `create_incidents_table` *(kolom kunci)*

Kolom `deviation_start` dan `deviation_end` pada tabel `incidents` merupakan
**sumber kebenaran** untuk rentang waktu penyaringan sampel terdampak.

| Kolom             | Tipe        | Keterangan                                              |
|-------------------|-------------|----------------------------------------------------------|
| `deviation_start` | `timestamp` | Wajib. Titik awal deviasi kondisi ruang terdeteksi.     |
| `deviation_end`   | `timestamp` | `nullable`. Kosong jika insiden masih aktif (*ongoing*). |

### Model: `AffectedSample`

**Lokasi:** `app/Models/AffectedSample.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectedSample extends Model
{
    protected $fillable = [
        'incident_id',
        'sample_code',
        'product_name',
        'batch_number',
        'quantity',
        'notes',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
```

### Model: `Incident` *(relasi terkait)*

**Lokasi:** `app/Models/Incident.php`

Relasi `affectedSamples()` pada `Incident` memberikan akses langsung ke seluruh
sampel yang terkait, dan kolom tanggal dikonfigurasi dengan `$casts` agar
otomatis dikonversi menjadi objek `Carbon`.

```php
// Dalam App\Models\Incident

protected $casts = [
    'deviation_start' => 'datetime',
    'deviation_end'   => 'datetime',
];

public function affectedSamples(): HasMany
{
    return $this->hasMany(AffectedSample::class);
}
```

### Relasi Antar Tabel

```
storage_rooms
    │ 1
    │ hasmany
    ╘══════► incidents
                │ id  ←── FK: incident_id
                │
                └──────► affected_samples
                           - sample_code
                           - product_name
                           - batch_number
                           - quantity
                           - notes
                           - created_at  ← basis filter waktu
```
