<?php

namespace Database\Seeders;

use App\Models\Pendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pendidikan::create(['nama' => 'TIDAK / BELUM SEKOLAH',]);
        Pendidikan::create(['nama' => 'BELUM TAMAT SD/SEDERAJAT',]);
        Pendidikan::create(['nama' => 'TAMAT SD / SEDERAJAT',]);
        Pendidikan::create(['nama' => 'SLTP/SEDERAJAT',]);
        Pendidikan::create(['nama' => 'SLTA / SEDERAJAT',]);
        Pendidikan::create(['nama' => 'DIPLOMA I / II',]);
        Pendidikan::create(['nama' => 'AKADEMI/ DIPLOMA III/S. MUDA',]);
        Pendidikan::create(['nama' => 'DIPLOMA IV/ STRATA I',]);
        Pendidikan::create(['nama' => 'STRATA II',]);
        Pendidikan::create(['nama' => 'STRATA III']);
    }
}
