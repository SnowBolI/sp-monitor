<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPeringatan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_peringatan';
    
    protected $fillable = [
        'no', 'kategori', 'tingkat', 'dibuat', 'kembali', 'diserahkan', 'keterangan', 'bukti_gambar', 'scan_pdf', 'id_account_officer'
    ];

    public function accountOfficer()
    {
        return $this->belongsTo(User::class,'id_account_officer');
    }

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no','no');
    }
}
