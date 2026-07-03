<?php

namespace Database\Seeders;

use App\Models\Poly;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Poli
        $polies = [
            ['name' => 'Poli Umum', 'color' => '#1565C0', 'icon' => 'fa-stethoscope', 'description' => 'Pelayanan kesehatan umum'],
            ['name' => 'Poli Gigi', 'color' => '#00ACC1', 'icon' => 'fa-tooth', 'description' => 'Perawatan gigi dan mulut'],
            ['name' => 'Poli Anak', 'color' => '#2E7D32', 'icon' => 'fa-child', 'description' => 'Kesehatan anak dan imunisasi'],
            ['name' => 'Poli Kandungan', 'color' => '#C2185B', 'icon' => 'fa-baby', 'description' => 'Kesehatan ibu dan kandungan'],
        ];
        foreach ($polies as $p) {
            Poly::create($p);
        }

        // 2. Buat Admin
        User::create([
            'username' => 'admin',
            'email' => 'admin@klinikgenz.com',
            'phone' => '081234567890',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Buat Dokter Contoh
        $doctorUser = User::create([
            'username' => 'dr_siti',
            'email' => 'dokter@klinikgenz.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'is_active' => true,
        ]);
        $doctorUser->doctor()->create([
            'poly_id' => 1,
            'specialization' => 'Dokter Umum',
            'license_number' => 'SIP-001/2024',
            'bio' => 'Dokter umum berpengalaman 10 tahun.',
            'is_available' => true,
        ]);

        // 4. Buat Pasien Contoh
        User::create([
            'username' => 'pasien1',
            'email' => 'pasien@klinikgenz.com',
            'phone' => '081234567892',
            'password' => Hash::make('password'),
            'role' => 'patient',
            'is_active' => true,
        ]);

        // 5. Tambahkan Jadwal untuk Dokter
        $doctor = $doctorUser->doctor;
        $doctor->schedules()->createMany([
            [
                'day_of_week' => 1, // Senin
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_slots' => 20,
                'is_available' => true,
            ],
            [
                'day_of_week' => 3, // Rabu
                'start_time' => '13:00',
                'end_time' => '17:00',
                'max_slots' => 20,
                'is_available' => true,
            ],
        ]);
    }
}