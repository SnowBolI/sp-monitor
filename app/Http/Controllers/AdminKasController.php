<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Nasabah;
use App\Models\PegawaiAccountOffice;
use App\Imports\NasabahImport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SuratPeringatan;
use App\Models\Cabang;
use App\Models\KantorKas;
use App\Models\User;
use Response;


class AdminKasController extends Controller
{
   
    
public function dashboard(Request $request)
{
    Log::info('Memasuki fungsi dashboard');

    $title = "Dashboard";

    // Retrieve account officers with jabatan_id = 5

    // Dapatkan ID admin kas yang sedang login
    $currentUser = auth()->user();
    $adminKasId = $currentUser->id;

    $accountOfficers = User::where('jabatan_id', 5)
    ->when($currentUser->id_cabang, function ($query) use ($currentUser) {
        return $query->where('id_cabang', $currentUser->id_cabang);
    })
    ->when($currentUser->id_kantorkas, function ($query) use ($currentUser) {
        return $query->where('id_kantorkas', $currentUser->id_kantorkas);
    })
    ->get();
    // Memulai query dengan relasi yang diperlukan
    $query = Nasabah::with('accountOfficer','adminKas','cabang','kantorkas')
                ->where('id_admin_kas', $adminKasId);  // Filter berdasarkan id_admin_kas

    Log::info('Query awal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan tanggal
    if ($request->has('date_filter')) {
        $dateFilter = $request->input('date_filter');
        Log::info('Filter tanggal diterapkan', ['date_filter' => $dateFilter]);

        switch ($dateFilter) {
            case 'last_7_days':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'last_month':
                $query->whereMonth('created_at', '=', now()->subMonth()->month);
                break;
            case 'last_year':
                $query->whereYear('created_at', '=', now()->subYear()->year);
                break;
        }
    }

    Log::info('Query setelah filter tanggal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan pencarian
    $search = $request->input('search');
    if ($search) {
        $query->where('nama', 'like', "%{$search}%")
              ->orWhereHas('cabang', function ($q) use ($search) {
                  $q->where('nama_cabang', 'like', "%{$search}%");
              })
              ->orWhereHas('kantorkas', function ($q) use ($search) {
                  $q->where('nama_kantorkas', 'like', "%{$search}%");
              });
    }

    // Filter based on cabang
    $cabangFilter = $request->input('cabang_filter');
    if ($cabangFilter) {
        $query->whereHas('cabang', function ($q) use ($cabangFilter) {
            $q->where('id_cabang', $cabangFilter);
        });
    }

    // Filter based on kantorkas
    $wilayahFilter = $request->input('wilayah_filter');
    if ($wilayahFilter) {
        $query->whereHas('kantorkas', function ($q) use ($wilayahFilter) {
            $q->where('id_kantorkas', $wilayahFilter);
        });
    }

    Log::info('Query setelah filter cabang dan kantorkas: ', ['query' => $query->toSql()]);

    $perPage = $request->input('per_page') ?: null;
    
    // Paginate the results 
    $nasabahs = $perPage ? $query->paginate($perPage) : $query->get();
    $nasabahNames = Nasabah::pluck('nama', 'no');

    $suratPeringatans = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama')
    ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
    ->get()
    ->sortByDesc('tingkat');
    $cabangs = Cabang::all();
    $kantorkas = KantorKas::all();

    return view('admin-kas.dashboard', compact('title', 'accountOfficers','nasabahs', 'suratPeringatans', 'cabangs', 'kantorkas', 'currentUser','nasabahNames'));
}

//     public function dashboard(Request $request)
// {
//     Log::info('Memasuki fungsi dashboard');

//     $title = "Dashboard";

//     // Retrieve account officers with jabatan_id = 5
//     $accountOfficers = User::where('jabatan_id', 5)->get();  // Change pluck to get to retrieve the full user objects

    
//     $query = Nasabah::with('accountOfficer','adminKas','cabang','kantorkas');

//     // Log query awal
//     Log::info('Query awal: ', ['query' => $query->toSql()]);

//     // Filter berdasarkan tanggal
//     if ($request->has('date_filter')) {
//         $dateFilter = $request->input('date_filter');
//         Log::info('Filter tanggal diterapkan', ['date_filter' => $dateFilter]);

//         switch ($dateFilter) {
//             case 'last_7_days':
//                 $query->where('created_at', '>=', now()->subDays(7));
//                 break;
//             case 'last_30_days':
//                 $query->where('created_at', '>=', now()->subDays(30));
//                 break;
//             case 'last_month':
//                 $query->whereMonth('created_at', '=', now()->subMonth()->month);
//                 break;
//             case 'last_year':
//                 $query->whereYear('created_at', '=', now()->subYear()->year);
//                 break;
//         }
//     }

//     // Log query setelah filter tanggal
//     Log::info('Query setelah filter tanggal: ', ['query' => $query->toSql()]);

//     // Filter berdasarkan pencarian
//     $search = $request->input('search');
//     if ($search) {
//         $query->where('nama', 'like', "%{$search}%")
//               ->orWhereHas('cabang', function ($q) use ($search) {
//                   $q->where('nama_cabang', 'like', "%{$search}%");
//               })
//               ->orWhereHas('kantorkas', function ($q) use ($search) {
//                   $q->where('nama_kantorkas', 'like', "%{$search}%");
//               });
//     }

//     // Filter based on cabang
//     $cabangFilter = $request->input('cabang_filter');
//     if ($cabangFilter) {
//         $query->whereHas('cabang', function ($q) use ($cabangFilter) {
//             $q->where('id_cabang', $cabangFilter);
//         });
//     }

//     // Filter based on kantorkas
//     $wilayahFilter = $request->input('wilayah_filter');
//     if ($wilayahFilter) {
//         $query->whereHas('kantorkas', function ($q) use ($wilayahFilter) {
//             $q->where('id_kantorkas', $wilayahFilter);
//         });
//     }

//     Log::info('Query setelah filter cabang dan kantorkas: ', ['query' => $query->toSql()]);

//     $nasabahs = $query->get();
//     $nasabahNames = Nasabah::pluck('nama', 'no');

//     $suratPeringatans = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama')
//     ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
//     ->get()
//     ->sortByDesc('tingkat');
//     $cabangs = Cabang::all();
//     $kantorkas = KantorKas::all();
//     $currentUser = auth()->user();

//     return view('admin-kas.dashboard', compact('title', 'accountOfficers','nasabahs', 'suratPeringatans', 'cabangs', 'kantorkas', 'currentUser','nasabahNames'));
// }
public function editNasabah($no)
{
    $nasabah = Nasabah::find($no);
    return response()->json($nasabah);
}

public function update(Request $request, $no)
    {
        $request->validate([
            'nama' => 'required',
            'pokok' => 'required|numeric',
            'bunga' => 'required|numeric',
            'denda' => 'required|numeric',
            'keterangan' => 'required',
            'tanggal_jtp' => 'required|date',
            'kembali' => 'required|date',
            'id_cabang' => 'required|integer',
            'id_kantorkas' => 'required|integer',
            'id_account_officer' => 'required|integer'
        ]);

        $nasabah = Nasabah::where('no', $no)->firstOrFail();
        $nasabah->update($request->all());

        return redirect()->route('admin-kas.dashboard')->with('success', 'Data berhasil di update');
    }

public function deleteNasabah($no)
{
    Nasabah::find($no)->delete();
    return redirect()->route('admin-kas.dashboard')->with('success', 'Data berhasil di hapus');
}

public function detailNasabah($no)
{
    $nasabah = Nasabah::find($no);
    return response()->json($nasabah);
}

public function addSurat(Request $request)
{
    Log::info('Add Surat request received', $request->all());

    $request->validate([
        'no' => 'required',
        'kategori' => 'required',
        'tingkat' => 'required',
        'scan_pdf' => 'required|mimes:pdf|max:2048'
    ]);

    Log::info('Data passed validation', $request->all());

    try {
        $suratData = $request->only(['no', 'kategori', 'tingkat', 'dibuat', 'kembali']);

        // Ambil id_account_officer dari tabel Nasabah berdasarkan 'no'
        $nasabah = Nasabah::where('no', $suratData['no'])->first();

        if (!$nasabah) {
            return redirect()->back()->with('error', 'Nasabah not found.');
        }

        $accountOfficerId = $nasabah->id_account_officer;

        // Handle limit
        $existingEntries = SuratPeringatan::where('no', $suratData['no'])->count();

        if ($existingEntries >= 3) {
            return redirect()->back()->with('error', 'This Nasabah already has the maximum allowed Surat Peringatan entries (3).');
        }

        $duplicateTingkat = SuratPeringatan::where('no', $suratData['no'])
            ->where('tingkat', $suratData['tingkat'])
            ->exists();

        if ($duplicateTingkat) {
            return redirect()->back()->with('error', "Surat Peringatan with Tingkat {$suratData['tingkat']} already exists for this Nasabah.");
        }

        // Handle the PDF upload for 'scan_pdf'
        if ($request->hasFile('scan_pdf')) {
            $scanPdfPath = $request->file('scan_pdf')->store('scan_pdf', 'public');
            $suratData['scan_pdf'] = $scanPdfPath;

            Log::info('PDF file uploaded successfully', ['path' => $scanPdfPath]);
        }

        // Save the Surat Peringatan
        SuratPeringatan::create([
            'no' => $suratData['no'],
            'kategori' =>$suratData['kategori'],
            'tingkat' => $suratData['tingkat'],
            'dibuat' => $suratData['dibuat'],
            'kembali' => $suratData['kembali'],
            'scan_pdf' => $suratData['scan_pdf'],
            'id_account_officer' => $accountOfficerId,
        ]);

        Log::info('Surat Peringatan added successfully', $suratData);

        return redirect()->route('admin-kas.dashboard')->with('success', 'Data berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Surat Peringatan: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return redirect()->back()->with('error', 'Failed to add data');
    }
}


// public function addSurat(Request $request)
// {
//     Log::info('Add Surat request received', $request->all());

//     $request->validate([
//         'no' => 'required',
//         'tingkat' => 'required',
//         'scan_pdf' => 'required|mimes:pdf|max:2048'
//     ]);

//     Log::info('Data passed validation', $request->all());

//     try {
//         $suratData = $request->only(['no', 'tingkat', 'dibuat','kembali']);

//         // Handle limit
//         $existingEntries = SuratPeringatan::where('no', $suratData['no'])->count();

//         if ($existingEntries >= 3) {
//             return redirect()->back()->with('error', 'This Nasabah already has the maximum allowed Surat Peringatan entries (3).');
//         }

//         $duplicateTingkat = SuratPeringatan::where('no', $suratData['no'])
//             ->where('tingkat', $suratData['tingkat'])
//             ->exists();

//         if ($duplicateTingkat) {
//             return redirect()->back()->with('error', "Surat Peringatan with Tingkat {$suratData['tingkat']} already exists for this Nasabah.");
//         }

//         // Handle the PDF upload for 'scan_pdf'
//         if ($request->hasFile('scan_pdf')) {
//             $scanPdfPath = $request->file('scan_pdf')->store('scan_pdf', 'public');
//             $suratData['scan_pdf'] = $scanPdfPath;

//             Log::info('PDF file uploaded successfully', ['path' => $scanPdfPath]);
//         }

//         $accountOfficerId = auth()->user()->id;

//         // Save the Surat Peringatan
//         SuratPeringatan::create([
//             'no' => $suratData['no'],
//             'tingkat' => $suratData['tingkat'],
//             'dibuat' => $suratData['dibuat'],
//             'kembali' => $suratData['kembali'],
//             'scan_pdf' => $suratData['scan_pdf'],
//             'id_account_officer' => $accountOfficerId,
//         ]);

//         Log::info('Surat Peringatan added successfully', $suratData);

//         return redirect()->route('admin-kas.dashboard')->with('success', 'Data berhasil ditambahkan');
//     } catch (\Exception $e) {
//         Log::error('Error adding Surat Peringatan: ' . $e->getMessage(), [
//             'request' => $request->all(),
//             'exception' => $e->getTraceAsString()
//         ]);

//         return redirect()->back()->with('error', 'Failed to add data');
//     }
// }

public function addNasabah(Request $request)
{
    Log::info('Add Nasabah request received', $request->all());

    $request->validate([
        'no' => 'required|numeric',
        'nama' => 'required|max:255',
        'pokok' => 'required|numeric',
        'bunga' => 'required|numeric',
        'denda' => 'required|numeric',
        'total' => 'required|numeric',
        'keterangan' => 'required',
        'tanggal_jtp' => 'required|date',
        // 'ttd' => 'required|date',
        // 'kembali' => 'required|date',
        'id_cabang' => 'required|exists:cabangs,id_cabang',
        'id_kantorkas' => 'required|exists:kantorkas,id_kantorkas',
        'id_account_officer' => 'required',
    ]);

    try {
        $nasabahData = $request->all();
        $nasabahData['id_admin_kas'] = auth()->user()->id;

        Nasabah::create($nasabahData);  // Insert data into the database
        Log::info('Tanggal JTP:', ['tanggal_jtp' => $request->input('tanggal_jtp')]);
        Log::info('Nasabah added successfully', $nasabahData);
        

        return redirect()->route('admin-kas.dashboard')->with('success', 'Data berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Nasabah: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to add data']);
    }
}

public function importNasabah(Request $request)
{
    // dd($request->all());
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
        $idAdminKas = auth()->user()->id; // Get the ID of the logged-in user directly
        Log::info('Admin Kas Detected', ['id_admin_kas' => $idAdminKas]);

        Excel::import(new NasabahImport($idAdminKas), $request->file('file'));

        return redirect()->back()->with('success', 'Data berhasil diimport!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
    }
}
}
