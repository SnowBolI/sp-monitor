<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use App\Models\SuratPeringatan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MobileSuratPeringatanController extends Controller
{

    public function getNasabahSP()
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        // Untuk Account Officer
        $idUser = $user->id;
        Log::info('Jabatan: account_officer - fetching Surat Peringatan for account_officer', ['id_account_officer' => $idUser]);
    
        // Mengambil semua data SuratPeringatan yang bukti_gambar masih kosong
        $suratPeringatanRaw = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama', 'surat_peringatans.kategori', 'surat_peringatans.tingkat')
            ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
            ->where('surat_peringatans.id_account_officer', $idUser)
            ->whereNotNull('surat_peringatans.no')
            ->whereNull('surat_peringatans.bukti_gambar')
            ->get();
    
        // Inisialisasi array untuk hasil akhir
        $filteredSP = [];
    
        // Mengelompokkan data berdasarkan nasabah
        $groupedByNasabah = $suratPeringatanRaw->groupBy('nama');
    
        foreach ($groupedByNasabah as $nasabahName => $suratNasabah) {
            // Kelompokkan data berdasarkan kategori
            $groupedByCategory = $suratNasabah->groupBy('kategori');
    
            // Logika prioritas: Peringatan > Somasi > Pendampingan
            $priorities = ['Peringatan', 'Somasi', 'Pendampingan'];
    
            foreach ($priorities as $priorityCategory) {
                // Jika ada surat untuk kategori saat ini (misalnya Peringatan, Somasi, Pendampingan)
                if (isset($groupedByCategory[$priorityCategory])) {
                    // Cari surat dengan tingkat terendah di kategori ini
                    $suratTingkatTerendah = $groupedByCategory[$priorityCategory]->sortBy('tingkat')->first();
    
                    if ($suratTingkatTerendah) {
                        // Masukkan ke dalam array filteredSP dan lanjutkan ke nasabah berikutnya
                        $filteredSP[] = $suratTingkatTerendah;
                        break; // Hanya tampilkan satu surat per nasabah
                    }
                }
            }
        }
    
        // Mengembalikan hasil
        return response()->json(array_values($filteredSP), 200);
    }
    // public function getNasabahSP()
    // {
    //     $user = Auth::user();
        
    //     if (!$user) {
    //         return response()->json(['error' => 'User not authenticated'], 401);
    //     }
    
    //     if ($user->jabatan_id == 99) {
    //         Log::info('Jabatan: admin - fetching all Surat Peringatan');
            
    //         // Mengambil data SuratPeringatan dan menambahkan nama nasabah langsung ke objek
    //         $suratPeringatan = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama')
    //                             ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
    //                             ->whereNotNull('surat_peringatans.no')
    //                             ->whereNull('surat_peringatans.bukti_gambar')
    //                             ->get();
    //         return response()->json($suratPeringatan, 200);
    //     }
    
    //     if ($user) {
    //         $idUser = $user->id;
    //         Log::info('Jabatan: account_officer - fetching Surat Peringatan for account_officer', ['id_account_officer' => $idUser]);
    
    //         // Mengambil data SuratPeringatan dan menambahkan nama nasabah langsung ke objek
    //         $suratPeringatanRaw = SuratPeringatan::select('surat_peringatans.*', 'nasabahs.nama')
    //                             ->join('nasabahs', 'surat_peringatans.no', '=', 'nasabahs.no')
    //                             ->where('surat_peringatans.id_account_officer', $idUser)
    //                             ->whereNotNull('surat_peringatans.no')
    //                             ->whereNull('surat_peringatans.bukti_gambar')
    //                             ->get();
            
    
    //         Log::info('Jabatan: account_officer - fetching Surat Peringatan for account_officer', ['id_account_officer' => $suratPeringatanRaw]);
    
    //         $suratPeringatan = $suratPeringatanRaw->unique('nama');
        
    //     return response()->json($suratPeringatan->values()->all(), 200);
    //     } else {
    //         return response()->json(['error' => 'Account Officer not found for this user'], 403);
    //     }
    // }
    
    
    public function updateSuratPeringatan(Request $request)
    {
        try {
            // Log data yang diterima
            Log::info('Data yang diterima untuk update SuratPeringatan: ' . json_encode($request->all()));

            $validated = $request->validate([
                'no' => 'required|integer',
                'tingkat' => 'required|integer',
                'diserahkan' => 'required|date',
                'kategori' => 'required|string|in:Peringatan,Somasi,Pendampingan', // Validasi kategori
                'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validate image file
            ]);

            // Cari SuratPeringatan berdasarkan no, tingkat, dan kategori
            $suratPeringatan = SuratPeringatan::where('no', $validated['no'])
                ->where('tingkat', $validated['tingkat'])
                ->where('kategori', $validated['kategori']) // Tambahkan kategori ke pencarian
                ->first();

            if (!$suratPeringatan) {
                Log::error('Surat ' . $validated['kategori'] . ' tidak ditemukan untuk No: ' . $validated['no'] . ', Tingkat: ' . $validated['tingkat']);
                return response()->json(['error' => 'Surat ' . $validated['kategori'] . ' belum tersedia untuk tingkat ini'], 404);
            }

            // Cek apakah sudah ada gambar
            if (!is_null($suratPeringatan->bukti_gambar)) {
                Log::warning('Gambar sudah ada untuk Surat ' . $validated['kategori'] . ' No: ' . $validated['no'] . ', Tingkat: ' . $validated['tingkat']);
                return response()->json(['error' => 'Surat ' . $validated['kategori'] . ' sudah ada untuk tingkat ini'], 400);
            }

            // Update tanggal dan kategori
            $suratPeringatan->diserahkan = $validated['diserahkan'];
            $suratPeringatan->kategori = $validated['kategori'];

            if ($request->hasFile('bukti_gambar')) {
                $buktiGambarPath = $request->file('bukti_gambar')->store('bukti_gambar', 'public');
                $suratPeringatan->bukti_gambar = $buktiGambarPath;
                Log::info('File gambar berhasil diupdate untuk Surat ' . $validated['kategori']);
            }

            // Simpan perubahan
            $suratPeringatan->save();

            Log::info('Surat ' . $validated['kategori'] . ' berhasil diupdate: ' . json_encode($suratPeringatan));

            return response()->json($suratPeringatan, 200);
        } catch (ValidationException $e) {
            Log::error('Validasi gagal: ' . $e->getMessage());
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengupdate surat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengupdate surat.'], 500);
        }
    }

    // public function updateSuratPeringatan(Request $request)
    // {
    //     try {
    //         // Log data yang diterima
    //         Log::info('Data yang diterima untuk update SuratPeringatan: ' . json_encode($request->all()));

    //         $validated = $request->validate([
    //             'no' => 'required|integer',
    //             'tingkat' => 'required|integer',
    //             'diserahkan' => 'required|date',
    //             'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Validate image file
    //         ]);

    //         // Cari SuratPeringatan berdasarkan no dan tingkat
    //         $suratPeringatan = SuratPeringatan::where('no', $validated['no'])
    //             ->where('tingkat', $validated['tingkat'])
    //             ->first();

    //         if (!$suratPeringatan) {
    //             Log::error('SuratPeringatan tidak ditemukan untuk No: ' . $validated['no'] . ', Tingkat: ' . $validated['tingkat']);
    //             return response()->json(['error' => 'Surat Peringatan belum tersedia untuk tingkat ini'], 404);
    //         }

    //         // Cek apakah sudah ada gambar
    //         if (!is_null($suratPeringatan->bukti_gambar)) {
    //             Log::warning('Gambar sudah ada untuk SuratPeringatan No: ' . $validated['no'] . ', Tingkat: ' . $validated['tingkat']);
    //             return response()->json(['error' => 'Surat Peringatan sudah ada untuk tingkat ini'], 400);
    //         }

    //         // Update tanggal
    //         $suratPeringatan->diserahkan = $validated['diserahkan'];

    //         // Update gambar jika ada
    //         // if ($request->hasFile('bukti_gambar')) {
    //         //     $buktiGambar = $request->file('bukti_gambar');
    //         //     $buktiGambarName = 'gambar_SP' . $validated['tingkat'] . '_' . $validated['no'] . '.' . $buktiGambar->getClientOriginalExtension();
    //         //     $buktiGambarPath = $buktiGambar->storeAs('private/surat_peringatan', $buktiGambarName);
    //         //     $suratPeringatan->bukti_gambar = $buktiGambarPath;

    //         //     Log::info('File gambar berhasil diupdate: ' . $buktiGambarName);
    //         // }
    //         if ($request->hasFile('bukti_gambar')) {
    //             $buktiGambarPath = $request->file('bukti_gambar')->store('bukti_gambar', 'public');

    //             $suratPeringatan->bukti_gambar = $buktiGambarPath;

    //             Log::info('File gambar berhasil diupdate: ');
    //         }
    //         // Simpan perubahan
    //         $suratPeringatan->save();

    //         Log::info('SuratPeringatan berhasil diupdate: ' . json_encode($suratPeringatan));

    //         return response()->json($suratPeringatan, 200);
    //     } catch (ValidationException $e) {
    //         Log::error('Validasi gagal: ' . $e->getMessage());
    //         return response()->json(['error' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         Log::error('Terjadi kesalahan saat mengupdate surat peringatan: ' . $e->getMessage());
    //         return response()->json(['error' => 'Terjadi kesalahan saat mengupdate surat peringatan.'], 500);
    //     }
    // }



    // public function SuratPeringatan(Request $request)
    // {
    //     try {
    //         // Log data yang diterima
    //         Log::info('Data yang diterima untuk SuratPeringatan: ' . json_encode($request->all()));

    //         $validated = $request->validate([
    //             'no' => 'required|integer',
    //             'tingkat' => 'required|integer',
    //             'tanggal' => 'required|date',
    //             'bukti_gambar' => 'required|image|mimes:jpeg,png,jpg,gif', // Validate image file
    //             // 'scan_pdf' => 'required|mimes:pdf|max:2048', // Validate PDF file
    //             'id_account_officer' => 'required'
    //         ]);

    //         // Log file details before saving
    //         if ($request->hasFile('bukti_gambar')) {
    //             Log::info('bukti_gambar: ' . $request->file('bukti_gambar')->getClientOriginalName() . ', size: ' . $request->file('bukti_gambar')->getSize() . ' bytes');
    //         }
    //         // if ($request->hasFile('scan_pdf')) {
    //         //     Log::info('scan_pdf: ' . $request->file('scan_pdf')->getClientOriginalName() . ', size: ' . $request->file('scan_pdf')->getSize() . ' bytes');
    //         // }

    //         // Ambil tingkat untuk digunakan dalam nama file
    //         $tingkat = $validated['tingkat'];
    //         $namaNasabah = $validated['no'];

    //         // Cek apakah sudah ada tingkat yang lebih rendah yang sudah terisi
    //         for ($i = 1; $i < $tingkat; $i++) {
    //             $existingSuratPeringatan = SuratPeringatan::where('no', $validated['no'])
    //                 ->where('tingkat', $i)
    //                 ->first();

    //             if (!$existingSuratPeringatan) {
    //                 Log::info("Tingkat $i belum diisi untuk Nasabah No: " . $validated['no']);
    //                 return response()->json(['error' => "Tingkat $i belum diisi untuk Nasabah ini. Harap isi tingkat yang lebih rendah terlebih dahulu."], 422);
    //             }
    //         }

    //         // Simpan gambar dan PDF
    //         $buktiGambar = $request->file('bukti_gambar');
    //         // $scanPdf = $request->file('scan_pdf');

    //         // if ($buktiGambar->isValid() && $scanPdf->isValid()) {
    //         //     $buktiGambarName = 'gambar_SP' . $tingkat . '_' . $namaNasabah . '.' . $buktiGambar->getClientOriginalExtension();
    //         //     $scanPdfName = 'pdf_SP' . $tingkat . '_' . $namaNasabah . '.' . $scanPdf->getClientOriginalExtension();
    //          if ($buktiGambar->isValid()) {
    //             $buktiGambarName = 'gambar_SP' . $tingkat . '_' . $namaNasabah . '.' . $buktiGambar->getClientOriginalExtension();
    //             // $scanPdfName = 'pdf_SP' . $tingkat . '_' . $namaNasabah . '.' . $scanPdf->getClientOriginalExtension();
    //             // Cek apakah sudah ada entri dengan nama gambar atau PDF yang sama
    //             $existingSuratPeringatan = SuratPeringatan::where('bukti_gambar', 'like', '%/' . $buktiGambarName)
    //                 ->first();

    //             // if ($existingSuratPeringatan) {
    //             //     Log::info('Data sudah ada untuk gambar atau PDF: ' . $buktiGambarName . ', ' . $scanPdfName);
    //             //     return response()->json(['error' => 'Data sudah ada untuk Nasabah SP ini.'], 422);
    //             // }
    //             if ($existingSuratPeringatan) {
    //                 Log::info('Data sudah ada untuk gambar atau PDF: ' . $buktiGambarName);
    //                 return response()->json(['error' => 'Data sudah ada untuk Nasabah SP ini.'], 422);
    //             }

    //             $buktiGambarPath = $buktiGambar->storeAs('private/surat_peringatan', $buktiGambarName);
    //             // $scanPdfPath = $scanPdf->storeAs('private/surat_peringatan', $scanPdfName);

    //             Log::info('File gambar berhasil disimpan: ' . $buktiGambarName);
    //             // Log::info('File PDF berhasil disimpan: ' . $scanPdfName);
    //         } else {
    //             Log::error('File gambar atau PDF tidak valid');
    //             throw new \Exception('File gambar atau PDF tidak valid');
    //         }

    //         // Buat record surat peringatan
    //         $suratPeringatan = SuratPeringatan::create([
    //             'no' => $validated['no'],
    //             'tingkat' => $validated['tingkat'],
    //             'tanggal' => $validated['tanggal'],
    //             'bukti_gambar' => $buktiGambarPath,
    //             // 'scan_pdf' => $scanPdfPath,
    //             'id_account_officer' => $validated['id_account_officer'],
    //         ]);

    //         Log::info('Surat peringatan berhasil dibuat: ' . json_encode($suratPeringatan));

    //         return response()->json($suratPeringatan, 201);
    //     } catch (ValidationException $e) {
    //         // Log pesan validasi
    //         Log::error('Validasi gagal: ' . $e->getMessage());

    //         return response()->json(['error' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         // Log pesan error umum
    //         Log::error('Terjadi kesalahan saat menyimpan surat peringatan: ' . $e->getMessage());

    //         return response()->json(['error' => 'Terjadi kesalahan saat menyimpan surat peringatan.'], 500);
    //     }
    // }

    public function serveImage($filename)
    {
        // $path = storage_path('app/private/surat_peringatan/' . $filename);
        $path = storage_path('app/public/bukti_gambar/' . $filename);
        if (file_exists($path)) {
            Log::info("Serving image from path: " . $path);
            return response()->file($path);
        } else {
            Log::error("Image not found at path: " . $path);
            return response()->json(['error' => 'Image not found'], 404);
        }
    }
    
    public function servePdf($filename)
    {
        // $path = storage_path('app/private/surat_peringatan/' . $filename);
        $path = storage_path('app/public/scan_pdf/' . $filename);
        if (file_exists($path)) {
            Log::info("Serving PDF from path: " . $path);
            return response()->file($path);
        } else {
            Log::error("PDF not found at path: " . $path);
            return response()->json(['error' => 'PDF not found'], 404);
        }
    }
}