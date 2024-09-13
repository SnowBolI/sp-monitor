<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KantorKasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //
        DB::table('kantorkas')->insert([
            ['nama_kantorkas' => 'Kantor Kas Karangrejo'],
            ['nama_kantorkas' => 'Kantor Kas Barat'],
            ['nama_kantorkas' => 'Kantor Kas Magetan'],
            ['nama_kantorkas' => 'Kantor Kas Ngariboyo'],
            ['nama_kantorkas' => 'Kantor Kas Takeran'],
            ['nama_kantorkas' => 'Kantor Kas Bendo'],
            ['nama_kantorkas' => 'Kantor Kas Maospati'],
            ['nama_kantorkas' => 'Kantor Kas Karas'],
            ['nama_kantorkas' => 'Kantor Kas Panekan'],
            ['nama_kantorkas' => 'Kantor Kas Lembeyan'],
            ['nama_kantorkas' => 'Kantor Kas Parang'],
            ['nama_kantorkas' => 'Kantor Kas Rejoso'],
            ['nama_kantorkas' => 'Kantor Kas Jiwan'],
            ['nama_kantorkas' => 'Kantor Kas Dolopo'],
            ['nama_kantorkas' => 'Kantor Kas Sumotoro'],
        ]);
    }
}
