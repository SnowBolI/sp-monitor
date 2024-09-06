<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KeySeeder extends Seeder
{
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            DB::table('keys')->insert([
                'key' => random_int(100000, 999999),
                'jabatan' => ($i % 5) + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert the 'Super Admin' key outside the loop
        DB::table('keys')->insert([
            'key' => 99,
            'jabatan' => 'Super Admin', 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}