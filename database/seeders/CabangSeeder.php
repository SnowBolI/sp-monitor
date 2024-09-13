<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //
        DB::table('cabangs')->insert([
            ['nama_cabang' => 'Kantor Cabang Utama'],
            ['nama_cabang' => 'Kantor Cabang Nganjuk'],
            ['nama_cabang' => 'Kantor Cabang Madiun'],
            ['nama_cabang' => 'Kantor Cabang Ponorogo'],
            ['nama_cabang' => 'Kantor Cabang Trenggalek'],
            ['nama_cabang' => 'Kantor Cabang Ngawi'],
        ]);
    }
}
