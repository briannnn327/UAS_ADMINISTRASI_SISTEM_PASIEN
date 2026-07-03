<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Poly;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk menambah dokter & poli.
     */
    public function run(): void
    {
        // 1. PASTIKAN POLI MATA & JANTUNG ADA
        $poliesData = [
            ['name' => 'Poli Mata', 'color' => '#7C3AED', 'icon' => 'fa-eye', 'description' => 'Pemeriksaan dan perawatan penglihatan'],
            ['name' => 'Poli Jantung', 'color' => '#DC2626', 'icon' => 'fa-heart-pulse', 'description' => 'Konsultasi dan pemeriksaan jantung'],
        ];

        foreach ($poliesData as $pData) {
            Poly::firstOrCreate(
                ['name' => $pData['name']],
                $pData
            );
        }

        // 2. DATA DOKTER (username, email, phone, poly_name, specialization)
        $doctors = [
            // Poli Umum (sudah ada dr_siti, tambah dr_budi)
            ['dr_budi', 'budi@klinikgenz.com', '081234567893', 'Poli Umum', 'Dokter Umum'],

            // Poli Gigi
            ['dr_anita', 'anita@klinikgenz.com', '081234567894', 'Poli Gigi', 'Dokter Gigi'],
            ['dr_rendi', 'rendi@klinikgenz.com', '081234567895', 'Poli Gigi', 'Dokter Gigi Spesialis'],

            // Poli Anak
            ['dr_sari', 'sari@klinikgenz.com', '081234567896', 'Poli Anak', 'Dokter Spesialis Anak'],
            ['dr_toni', 'toni@klinikgenz.com', '081234567897', 'Poli Anak', 'Dokter Anak'],

            // Poli Kandungan
            ['dr_maya', 'maya@klinikgenz.com', '081234567898', 'Poli Kandungan', 'Dokter Spesialis Kandungan'],
            ['dr_rina', 'rina@klinikgenz.com', '081234567899', 'Poli Kandungan', 'Dokter Kandungan'],

            // Poli Mata
            ['dr_adi', 'adi@klinikgenz.com', '081234567810', 'Poli Mata', 'Dokter Spesialis Mata'],
            ['dr_nina', 'nina@klinikgenz.com', '081234567811', 'Poli Mata', 'Dokter Mata'],

            // Poli Jantung
            ['dr_fahri', 'fahri@klinikgenz.com', '081234567812', 'Poli Jantung', 'Dokter Spesialis Jantung'],
            ['dr_lina', 'lina@klinikgenz.com', '081234567813', 'Poli Jantung', 'Dokter Jantung'],
        ];

        // 3. LOOPING BUAT DOKTER
        foreach ($doctors as [$username, $email, $phone, $polyName, $specialization]) {
            // Cari poli
            $poly = Poly::where('name', $polyName)->first();
            if (!$poly) {
                $this->command->error("❌ Poli '$polyName' tidak ditemukan!");
                continue;
            }

            // Cek apakah user sudah ada (pakai email)
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->command->warn("⚠️  User $email sudah ada, lewati.");
                continue;
            }

            // Buat user
            $user = User::create([
                'username'  => $username,
                'email'     => $email,
                'phone'     => $phone,
                'password'  => Hash::make('password'),
                'role'      => 'doctor',
                'is_active' => true,
            ]);

            // Buat doctor
            $doctor = Doctor::create([
                'user_id'        => $user->id,
                'poly_id'        => $poly->id,
                'specialization' => $specialization,
                'license_number' => 'SIP-' . rand(100, 999) . '/' . date('Y'),
                'bio'            => 'Dokter spesialis ' . $specialization,
                'is_available'   => true,
            ]);

            // Tambahkan jadwal (Senin & Rabu) jika belum ada
            $existingSchedules = $doctor->schedules()->count();
            if ($existingSchedules === 0) {
                $doctor->schedules()->createMany([
                    [
                        'day_of_week'  => 1, // Senin
                        'start_time'   => '08:00',
                        'end_time'     => '12:00',
                        'max_slots'    => 20,
                        'is_available' => true,
                    ],
                    [
                        'day_of_week'  => 3, // Rabu
                        'start_time'   => '13:00',
                        'end_time'     => '17:00',
                        'max_slots'    => 20,
                        'is_available' => true,
                    ],
                ]);
            }

            $this->command->info("✅ Dokter $username ($specialization) berhasil dibuat di $polyName");
        }

        $this->command->info("\n🎉 Selesai! Semua dokter telah ditambahkan.");
    }
}