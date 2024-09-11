<?php

namespace App\Imports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class NasabahImport implements ToModel, WithHeadingRow
{

    protected $idAdminKas;

    public function __construct($idAdminKas) 
    {
        $this->idAdminKas = $idAdminKas;
    }

    public function model(array $row)
    {
        // Log the entire row for debugging
        Log::info('Processing row:', $row);

        // Validate 'no' field
        if (empty($row['no'])) {
            Log::warning("Skipping row due to empty 'no' field", $row);
            return null; // Skip this row
        }

        // Ensure all required fields are present and not empty
        $requiredFields = ['no', 'nama', 'pokok', 'bunga', 'denda', 'total', 'id_cabang', 'id_kantorkas', 'id_account_officer'];
        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || $row[$field] === '') {
                Log::warning("Skipping row due to missing or empty field: {$field}", $row);
                return null; // Skip this row
            }
        }

        // If all validations pass, create and return the Nasabah model
        return new Nasabah([
            'no' => $row['no'], 
            'nama' => $row['nama'],
            'pokok' => $row['pokok'],
            'bunga' => $row['bunga'],
            'denda' => $row['denda'],
            'total' => $row['total'],
            'keterangan' => $row['keterangan'] ?? null, // Optional field
            'id_cabang' => $row['id_cabang'],
            'id_kantorkas' => $row['id_kantorkas'],
            'id_account_officer' => $row['id_account_officer'],
            'id_admin_kas' => $this->idAdminKas,
        ]);
        
    }
}