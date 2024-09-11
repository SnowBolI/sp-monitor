<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cabang;
use App\Models\Status;
use App\Models\Jabatan;
use App\Models\KantorKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class MobileAdminController extends Controller
{
    public function getAllData()
    {
        $jabatan = Jabatan::all();
        $cabang = Cabang::all();
        $kantorkas = Kantorkas::all();
        $status = Status::all(); // Assuming you have a Status model for infostatus

        return response()->json([
            'jabatan' => $jabatan,
            'cabang' => $cabang,
            'kantorkas' => $kantorkas,
            'status' => $status
        ]);
    }

    public function getUserAdmin(Request $request)
    {
        Log::info('Request received for getUsers', ['request' => $request->all()]);
        
        $perPage = 15; // Jumlah pengguna per halaman

        // Eager load semua relasi yang diperlukan
        $query = User::with([
            'jabatan',
            'infostatus',
            'cabang',
            'kantorkas',

        ])->orderBy('updated_at', 'desc');//ini

        if ($request->has('search')) {
            $search = $request->search;
            Log::info('Search parameter provided', ['search' => $search]);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')

                    ->orWhereHas('cabang', function($q) use ($search) {
                        $q->where('nama_cabang', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('jabatan', function($q) use ($search) {
                        $q->where('nama_jabatan', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('kantorkas', function($q) use ($search) {
                        $q->where('nama_kantorkas', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        Log::info('Executing query to fetch users');
        $users = $query->paginate($perPage);

        Log::info('Transforming user data to include all relations');
        
        $users->getCollection()->transform(function($user) {
            Log::info('Transforming user', ['user' => $user]);
            Log::info('User status', ['status' => $user->status]);
            $cabang = $user->cabang ? $user->cabang : null;


            $kantorkas = 
                 
                    $user->kantorkas ? $user->kantorkas : null;
          

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'jabatan' => $user->jabatan ? $user->jabatan->nama_jabatan : null,
                'id_jabatan' => $user->jabatan ? $user->jabatan->id_jabatan : null,
                'cabang' => $cabang ? $cabang->nama_cabang : null,
                'id_cabang' => $cabang ? $cabang->id_cabang : null,
                'kantorkas' => $kantorkas ? $kantorkas->nama_kantorkas : null,
                'id_kantorkas' => $kantorkas ? $kantorkas->id_kantorkas : null,
                'status' => $user->infostatus ? $user->infostatus->nama_status : null,
                'status_id' => $user->infostatus ? $user->infostatus->id : null,

            ];
        });

        Log::info('Users fetched successfully', ['users' => $users->toArray()]);
        return response()->json($users->toArray());
    }
    
    public function updateUser(Request $request, $id)
    {
    Log::info('Update user request received', ['id' => $id, 'request_data' => $request->all()]);

        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string',
                'jabatan' => 'required|integer',
                'cabang' => 'nullable|integer',
                'kantorkas' => 'nullable|integer',
                // 'id_direksi' => 'nullable|integer',
                // 'id_kepala_cabang' => 'nullable|integer',
                // 'id_supervisor' => 'nullable|integer',
                // 'id_admin_kas' => 'nullable|integer',
                'status' => 'nullable|integer',
            ]);

            Log::info('Input validated', ['validated_data' => $validated]);
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        // Cari pengguna berdasarkan ID
        $user = User::find($id);

        if (!$user) {
            Log::warning('User not found', ['id' => $id]);
            return response()->json(['message' => 'User not found'], 404);
        }

        Log::info('User found', ['user' => $user]);

    

        // Update tabel berdasarkan jabatan
        switch (strtolower($validated['jabatan'])) {
            case '1':
                Log::info('Updating Direksi table', ['user_id' => $id]);
                // $direksi = Direksi::find($id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->jabatan_id = $validated['jabatan'];
                    $user->status = $validated['status'];

                    // $user->cabang = $validated['cabang'];
                    $user->save();
                    Log::info('Direksi data updated successfully', ['direksi' => $user]);
                } else {
                    Log::warning('Direksi not found', ['id' => $id]);
                }
                break;

            case '2':
                Log::info('Updating Kepala Cabang table', ['user_id' => $id]);
                // $kepalaCabang = PegawaiKepalaCabang::find($id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->jabatan_id = $validated['jabatan'];

                    $user->id_cabang = $validated['cabang'];
                    $user->status = $validated['status'];

                    $user->save();
                    Log::info('Kepala Cabang data updated successfully', ['kepalaCabang' => $user]);
                } else {
                    Log::warning('Kepala Cabang not found', ['id' => $id]);
                }
                break;

            case '3':
                Log::info('Updating Supervisor table', ['user_id' => $id]);
                // $supervisor = PegawaiSupervisor::find($id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->jabatan_id = $validated['jabatan'];

                    $user->id_cabang = $validated['cabang'];
                    $user->id_kantorkas = $validated['kantorkas'] ?? null;
                    $user->status = $validated['status'];

                    $user->save();
                    Log::info('Supervisor data updated successfully', ['supervisor' => $user]);
                } else {
                    Log::warning('Supervisor not found', ['id' => $id]);
                }
                break;

            case '4':
                Log::info('Updating Admin Kas table', ['user_id' => $id]);
                // $adminKas = PegawaiAdminKas::find($id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->jabatan_id = $validated['jabatan'];

                    $user->id_cabang = $validated['cabang'];
                    $user->id_kantorkas = $validated['kantorkas'] ?? null;
                    $user->status = $validated['status'];

                    $user->save();
                    Log::info('Admin Kas data updated successfully', ['adminKas' => $user]);
                } else {
                    Log::warning('Admin Kas not found', ['id' => $id]);
                }
                break;

            case '5':
                Log::info('Updating Account Officer table', ['user_id' => $id]);
                // $accountOfficer = PegawaiAccountOffice::find($id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->jabatan_id = $validated['jabatan'];

                    $user->id_cabang = $validated['cabang'];
                    $user->id_kantorkas = $validated['kantorkas'] ?? null;
                    // $accountOfficer->id_admin_kas = $validated['id_admin_kas'];
                    $user->status = $validated['status'];
                    // $accountOfficer->save();
                    $user->save();
                    Log::info('Account Officer data updated successfully', ['accountOfficer' => $user]);
                    Log::info('user data updated successfully', ['user' => $user]);
                } else {
                    Log::warning('Account Officer not found', ['id' => $id]);
                }
                break;

            default:
                Log::error('Invalid jabatan provided', ['jabatan' => $validated['jabatan']]);
                return response()->json(['message' => 'Invalid jabatan'], 400);
        }

        Log::info('User update process completed', ['user_id' => $id]);

        return response()->json(['message' => 'User updated successfully'], 200);
    }
}