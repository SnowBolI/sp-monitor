<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MobileKunjunganController extends Controller
{
    public function getKunjunganList($noNasabah)
    {
        try {
            // Log ketika fungsi mulai dijalankan
            Log::info("Mengambil daftar kunjungan untuk nasabah dengan nomor: $noNasabah");
            
            $kunjunganList = Kunjungan::where('no_nasabah', $noNasabah)
                ->orderBy('tanggal', 'desc') // Urutkan berdasarkan tanggal terbaru
                ->take(10) // Ambil 10 data terbaru
                ->get(); // Gunakan get() untuk mengambil data
            
            // Log jumlah data yang ditemukan
            Log::info("Jumlah data kunjungan ditemukan: " . $kunjunganList->count());

            return response()->json($kunjunganList);

        } catch (\Exception $e) {
            // Log error jika terjadi masalah
            Log::error("Error saat mengambil data kunjungan: " . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function serveImage($filename)
    {
        // $path = storage_path('app/private/kunjungan/' . $filename);
        $path = storage_path('app/public/kunjungan/' . $filename);
        if (file_exists($path)) {
            Log::info("Serving image from path: " . $path);
            return response()->file($path);
        } else {
            Log::error("Image not found at path: " . $path);
            return response()->json(['error' => 'Image not found'], 404);
        }
    }
    public function tambahKunjungan(Request $request)
    {
        // Log data yang diterima dari permintaan
        Log::info('Data kunjungan yang diterima:', $request->all());
    
        // Validasi input
        $validated = $request->validate([
            'no_nasabah' => 'required',
            'tanggal' => 'required|date_format:Y-m-d H:i:s',
            'koordinat' => 'required|string',
            'keterangan' => 'required|string',
            'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);
    
        // Cari nasabah berdasarkan no_nasabah
        $nasabah = Nasabah::where('no', $validated['no_nasabah'])->first();
    
        if (!$nasabah) {
            Log::warning('Nasabah tidak ditemukan', ['no_nasabah' => $validated['no_nasabah']]);
            return response()->json(['error' => 'Nasabah tidak ditemukan'], 404);
        }
    
        // Ambil id_account_officer dari nasabah yang ditemukan
        $userId = $nasabah->id_account_officer;
    
        // Log info tentang nasabah yang ditemukan
        Log::info('Nasabah ditemukan untuk penambahan kunjungan', ['no_nasabah' => $nasabah->no, 'id_account_officer' => $userId]);
    
        // Buat data kunjungan baru
        $kunjungan = new Kunjungan();
        $kunjungan->user_id = $userId;
        $kunjungan->no_nasabah = $validated['no_nasabah']; 
        $kunjungan->tanggal = $validated['tanggal'];
        $kunjungan->koordinat = $validated['koordinat'];
        $kunjungan->keterangan = $validated['keterangan'];
    
        // Jika ada file bukti gambar yang diunggah, simpan gambar dan path-nya
        if ($request->hasFile('bukti_gambar')) {
            $buktiGambarPath = $request->file('bukti_gambar')->store('kunjungan', 'public');
            $kunjungan->bukti_gambar = $buktiGambarPath;
    
            Log::info('Bukti gambar berhasil diunggah', ['bukti_gambar' => $buktiGambarPath]);
        }
    
        // Simpan data kunjungan
        $kunjungan->save();
    
        // Log info setelah kunjungan berhasil ditambahkan
        Log::info('Kunjungan berhasil ditambahkan', ['kunjungan_id' => $kunjungan->id, 'no_nasabah' => $kunjungan->no_nasabah]);


        // Respons sukses setelah menambahkan kunjungan
        return response()->json(['message' => 'Kunjungan berhasil ditambahkan', 'kunjungan' => $kunjungan], 201);

    }
    


    public function getNasabahKunjungan()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Untuk Account Officer
        $idUser = $user->id;
        Log::info('Jabatan: account_officer - fetching nasabah kunjungan for account_officer', ['id_account_officer' => $idUser]);

        // Mengambil data Nasabah berdasarkan id_account_officer yang sesuai dengan id user yang login
        $nasabahList = Nasabah::select('nasabahs.*')
                            ->where('nasabahs.id_account_officer', $idUser)
                            ->get();

        if ($nasabahList->isEmpty()) {
            Log::warning('Tidak ada nasabah yang ditemukan untuk user:', ['id_account_officer' => $idUser]);
            return response()->json(['error' => 'Tidak ada nasabah untuk account officer ini'], 404);
        }

        // Logging data nasabah yang ditemukan
        Log::info('Data nasabah yang ditemukan:', ['nasabah' => $nasabahList]);

        // Kembalikan data nasabah
        return response()->json($nasabahList->values()->all(), 200);
    }

    
}

