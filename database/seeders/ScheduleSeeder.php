<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Tambahkan jadwal praktik untuk semua dokter.
     */
    public function run(): void
    {
        // Konfigurasi jadwal yang akan diberikan ke semua dokter
        $schedulesConfig = [
            ['day' => 1, 'start' => '08:00', 'end' => '12:00', 'note' => 'Senin Pagi'],
            ['day' => 2, 'start' => '13:00', 'end' => '17:00', 'note' => 'Selasa Siang'],
            ['day' => 3, 'start' => '08:00', 'end' => '12:00', 'note' => 'Rabu Pagi'],
            ['day' => 4, 'start' => '13:00', 'end' => '17:00', 'note' => 'Kamis Siang'],
            ['day' => 5, 'start' => '08:00', 'end' => '12:00', 'note' => 'Jumat Pagi'],
        ];

        // Ambil semua dokter
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->command->error('❌ Tidak ada dokter ditemukan. Jalankan DoctorSeeder dulu.');
            return;
        }

        foreach ($doctors as $doctor) {
            $countAdded = 0;

            foreach ($schedulesConfig as $config) {
                // Cek apakah jadwal untuk hari itu sudah ada untuk dokter ini
                $exists = Schedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $config['day'])
                    ->exists();

                // Jika belum ada, buat jadwal baru
                if (!$exists) {
                    Schedule::create([
                        'doctor_id'    => $doctor->id,
                        'day_of_week'  => $config['day'],
                        'start_time'   => $config['start'],
                        'end_time'     => $config['end'],
                        'max_slots'    => 20,
                        'is_available' => true,
                        'notes'        => $config['note'],
                    ]);
                    $countAdded++;
                }
            }

            $this->command->info("✅ Dokter dr. {$doctor->user->username}: menambahkan {$countAdded} jadwal baru.");
        }

        $this->command->info("\n🎉 Selesai! Semua dokter sekarang memiliki jadwal Senin s/d Jumat.");
    }
}