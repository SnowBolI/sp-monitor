<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use App\Models\PegawaiAdminKas;
use App\Models\PegawaiSupervisor;
use Illuminate\Support\Facades\DB;
use App\Models\PegawaiKepalaCabang;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PegawaiAccountOffice;
use Illuminate\Support\Facades\Auth;

class MobileMonitoringController extends Controller
{
    public function getNasabah(Request $request)
    {
        Log::info('Request received for getNasabah', ['request' => $request->all()]);
        $user = Auth::user();
        Log::info('Authenticated user', ['user' => $user]);
        $jabatan = $user->jabatan->nama_jabatan;
        Log::info('Jabatan user', ['jabatan' => $jabatan]);

        $perPage = 15; // Jumlah nasabah per halaman

        // Eager load semua relasi yang diperlukan dan join dengan surat peringatan
        $query = Nasabah::select(
            'nasabahs.no',
            'nasabahs.nama',
            'nasabahs.pokok',
            'nasabahs.bunga',
            'nasabahs.denda',
            'nasabahs.total',
            'nasabahs.keterangan',
            'nasabahs.tanggal_jtp',
            'nasabahs.id_cabang',
            'nasabahs.id_kantorkas',
            'nasabahs.id_admin_kas',
            'nasabahs.id_account_officer',
            DB::raw('MAX(surat_peringatans.updated_at) as surat_updated_at')
        )
        ->leftJoin('surat_peringatans', 'nasabahs.no', '=', 'surat_peringatans.no')
        ->with([
            'cabang:id_cabang,nama_cabang',
            'kantorkas:id_kantorkas,nama_kantorkas',
            'adminkas:id,name',
            'accountofficer:id,name',
            'suratPeringatan' => function ($query) {
                $query->orderBy('tingkat', 'desc'); // Urutkan surat peringatan berdasarkan tingkat dari yang terbesar
            }
        ])
        ->groupBy(
            'nasabahs.no',
            'nasabahs.nama',
            'nasabahs.pokok',
            'nasabahs.bunga',
            'nasabahs.denda',
            'nasabahs.total',
            'nasabahs.tanggal_jtp',
            'nasabahs.keterangan',
            'nasabahs.id_cabang',
            'nasabahs.id_kantorkas',
            'nasabahs.id_admin_kas',
            'nasabahs.id_account_officer'
        )
        ->orderByRaw('
            CASE 
                WHEN surat_peringatans.updated_at IS NOT NULL THEN 1 
                ELSE 2 
            END, 
            surat_updated_at DESC
        ');



        // Filter berdasarkan jabatan
        if ($user) {
            $idCabang = $user->id_cabang;
            $idkantorkas= $user->id_kantorkas;
            $idUser = $user->id;
            $jabatan = $user->jabatan->nama_jabatan;


            switch ($jabatan) {
                case 'Kepala Cabang':
                    Log::info('Fetching nasabahs for Kepala Cabang');
                    $query->where('id_cabang', $idCabang);
                    break;
                case 'Kepala Bagian':
                    Log::info('Fetching nasabahs for Kepala Bagian');
                    $query->where('id_cabang', $idCabang);
                    break;
            
                case 'Supervisor':
                    Log::info('Fetching nasabahs for Supervisor');
                    $query->where('id_cabang', $idCabang)->where('id_kantorkas', $idkantorkas);
                    break;
            
                case 'Admin Kas':
                    Log::info('Fetching nasabahs for Admin Kas');
                    $query->where('id_admin_kas', $idUser);
                    break;
            
                case 'Account Officer':
                    Log::info('Fetching nasabahs for Account Officer');
                    $query->where('nasabahs.id_account_officer', $idUser);
                    break;
            }
        }

        // Filter search
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->search;
            Log::info('Search parameter provided', ['search' => $search]);
        
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('accountofficer', function($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('kantorkas', function($q) use ($search) {
                        $q->where('nama_kantorkas', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('adminkas', function($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('cabang', function($q) use ($search) {
                        $q->where('nama_cabang', 'LIKE', '%' . $search . '%');
                    });
                    
            });
        }

        // Filter cabang
        if ($request->has('cabang') && $request->filled('cabang')) {
            $cabang = $request->cabang;
            Log::info('Cabang parameter provided', ['cabang' => $cabang]);
        
            $query->whereHas('cabang', function($q) use ($cabang) {
                $q->where('nama_cabang', 'LIKE', '%' . $cabang . '%');
            });
        }

        Log::info('Executing query to fetch nasabahs');
        $nasabahs = $query->paginate($perPage);

        Log::info('Transforming nasabah data to include all Surat Peringatan');
        $nasabahs->getCollection()->transform(function($nasabah) {
            $allSuratPeringatan = $nasabah->suratPeringatan->map(function($suratPeringatan) {
                return [
                    'no' => $suratPeringatan->no,
                    'kategori' => $suratPeringatan->kategori,
                    'tingkat' => $suratPeringatan->tingkat,
                    'dibuat' => $suratPeringatan->dibuat,
                    'kembali' => $suratPeringatan->kembali,
                    'diserahkan' => $suratPeringatan->diserahkan,
                    'bukti_gambar' => $suratPeringatan->bukti_gambar,
                    'scan_pdf' => $suratPeringatan->scan_pdf,
                    'id_account_officer' => $suratPeringatan->user,
                ];
            });

            return [
                'no' => $nasabah->no,
                'nama' => $nasabah->nama,
                'pokok' => $nasabah->pokok,
                'bunga' => $nasabah->bunga,
                'denda' => $nasabah->denda,
                'total' => $nasabah->total,
                'keterangan' => $nasabah->keterangan,
                'tanggal_jtp' => $nasabah->tanggal_jtp,
                'cabang' => $nasabah->cabang->nama_cabang,
                'kantorkas' => $nasabah->kantorkas->nama_kantorkas,
                'adminKas' => $nasabah->adminkas->name,
                'accountOfficer' => $nasabah->accountofficer->name,
                'suratPeringatan' => $allSuratPeringatan->toArray(),
            ];
        });

        Log::info('Customers fetched successfully', ['customers' => $nasabahs->toArray()]);
        return response()->json($nasabahs->toArray());
    }

    // public function getNasabah(Request $request)
    // {
    //     Log::info('Request received for getNasabah', ['request' => $request->all()]);
    //     $user = Auth::user();
    //     Log::info('Authenticated user', ['user' => $user]);
    //     $jabatan = $user->jabatan->nama_jabatan;
    //     Log::info('Jabatan user', ['jabatan' => $jabatan]);

    //     $perPage = 15; // Jumlah nasabah per halaman

    //     // Eager load semua relasi yang diperlukan
       
    //     $query = Nasabah::select('no', 'nama', 'pokok', 'bunga', 'denda', 'total','keterangan', 'id_cabang', 'id_kantorkas', 'id_admin_kas', 'id_account_officer')
    //     ->with([
    //         'cabang:id_cabang,nama_cabang',
    //         'kantorkas:id_kantorkas,nama_kantorkas',
    //         'adminkas:id,name',
    //         'accountofficer:id,name',
    //         'suratPeringatan:id_peringatan,no,tingkat,dibuat,kembali,diserahkan,bukti_gambar,scan_pdf',
    //         'suratPeringatan' => function ($query) {
    //             $query->orderBy('tingkat', 'desc'); // Urutkan surat peringatan berdasarkan tingkat dari yang terbesar
    //         }
    //     ]);
    //     if ($user) {
    //         $idCabang = $user->id_cabang;
    //         $idkantorkas= $user->id_kantorkas;
    //         $idUser = $user->id;
    //         $jabatan = $user->jabatan->nama_jabatan;
    //     } else {
    //         Log::error('User not authenticated');
    //         return response()->json(['error' => 'User not authenticated'], 403);
    //     }
        
    //     switch ($jabatan) {
    //         case 'Kepala Cabang':
    //             Log::info('Fetching nasabahs for Kepala Cabang');
    //             $query->where('id_cabang', $idCabang);
    //             break;
        
    //         case 'Supervisor':
    //             Log::info('Fetching nasabahs for Supervisor');
    //             $query->where('id_cabang', $idCabang)->where('id_kantorkas', $idkantorkas);
    //             break;
        
    //         case 'Admin Kas':
    //             Log::info('Fetching nasabahs for Admin Kas');
    //             $query->where('id_admin_kas', $idUser);
    //             break;
        
    //         case 'Account Officer':
    //             Log::info('Fetching nasabahs for Account Officer');
    //             $query->where('id_account_officer', $idUser);
    //             break;
            
        
    //         // other cases...
    //     }
        

    //     if ($request->has('search') && $request->filled('search')) {
    //         $search = $request->search;
    //         Log::info('Search parameter provided', ['search' => $search]);
    
    //         $query->where(function ($q) use ($search) {
    //             $q->where('nama', 'LIKE', '%' . $search . '%')
    //                 ->orWhereHas('accountofficer', function($q) use ($search) {
    //                     $q->where('name', 'LIKE', '%' . $search . '%');
    //                 })
    //                 ->orWhereHas('kantorkas', function($q) use ($search) {
    //                     $q->where('nama_kantorkas', 'LIKE', '%' . $search . '%');
    //                 })
    //                 ->orWhereHas('cabang', function($q) use ($search) {
    //                     $q->where('nama_cabang', 'LIKE', '%' . $search . '%');
    //                 });
    //         });
    //     }
    
    //     if ($request->has('cabang') && $request->filled('cabang')) {
    //         $cabang = $request->cabang;
    //         Log::info('Cabang parameter provided', ['cabang' => $cabang]);
    
    //         $query->whereHas('cabang', function($q) use ($cabang) {
    //             $q->where('nama_cabang', 'LIKE', '%' . $cabang . '%');
    //         });
    //     }
        
    //     Log::info('Executing query to fetch nasabahs');
    //     $nasabahs = $query->paginate($perPage);

    //     Log::info('Transforming nasabah data to include all Surat Peringatan');
    //     $nasabahs->getCollection()->transform(function($nasabah) {
    //         $allSuratPeringatan = $nasabah->suratPeringatan->map(function($suratPeringatan) {
    //             return [
    //                 'no' => $suratPeringatan->no,
    //                 'tingkat' => $suratPeringatan->tingkat,
    //                 'dibuat' => $suratPeringatan->dibuat,
    //                 'kembali' => $suratPeringatan->kembali,
    //                 'diserahkan' => $suratPeringatan->diserahkan,
    //                 'bukti_gambar' => $suratPeringatan->bukti_gambar,
    //                 'scan_pdf' => $suratPeringatan->scan_pdf,
    //                 'id_account_officer' => $suratPeringatan->user,
    //             ];
    //         });

    //         return [
    //             'no' => $nasabah->no,
    //             'nama' => $nasabah->nama,
    //             'pokok' => $nasabah->pokok,
    //             'bunga' => $nasabah->bunga,
    //             'denda' => $nasabah->denda,
    //             'total' => $nasabah->total,
    //             'keterangan' => $nasabah->keterangan,
    //             'cabang' => $nasabah->cabang->nama_cabang,
    //             'kantorkas' => $nasabah->kantorkas->nama_kantorkas,
    //             'adminKas' => $nasabah->adminkas->name,
    //             'accountOfficer' => $nasabah->accountofficer->name,

    //             'suratPeringatan' => $allSuratPeringatan->toArray(), // Convert collection to array
    //         ];
    //     });

    //     Log::info('Customers fetched successfully', ['customers' => $nasabahs->toArray()]);
    //     return response()->json($nasabahs->toArray());
    // }
    public function cabang()
    {
        $cabang = Cabang::all();
        return response()->json($cabang);
    }
}
