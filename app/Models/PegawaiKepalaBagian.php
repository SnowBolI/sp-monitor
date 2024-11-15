<?php

namespace App\Models;

use App\Models\Cabang;
use App\Models\Direksi;
use Illuminate\Database\Eloquent\Model;

class PegawaiKepalaBagian extends Model
{
    protected $table = 'pegawai_kepala_bagian'; // Sesuaikan dengan nama tabel yang sesuai
    protected $primaryKey = 'id_kepala_bagian'; // Atur primary key jika perlu
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }



    public function direksi()
    {
        return $this->belongsTo(Direksi::class, 'id_direksi');
    }

    protected $fillable = [
        'id_kepala_bagian',
        'nama_kepala_bagian',
        'id_user',
        'id_jabatan',
        'id_cabang',
        'id_direksi',
        'email',
        'password',
        // tambahkan atribut tambahan jika diperlukan
    ];

    // Tambahkan relasi atau metode lain jika diperlukan
}
