<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KantorKas extends Model
{
    protected $table = 'kantorkas'; // Sesuaikan dengan nama tabel yang sesuai
    protected $primaryKey = 'id_kantorkas'; // Atur primary key jika perlu

    protected $fillable = [
        'nama_kantorkas',
        // tambahkan atribut tambahan jika diperlukan   
    ];

    public $timestamps = false;

    // Relasi many-to-many dengan model Cabang
    public function cabangs()
    {
        return $this->belongsToMany(Cabang::class, 'kantorkas_id', 'cabang_id');
    }
}
