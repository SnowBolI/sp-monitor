<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'no_nasabah',
        'tanggal',
        'keterangan',
        'koordinat',
        'keterangan',
        'bukti_gambar'
    ];
    
    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no_nasabah', 'no');
    }
}
