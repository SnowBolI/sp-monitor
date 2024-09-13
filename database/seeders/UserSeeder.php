<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan sementara foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus semua data pengguna yang ada
        User::truncate();

        // Aktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Daftar nama-nama dan id_cabang yang akan dimasukkan
        $users = [
            ["Didik Purnomo", 1],
            ["Aldi Bagus Septian", 1],
            ["Yesi Indrianasari", 1],
            ["Susanto", 4],
            ["Muhammad Ramadhan Firdaus Elbahar", 2],
            ["Ari Wahyudi", 6],
            ["Yannis One Brielliant", 1],
            ["Achmad Agung Nurcahyono", 1],
            ["Kun Ekwan Junianto", 1],
            ["Nova Herianto", 4],
            ["Taufik Dian Murseto", 1],
            ["Danang Windianto", 1],
            ["Yani Widodo", 6],
            ["Suprapto", 1],
            ["Muhamad Rois Muchlisin", 2],
            ["Fajar Baskoro", 2],
            ["Fanny Permana Sujatmiko", 1],
            ["Arisman Anna", 1],
            ["Siti Latifa", 1],
            ["Ridho Alex Kuncoro", 2],
            ["Asrul Anggriawan Azis", 3],
            ["Suhardianto", 3],
            ["Panji Dwi Febrianto", 3],
            ["Kamal Abu Hasan", 3],
            ["Basuki Rahmat", 5],
            ["Lega Frediyan Satriadi", 5],
            ["Muhammad Dwi Hardianto", 5],
            ["Zeqi Irawan", 1],
            ["Azhar Datuk Herman Basjori", 1],
            ["Yohanes Gigih Pranata Putra", 1],
            ["Syifa Habibia Nafidina", 1],
            ["Rendra Firmansyah", 1],
            ["Angga Prasetiawan", 1],
            ["Reza Nur Fajar Romadlon", 1],
            ["Yona Bagus Pradana", 4],
            ["Dani Lestari", 1]
        ];
        
        foreach ($users as [$name, $id_cabang]) {
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'password' => bcrypt('password'), 
                'jabatan_id' => 5,
                'id_cabang' => $id_cabang,
                'id_kantorkas' => null // Setting this to null as per your previous image
            ]);
        }

        // Tambahkan pengguna "admin" dengan jabatan_id 99
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'jabatan_id' => 99,
            'id_cabang' => 1, // Assuming admin is associated with Kantor Cabang Utama
            'id_kantorkas' => null
        ]);
    }
}