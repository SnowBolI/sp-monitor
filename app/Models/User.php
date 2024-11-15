<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Nip;
use App\Models\Key;
use App\Models\Jabatan;
use App\Models\Nasabah;
use App\Models\KantorKas;
use App\Models\PegawaiAdminKas;
use App\Models\PegawaiSupervisor;
use App\Models\PegawaiKepalaBagian;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PegawaiKepalaCabang;
use App\Events\UserRegisteredMobile;
use App\Models\PegawaiAccountOffice;
use Illuminate\Notifications\Notifiable;
use App\Events\RegisterKeySubmit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }
    

    // public function nip()
    // {
    //     return $this->belongsTo(Nip::class, 'nip', 'nip');
    // }

    public function key()
    {
        return $this->belongsTo(Key::class, 'key', 'key');
    }

    public function pegawaiKepalaBagian()
    {
        return $this->hasOne(PegawaiKepalaBagian::class, 'id_user');
    }

    public function pegawaiKepalaCabang()
    {
        return $this->hasOne(PegawaiKepalaCabang::class, 'id_user');
    }

    public function pegawaiSupervisor()
    {
        return $this->hasOne(PegawaiSupervisor::class, 'id_user');
    }

    public function pegawaiAdminKas()
    {
        return $this->hasOne(PegawaiAdminKas::class, 'id_user');
    }

    public function pegawaiAccountOfficer()
    {
        return $this->hasOne(PegawaiAccountOffice::class, 'id_user');
    }


    public function kantorkas()
    {
        return $this->belongsTo(KantorKas::class, 'id_kantorkas');
    }
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }
    protected $primaryKey = 'id'; // Atur primary key jika perlu

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no','id');
    }
    public function infostatus()
    {
        return $this->belongsTo(Status::class, 'status', 'id');
    }
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan_id',
        'id_cabang',
        'id_kantorkas',
        'key',
        'status'
    ];


    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => RegisterKeySubmit::class,
    ];
}
