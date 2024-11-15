<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Nip;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\KantorKas;
use App\Models\PegawaiAdminKas;
use App\Models\PegawaiSupervisor;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PegawaiKepalaCabang;
use App\Events\UserRegisteredMobile;
use App\Models\PegawaiAccountOffice;
use Illuminate\Notifications\Notifiable;

class Nasabah extends Model
{
    use HasFactory;
    protected $fillable = [
        'no',
        'nama',
        'pokok',
        'bunga',
        'denda',
        'total',
        'account_officer',
        'keterangan',
        'tanggal_jtp',
        // 'ttd',
        // 'kembali',
        'id_cabang',
        'id_kantorkas',
        'id_account_officer',
        'id_admin_kas',
    ];
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }
    public function kantorkas()
    {
        return $this->belongsTo(KantorKas::class, 'id_kantorkas');
    }
    protected $primaryKey = 'no';

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
    // public function adminkas()
    // {
    //     return $this->belongsTo(PegawaiAdminKas::class, 'id_admin_kas');
    // }
    // public function accountofficer()
    // {
    //     return $this->belongsTo(PegawaiAccountOffice::class, 'id_account_officer');
    // }
    public function suratPeringatan()
    {
        return $this->hasMany(SuratPeringatan::class, 'no' ,'no');
    }
    public function accountOfficer()
    {
        return $this->belongsTo(User::class, 'id_account_officer');
    }

    public function adminKas()
    {
        return $this->belongsTo(User::class, 'id_admin_kas');
    }

    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class, 'no_nasabah', 'no');
    }
    
    // Relasi-relasi lain yang mungkin dimiliki oleh Nasabah
}
