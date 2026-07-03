<?php

namespace Database\Seeders;

use App\Models\Poly;
use Illuminate\Database\Seeder;

class AdditionalPoliesSeeder extends Seeder
{
    public function run(): void
    {
        $polies = [
            [
                'name' => 'Poli Mata',
                'color' => '#7C3AED',
                'icon' => 'fa-eye',
                'description' => 'Pemeriksaan dan perawatan penglihatan',
                'is_active' => true,
            ],
            [
                'name' => 'Poli Jantung',
                'color' => '#DC2626',
                'icon' => 'fa-heart-pulse',
                'description' => 'Konsultasi dan pemeriksaan jantung',
                'is_active' => true,
            ],
        ];

        foreach ($polies as $p) {
            // Cegah duplikat jika sudah ada
            if (!Poly::where('name', $p['name'])->exists()) {
                Poly::create($p);
            }
        }
    }
}