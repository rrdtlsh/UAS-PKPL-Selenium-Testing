<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StorageRoom;
use App\Models\ConditionData;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class CheckOverdueMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek log kondisi ruang penyimpanan yang terlambat (lebih dari 6 jam) dan mengirim notifikasi.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rooms = StorageRoom::all();
        $now = Carbon::now();
        $notifiedCount = 0;

        // Berdasarkan Acceptance Criteria: Mengirim pengingat ke Petugas dan Manajer Laboratorium 
        $targetUsers = User::whereIn('role', ['admin', 'petugas'])->get();

        if ($targetUsers->isEmpty()) {
            $this->warn('Tidak ada user (petugas/admin) di database untuk diberikan notifikasi.');
            return self::FAILURE;
        }

        foreach ($rooms as $room) {
            $latestData = ConditionData::where('storage_room_id', $room->id)
                                       ->latest()
                                       ->first();

            $isOverdue = false;

            // Logika: Jika belum ada rekaman sama sekali, ATAU selisih waktu sudah >= 6 jam.
            if (!$latestData) {
                $isOverdue = true;
            } else {
                if ($latestData->created_at->diffInHours($now) >= 6) {
                    $isOverdue = true;
                }
            }

            if ($isOverdue) {
                // Buatkan Notifikasi otomatis ke tabel `notifications`
                foreach ($targetUsers as $user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'message' => "Peringatan Terlambat (Overdue)! Ruang penyimpanan '{$room->room_name}' belum mendapatkan pengecekan data kondisi ruangan selama lebih dari 6 jam terakhir. Harap segera lakukan dan input pengecekan!"
                    ]);
                }
                $notifiedCount++;
            }
        }

        $this->info("Proses Selesai: Terdapat {$notifiedCount} ruangan yang memicu peringatan otomatis ke manajer dan petugas.");
        return self::SUCCESS;
    }
}
