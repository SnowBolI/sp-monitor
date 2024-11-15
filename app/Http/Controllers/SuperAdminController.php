<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Nasabah;
use App\Models\PegawaiAccountOffice;
use App\Models\SuratPeringatan;
use App\Models\Cabang;
use App\Models\Key;
use App\Models\Jabatan;
use App\Models\Status;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KeysImport;
use App\Models\KantorKas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class SuperAdminController extends Controller
{
    public function dashboard(Request $request)
{
    Log::info('Memasuki fungsi dashboard');

    $title = "Dashboard";
    Log::info('Title set: ' . $title);

    // Retrieve account officers with jabatan_id = 5
    $accountOfficers = User::where('jabatan_id', 5)->get();
    Log::info('Account Officers retrieved: ', ['count' => $accountOfficers->count()]);

    // Dapatkan ID admin kas yang sedang login
    $currentUser = auth()->user();
    $adminKasId = $currentUser->id;
    Log::info('Current User ID: ', ['adminKasId' => $adminKasId]);

    // Memulai query dengan relasi yang diperlukan dan filter berdasarkan ID admin kas
    $query = User::with('jabatan', 'cabang', 'kantorkas','infostatus');

    Log::info('Query awal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan tanggal (jika diperlukan)
    if ($request->has('date_filter')) {
        $dateFilter = $request->input('date_filter');
        Log::info('Filter tanggal diterapkan', ['date_filter' => $dateFilter]);

        switch ($dateFilter) {
            case 'last_7_days':
                $query->where('created_at', '>=', now()->subDays(7));
                Log::info('Filter last 7 days diterapkan');
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                Log::info('Filter last 30 days diterapkan');
                break;
            case 'last_month':
                $query->whereMonth('created_at', '=', now()->subMonth()->month);
                Log::info('Filter last month diterapkan');
                break;
            case 'last_year':
                $query->whereYear('created_at', '=', now()->subYear()->year);
                Log::info('Filter last year diterapkan');
                break;
        }
    }

    Log::info('Query setelah filter tanggal: ', ['query' => $query->toSql()]);

    // Filter berdasarkan pencarian
    $search = $request->input('search');
    if ($search) {
        Log::info('Pencarian diterapkan', ['search' => $search]);
        $query->where('name', 'like', "%{$search}%")
            ->orWhereHas('cabang', function ($q) use ($search) {
                $q->where('nama_cabang', 'like', "%{$search}%");
            })
            ->orWhereHas('kantorkas', function ($q) use ($search) {
                $q->where('nama_kantorkas', 'like', "%{$search}%"); 
            });
    }

    // Filter berdasarkan cabang
    $cabangFilter = $request->input('cabang_filter');
    if ($cabangFilter) {
        Log::info('Filter cabang diterapkan', ['cabang_filter' => $cabangFilter]);
        $query->whereHas('cabang', function ($q) use ($cabangFilter) {
            $q->where('id_cabang', $cabangFilter);
        });
    }

    // Filter berdasarkan kantorkas 
    $wilayahFilter = $request->input('wilayah_filter');
    if ($wilayahFilter) {
        Log::info('Filter kantorkas diterapkan', ['wilayah_filter' => $wilayahFilter]);
        $query->whereHas('kantorkas', function ($q) use ($wilayahFilter) {
            $q->where('id_kantorkas', $wilayahFilter); 
        });
    }
    

    Log::info('Query setelah filter cabang dan kantorkas: ', ['query' => $query->toSql()]);

    // Dapatkan nilai 'per_page' dari request, atau gunakan 20 sebagai default
    $perPage = $request->input('per_page') ?: null;
    
    // Paginate the results 
    $users = $perPage ? $query->paginate($perPage) : $query->get(); 

    Log::info('Users retrieved: ', ['count' => $users->count()]);

    $cabangs = Cabang::all();
    Log::info('Cabangs retrieved: ', ['count' => $cabangs->count()]);
    $kantorkas = KantorKas::all();
    Log::info('Wilayahs retrieved: ', ['count' => $kantorkas->count()]);
    $jabatans = Jabatan::all();
    Log::info('Jabatans retrieved: ', ['count' => $jabatans->count()]);
    $statuses = Status::all(); 
    Log::info('Statuses retrieved: ', ['count' => $statuses->count()]);

    // Mengambil semua data user (tanpa filter)
    $allUsers = User::all(); 
    Log::info('All users retrieved: ', ['count' => $allUsers->count()]);

    return view('super-admin.dashboard', compact('title', 'accountOfficers','statuses', 'jabatans', 'users','allUsers', 'cabangs', 'kantorkas', 'currentUser')); 
}

public function tampilkanCabang()
{
    $title = 'Cabang Admin';
    $cabangs = Cabang::all(); 
    $kantorkas = KantorKas::all();
    return view('super-admin.cabang', compact('cabangs','kantorkas','title'));
}

public function addCabang(Request $request)
{
    Log::info('Add Cabang request received', $request->all());

    $request->validate([
        'nama_cabang' => 'required|max:255', // Validasi hanya untuk nama_cabang
    ]);

    try {
        // Buat array baru hanya dengan data yang diperlukan
        $cabangData = [
            'nama_cabang' => $request->input('nama_cabang')
        ];

        Cabang::create($cabangData); // Insert data ke tabel cabang

        Log::info('Cabang added successfully', $cabangData);

        return redirect()->route('super-admin.cabang')->with('success', 'Cabang berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Cabang: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to add cabang']); // Pesan error yang lebih spesifik
    }
}

public function deleteCabang($id_cabang)
{
    Cabang::find($id_cabang)->delete();
    return redirect()->route('super-admin.cabang')->with('success', 'Data berhasil di hapus');
}
public function tampilkanKantorKas()
{
    $cabangs = Cabang::all();
    $title = 'Admin Kantorkas';
    $kantorkas = KantorKas::all(); 
    return view('super-admin.kantorkas', compact('kantorkas','title','cabangs'));
}
// public function edit(Request $request, $id)

//     // Ambil user dan cek validated user
//     $validatedUser = Auth::user(); // Atau metode lain sesuai validasi yang digunakan
//     $user = User::findOrFail($id); // Ambil user berdasarkan ID

//     if ($validatedUser) {
//         Log::info('Validated user ditemukan: ', ['validated_user' => $validatedUser]);

//         $userId = $user->id;
//         $jabatanId = $user->id_jabatan;

//         if ($jabatanId == 4) { // Admin Kas
//             // Cari nasabah yang memiliki id_admin_kas sesuai dengan user ID
//             $nasabah = Nasabah::where('id_admin_kas', $userId)->first();
//             if ($nasabah) {
//                 $nasabah->id_admin_kas = $validatedUser->id; // Ganti dengan ID validated user
//                 $nasabah->save();
//                 Log::info('Nasabah id_admin_kas updated', ['nasabah' => $nasabah]);
//             }
//         } elseif ($jabatanId == 5) { // Account Officer
//             // Cari nasabah yang memiliki id_account_officer sesuai dengan user ID
//             $nasabah = Nasabah::where('id_account_officer', $userId)->first();
//             if ($nasabah) {
//                 $nasabah->id_account_officer = $validatedUser->id; // Ganti dengan ID validated user
//                 $nasabah->save();
//                 Log::info('Nasabah id_account_officer updated', ['nasabah' => $nasabah]);
//             }
//         }
//     } else {
//         Log::info('Tidak ada validated user, tidak ada perubahan pada nasabah.');
//     }

//     // Lanjutkan update user sesuai request
//     $user->update($request->all());

//     return response()->json(['message' => 'User berhasil diupdate', 'user' => $user]);
// }

public function edit($id)
{
    Log::info('Memasuki fungsi edit', ['user_id' => $id]);

    $user = User::with('jabatan:id_jabatan,nama_jabatan', 'cabang:id_cabang,nama_cabang', 'kantorkas:id_kantorkas,nama_kantorkas','infostatus:id,nama_status')->findOrFail($id);
    Log::info('User found for editing: ', ['user' => $user]);

    $jabatans = Jabatan::all();
    $cabangs = Cabang::all();
    $kantorkas = KantorKas::all();
    $statuses = Status::all();

    // return view('super-admin.dashboard', compact('user', 'jabatans', 'cabangs', 'kantorkas', 'statuses')); // Ganti 'super-admin.dashboard' dengan nama view yang sesuai
    return response()->json($user);
}

    public function addKantorkas(Request $request)
{
    Log::info('Add Kantorkas request received', $request->all());

    $request->validate([
        'nama_kantorkas' => 'required|max:255', // Validasi hanya untuk nama_cabang
    ]);

    try {
        // Buat array baru hanya dengan data yang diperlukan
        $kantorkasData = [
            'nama_kantorkas' => $request->input('nama_kantorkas')
        ];

        KantorKas::create($kantorkasData); // Insert data ke tabel cabang

        Log::info('Kantorkas added successfully', $kantorkasData);

        return redirect()->route('super-admin.kantorkas')->with('success', 'Kantorkas berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Cabang: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to add kantorkas']); // Pesan error yang lebih spesifik
    }
}

