<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\PegawaiAccountOffice;
use App\Models\SuratPeringatan;
use App\Models\Cabang;
use App\Models\KantorKas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class AccountOfficerController extends Controller
{
    public function dashboard(Request $request)
{
    Log::info('Memasuki fungsi dashboard');

    $title = "Dashboard";
    $currentUser = auth()->user();


    // Retrieve account officers with jabatan_id = 5
    $accountOfficers = User::where('jabatan_id', 5)->get();  // Change pluck to get to retrieve the full user objects

    
    $query = Nasabah::with('accountOfficer','adminKas','cabang','kantorkas');

    // Log query awal
    Log::info('Query awal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan diserahkan
    if ($request->has('date_filter')) {
        $dateFilter = $request->input('date_filter');
        Log::info('Filter diserahkan diterapkan', ['date_filter' => $dateFilter]);

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

    // Log query setelah filter diserahkan
    Log::info('Query setelah filter diserahkan: ', ['query' => $query->toSql()]);

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
    // $nasabahNames = Nasabah::pluck('nama', 'no');
    $nasabahNames = SuratPeringatan::where('id_account_officer', $currentUser->id)
    ->whereNull('bukti_gambar')
    ->with('nasabah') // Memuat relasi nasabah
    ->get()
    ->pluck('nasabah.nama', 'no');


    $suratPeringatans = SuratPeringatan::where('id_account_officer', $currentUser->id)
    ->select('id_peringatan', 'kategori', 'no', 'tingkat', 'diserahkan', 'bukti_gambar', 'scan_pdf')
    ->latest('diserahkan')
    ->get();
    $cabangs = Cabang::all();
    $kantorkas = KantorKas::all();

    return view('account-officer.dashboard', compact('title', 'accountOfficers','nasabahs', 'suratPeringatans', 'cabangs', 'kantorkas', 'currentUser', 'nasabahNames'));
}
public function editNasabah($no)
{
    $nasabah = Nasabah::find($no);
    return response()->json($nasabah);
}

public function update(Request $request, $no)
    {
        $request->validate([
            'nama'=> 'required',
            'tingkat' => 'required|integer|in:1,2,3',
            'diserahkan' => 'required|date',
            'bukti_gambar' => 'required',
            'scan_pdf' => 'required'
        ]);

        $nasabah = Nasabah::where('no', $no)->firstOrFail();
        $nasabah->update($request->all());

        return redirect()->route('account-officer.dashboard')->with('success', 'Data berhasil di update');
    }

public function deleteNasabah($id_peringatan)
{
    SuratPeringatan::find($id_peringatan)->delete();
    return redirect()->route('account-officer.dashboard')->with('success', 'Data berhasil di hapus');
}

public function detailNasabah($id_peringatan)
{
    $nasabah = SuratPeringatan::find($id_peringatan);
    return response()->json($nasabah);
}

public function addNasabah(Request $request)
{
    Log::info('Add Nasabah request received', $request->all());

    $request->validate([
        'nama' => 'required',
        'tingkat' => 'required',
        'bukti_gambar' => 'required|image',
    ]);

    try {
        $nasabahData = $request->only(['nama', 'tingkat', 'diserahkan']);

        Log::info('File upload attempt', [
            'has_file' => $request->hasFile('bukti_gambar'),
            'is_valid' => $request->hasFile('bukti_gambar') ? $request->file('bukti_gambar')->isValid() : 'N/A',
            'file_info' => $request->hasFile('bukti_gambar') ? $request->file('bukti_gambar') : 'No file'
        ]);

        // Handle the image upload for 'bukti_gambar'
        if ($request->hasFile('bukti_gambar')) {
            $buktiGambarPath = $request->file('bukti_gambar')->store('bukti_gambar', 'public');
            $nasabahData['bukti_gambar'] = $buktiGambarPath;
        }

        Log::info('File upload result', [
        'path' => $buktiGambarPath ?? 'No path (upload likely failed)'
        ]);

        // Retrieve the Nasabah by name
        $nasabah = Nasabah::where('nama', $nasabahData['nama'])->first();

        if (!$nasabah) {
            return redirect()->back()->with('error', 'Nasabah not found.');
        }


        // Check if the falready exists
        $suratPeringatan = SuratPeringatan::where('no', $nasabah->no)
            ->where('tingkat', $nasabahData['tingkat'])
            ->first();

        if (!$suratPeringatan) {
            return redirect()->back()->with('error', "Surat Peringatan with Tingkat {$nasabahData['tingkat']} not found for this Nasabah.");
        }

        $accountOfficerId = auth()->user()->id;
        
        // Update the existing Surat Peringatan
        $suratPeringatan->update([
            'diserahkan' => $nasabahData['diserahkan'],
            'bukti_gambar' => $nasabahData['bukti_gambar'],
            'id_account_officer' => $accountOfficerId,
        ]);

        Log::info('Nasabah updated successfully by Account Officer', $nasabahData);

        return redirect()->route('account-officer.dashboard')->with('success', 'Data berhasil diperbarui');
    } catch (\Exception $e) {
        Log::error('Error updating Nasabah: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return redirect()->back()->with('error', 'Failed to update data');
    }
}
    public function showAddNasabahForm()
    {
        $loggedInAccountOfficerId = auth()->user()->id;

        $nasabahWithPendingSuratPeringatan = SuratPeringatan::whereNull('bukti_gambar')
            ->where('id_account_officer', $loggedInAccountOfficerId)
            ->join('nasabah', 'surat_peringatan.no', '=', 'nasabah.no')
            ->select('nasabah.no', 'nasabah.nama', 'surat_peringatan.tingkat')
            ->get()
            ->groupBy('nama')
            ->map(function ($group) {
                return $group->pluck('tingkat', 'no')->toArray();
            });

        return view('account_officer.dashboard', compact('nasabahSurat'));
    }

}
