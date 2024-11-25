<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\PegawaiAccountOffice;
use App\Models\SuratPeringatan;
use App\Models\Kunjungan;
use App\Models\Cabang;
use App\Models\KantorKas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;


class SupervisorController extends Controller
{

//     public function dashboard(Request $request)
// {
//     Log::info('Memasuki fungsi dashboard');
//     $title = "Dashboard";
//     $currentUser = auth()->user();
//     $userCabang = $currentUser->id_cabang;

//     // Log current user details
//     Log::info('Current User:', ['id' => $currentUser->id, 'id_cabang' => $currentUser->id_cabang, 'id_kantorkas' => $currentUser->id_kantorkas]);

//     // Retrieve account officers with jabatan_id = 5
//     $accountOfficers = User::where('jabatan_id', 5)->get();
//     Log::info('Account Officers:', ['count' => $accountOfficers->count()]);

//     // Query nasabah dengan filter berdasarkan id_cabang dari user yang login
//     $query = Nasabah::with('accountOfficer', 'adminKas', 'cabang', 'kantorkas')
//         ->where('id_cabang', $currentUser->id_cabang) // Filter based on id_cabang of the logged-in user
//         ->where('id_kantorkas', $currentUser->id_kantorkas);

//     $aocabang = User::where('id_cabang', $userCabang)
//         ->where('jabatan_id', 5)
//         ->get();
//     Log::info('AO Cabang:', ['count' => $aocabang->count()]);

//     // Filter berdasarkan no
//     if ($request->has('no')) {
//         $noFilter = $request->input('no');
//         $query->where('no', $noFilter);
//         Log::info('Filter by No:', ['no' => $noFilter]);
//     }

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

//     // Filter berdasarkan pencarian
//     $search = $request->input('search');
//     if ($search) {
//         $query->where(function($q) use ($search) {
//             $q->where('nama', 'like', "%{$search}%")
//               ->orWhereHas('cabang', function ($subQ) use ($search) {
//                   $subQ->where('nama_cabang', 'like', "%{$search}%");
//               })
//               ->orWhereHas('kantorkas', function ($subQ) use ($search) {
//                   $subQ->where('nama_wilayah', 'like', "%{$search}%");
//               });
//         });
//         Log::info('Search Filter:', ['search' => $search]);
//     }

//     // Filter berdasarkan cabang, jika ada filter tambahan
//     $cabangFilter = $request->input('cabang_filter');
//     if ($cabangFilter) {
//         $query->where('id_cabang', $cabangFilter);
//         Log::info('Filter by Cabang:', ['id_cabang' => $cabangFilter]);
//     }

//     // Filter based on kantorkas
//     $wilayahFilter = $request->input('wilayah_filter');
//     if ($wilayahFilter) {
//         $query->where('id_kantorkas', $wilayahFilter);
//         Log::info('Filter by Kantor Kas:', ['id_kantorkas' => $wilayahFilter]);
//     }

//     // Filter based on AO
//     $aoFilter = $request->input('ao_filter');
//     if ($aoFilter) {
//         $query->whereHas('accountOfficer', function ($q) use ($aoFilter) {
//             $q->where('name', $aoFilter);
//         });
//         Log::info('Filter by Account Officer:', ['name' => $aoFilter]);
//     }

//     // Execute the query
//     $nasabahs = $query->get();
//     Log::info('Nasabah Results:', ['count' => $nasabahs->count()]);

//     $nasabahNames = Nasabah::pluck('nama', 'no');
//     $suratPeringatans = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama')
//         ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
//         ->get()
//         ->sortByDesc('tingkat');
//     Log::info('Surat Peringatan Results:', ['count' => $suratPeringatans->count()]);

//     $cabangs = Cabang::all();
//     $kantorkas = KantorKas::all();

//     return view('supervisor.dashboard', compact('title', 'accountOfficers', 'aocabang', 'nasabahs', 'suratPeringatans', 'cabangs', 'kantorkas', 'currentUser', 'nasabahNames'));
// }

public function dashboard(Request $request)
{
    Log::info('Memasuki fungsi dashboard');

    $title = "Dashboard";
    $currentUser = auth()->user(); // Get the current logged-in user
    $idKantorKasUser = $currentUser->id_kantorkas; // Get the id_kantorkas of the logged-in user
    $idCabangUser = $currentUser->id_cabang; // Get the id_cabang of the logged-in user

    // Retrieve account officers with jabatan_id = 5
    $accountOfficers = User::where('jabatan_id', 5)
    ->when($currentUser->id_cabang, function ($query) use ($currentUser) {
        return $query->where('id_cabang', $currentUser->id_cabang);
    })
    ->when($currentUser->id_kantorkas, function ($query) use ($currentUser) {
        return $query->where('id_kantorkas', $currentUser->id_kantorkas);
    })
    ->get();

    // Initial query for nasabahs, filtered by id_kantorkas and id_cabang of the logged-in user
    $query = Nasabah::with('accountOfficer', 'adminKas', 'cabang', 'kantorkas')
        ->where('id_kantorkas', $idKantorKasUser) // Filter by id_kantorkas of the logged-in user
        ->where('id_cabang', $idCabangUser);

    // Log query awal
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

    // Log query setelah filter tanggal
    Log::info('Query setelah filter tanggal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan pencarian
    $search = $request->input('search');
    if ($search) {
        $query->where('nama', 'like', "%{$search}%")
              ->orWhereHas('cabang', function ($q) use ($search) {
                  $q->where('nama_cabang', 'like', "%{$search}%");
              })
              ->orWhereHas('kantorkas', function ($q) use ($search) {
                  $q->where('nama_wilayah', 'like', "%{$search}%");
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

    // Filter based on AO
    $aoFilter = $request->input('ao_filter');
    if ($aoFilter) {
    $query->whereHas('accountOfficer', function ($q) use ($aoFilter, $currentUser) {
        $q->where('name', $aoFilter)
          ->where('id_cabang', $idCabangUser) // Filter berdasarkan id_cabang pengguna
          ->where('id_kantorkas', $idKantorKasUser); // Filter berdasarkan id_kantorkas pengguna
    });
        Log::info('Filter by Account Officer:', ['name' => $aoFilter]);
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

    return view('supervisor.dashboard', compact('title', 'accountOfficers', 'nasabahs', 'suratPeringatans', 'cabangs', 'kantorkas', 'currentUser', 'nasabahNames'));
}

public function show($no)
{
    $nasabah = Nasabah::with([
        'cabang',
        'kantorkas',
        'kunjungan',
        'account_officer',
        'admin_kas'
    ])->where('no', $no)->firstOrFail();

    // Get 5 most recent visits
    $kunjunganTerbaru = Kunjungan::where('no_nasabah', $nasabah->user_id)
        ->orderBy('tanggal', 'desc')
        ->take(5)
        ->get();

    // Get all visits
    $kunjunganSemua = Kunjungan::where('no_nasabah', $nasabah->user_id)
        ->orderBy('tanggal', 'desc')
        ->get();

    return response()->json([
        'nasabah' => $nasabah,
        'kunjunganTerbaru' => $kunjunganTerbaru,
        'kunjunganSemua' => $kunjunganSemua
    ]);
}

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
            'ttd' => 'required|date',
            'kembali' => 'required|date',
            'id_cabang' => 'required|integer',
            'id_kantorkas' => 'required|integer',
            'id_account_officer' => 'required|integer'
        ]);

        $nasabah = Nasabah::where('no', $no)->firstOrFail();
        $nasabah->update($request->all());

        return redirect()->route('direksi.dashboard')->with('success', 'Data berhasil di update');
    }

public function deleteNasabah($no)
{
    Nasabah::find($no)->delete();
    return redirect()->route('direksi.dashboard')->with('success', 'Data berhasil di hapus');
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
        'tingkat' => 'required',
        'scan_pdf' => 'required|mimes:pdf|max:2048'
    ]);

    Log::info('Data passed validation', $request->all());

    try {
        $suratData = $request->only(['no', 'tingkat', 'dibuat','kembali']);

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

        $accountOfficerId = auth()->user()->id;

        // Save the Surat Peringatan
        SuratPeringatan::create([
            'no' => $suratData['no'],
            'tingkat' => $suratData['tingkat'],
            'dibuat' => $suratData['dibuat'],
            'kembali' => $suratData['kembali'],
            'scan_pdf' => $suratData['scan_pdf'],
            'id_account_officer' => $accountOfficerId,
        ]);

        Log::info('Surat Peringatan added successfully', $suratData);

        return redirect()->route('direksi.dashboard')->with('success', 'Data berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Surat Peringatan: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return redirect()->back()->with('error', 'Failed to add data');
    }
}
public function cetakPdf(Request $request)
{
    $accountOfficer = User::where('jabatan_id', 5)->get();
    $query = SuratPeringatan::with('nasabah', 'accountOfficer');
    $query->whereHas('nasabah', function ($q) {
        $q->where('id_cabang', auth()->user()->id_cabang);
    });
    $query->whereHas('nasabah', function ($q) {
        $q->where('id_kantorkas', auth()->user()->id_kantorkas);
    });
    $title = 'Laporan Surat Peringatan';

    // Terapkan filter dari request
    if ($request->has('search')) {
        $search = $request->input('search');
        $query->whereHas('nasabah', function ($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%');
        });
    }
    if ($request->has('ao_filter')) {
        $aoFilter = $request->input('ao_filter');
        $query->whereHas('accountOfficer', function ($query) use ($aoFilter) {
            $query->where('name', $aoFilter);
        });
    }
    $suratPeringatans = $query->get();

    // Handle jika tidak ada data yang ditemukan
    if ($suratPeringatans->isEmpty()) {
        return redirect()->back()->with('error', 'Tidak ada data surat peringatan yang ditemukan.');
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $logoPath = public_path('logobank.png'); 
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml(view('surat_peringatan_pdf', compact('suratPeringatans','title','logoSrc'))->render());
    $dompdf->render();
    return $dompdf->stream('surat_peringatan_hasil_pencarian.pdf');
}

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

        Log::info('Nasabah added successfully', $nasabahData);
        

        return redirect()->route('direksi.dashboard')->with('success', 'Data berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Nasabah: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to add data']);
    }
}

public function getRecentVisits($no)
{
    try {
        $visits = Kunjungan::where('no_nasabah', $no)
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get()
            ->map(function ($visit) {
                // Add full URL for image if it exists
                if ($visit->bukti_gambar) {
                    $visit->bukti_gambar = asset('storage/' . $visit->bukti_gambar);
                }
                return $visit;
            });

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching recent visits'
        ], 500);
    }
}

public function getAllVisits($no)
{
    try {
        $visits = Kunjungan::where('no_nasabah', $no)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($visit) {
                // Add full URL for image if it exists
                if ($visit->bukti_gambar) {
                    $visit->bukti_gambar = asset('storage/' . $visit->bukti_gambar);
                }
                return $visit;
            });

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching all visits'
        ], 500);
    }
}

}