public function deleteKantorkas($id_kantorkas)
{
    KantorKas::find($id_kantorkas)->delete();
    return redirect()->route('super-admin.kantorkas')->with('success', 'Data berhasil di hapus');
}

public function tampilkanKey()
{
    $title = 'Keys Admin';
    $keys = Key::with('jabatannama')->whereNull('key_status')->get(); 
    $kantorkas = KantorKas::all();
    do {
        $uniqueKey = rand(100000, 999999); 
    } while (Key::where('key', $uniqueKey)->exists());
    
    Log::info($keys); 

    return view('super-admin.keys', compact('keys','kantorkas','title','uniqueKey'));
}

public function addKey(Request $request)
{
    Log::info('Add Key request received', $request->all());

    $request->validate([
        'key' => 'required|max:255',
        'jabatan' =>'required|max:255'
    ]);

    try {
        // Generate nomor unik (hanya angka acak 6 digit)
        do {
            $uniqueKey = rand(100000, 999999); 
        } while (Key::where('key', $uniqueKey)->exists()); 

        $keyData = [
            'key' => $uniqueKey,
            'jabatan' => $request->input('jabatan')
        ];

        Key::create($keyData); 

        Log::info('Key added successfully', $keyData);

        return redirect()->route('super-admin.key')->with('success', 'Key berhasil ditambahkan');
    } catch (\Exception $e) {
        Log::error('Error adding Key: ' . $e->getMessage(), [
            'request' => $request->all(),
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json(['error' => 'Failed to add key']); // Pesan error yang lebih spesifik
    }
}

public function deleteKey($key)
{
    Key::find($key)->delete();
    return redirect()->route('super-admin.key')->with('success', 'Data berhasil di hapus');
}


public function importKeys(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    Excel::import(new KeysImport, $request->file('file'));
    return redirect()->back()->with('success', 'Data berhasil diimport!');
}


    public function update(Request $request, $id)
    {
        Log::info('Memasuki fungsi update', ['user_id' => $id]);

        // Validasi data input jika diperlukan
        $validatedData = $request->validate([
            'name' => 'required',
            'jabatan_id' => 'required',
            'id_user'=> 'nullable',
            'id_cabang' => 'required',
            'id_kantorkas' => 'required',
        ]);
        Log::info('Data tervalidasi: ', ['validated_data' => $validatedData]);

        $user = User::findOrFail($id);
        Log::info('User found for updating: ', ['user' => $user]);
        if (!$user) {
            Log::warning('User not found', ['id' => $id]);
            return response()->json(['message' => 'User not found'], 404);
        }
    
        Log::info('User found', ['user' => $user]);
    
        // Cek apakah validated user ada
        if (isset($validatedData['id_user'])) {
            // Cari pengguna berdasarkan jabatan
            switch ($user->jabatan_id) {
                case 4:
                    // Update semua nasabah dengan id_admin_kas yang cocok
                    $updatedRowsNasabah = Nasabah::where('id_admin_kas', $id)
                        ->update(['id_admin_kas' => $validatedData['id_user']]);
                    
                    Log::info("Nasabah id_admin_kas updated for {$updatedRowsNasabah} rows", ['new_id_admin_kas' => $validatedData['id_user']]);
                    break;
        
                case 5:
                    // Update semua nasabah dengan id_account_officer yang cocok
                    $updatedRowsNasabah = Nasabah::where('id_account_officer', $id)
                        ->update(['id_account_officer' => $validatedData['id_user']]);
                    
                    Log::info("Nasabah id_account_officer updated for {$updatedRowsNasabah} rows", ['new_id_account_officer' => $validatedData['id_user']]);
        
                    // Update semua SuratPeringatan dengan id_account_officer yang cocok
                    $updatedRowsSuratPeringatan = SuratPeringatan::where('id_account_officer', $id)
                        ->update(['id_account_officer' => $validatedData['id_user']]);
        
                    Log::info("SuratPeringatan id_account_officer updated for {$updatedRowsSuratPeringatan} rows", ['new_id_account_officer' => $validatedData['id_user']]);
                    break;
        
                default:
                    Log::info('No action needed for this jabatan', ['jabatan_id' => $user->jabatan_id]);
                    break;
            }
        }
        
    

        $user->update($request->all());
        Log::info('User updated successfully', ['user' => $user]);

        return redirect()->route('super-admin.dashboard')->with('success', 'Data pengguna berhasil diperbarui!');
    }
}