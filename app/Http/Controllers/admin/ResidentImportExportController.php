<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Household;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ResidentImportExportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt|max:5120']);

        $fileHandle = fopen($request->file('file')->getPathname(), 'r');
        $headers = array_map('strtolower', array_map('trim', fgetcsv($fileHandle)));
        $importedCount = 0;

        $toBool = fn($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN);

        while (($row = fgetcsv($fileHandle)) !== false) {
            // Skip empty rows
            if (array_filter($row) === []) continue;

            // Prevent app crash if a row has missing or extra columns
            if (count($headers) !== count($row)) {
                continue; 
            }

            $data = array_combine($headers, $row);

            // 1. Handle Date Format (DD/MM/YYYY)
            $birthdate = null;
            if (!empty($data['birthdate'])) {
                try {
                    $birthdate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($data['birthdate']))->format('Y-m-d');
                } catch (\Exception $e) {
                    $birthdate = date('Y-m-d', strtotime(trim($data['birthdate'])));
                }
            }

            // 2. Map 'is_family_head' from CSV to 'relation_to_head' in DB
            $isFamilyHead = $toBool($data['is_family_head'] ?? false);
            $relationToHead = $isFamilyHead ? 'head' : null;

            // 3. SAFE HOUSEHOLD CREATION 
            // This prevents the Foreign Key crash!
            $actualHouseholdId = null;
            if (!empty($data['household_id'])) {
                $household = Household::firstOrCreate(
                    ['household_number' => (string) $data['household_id']], // Treat CSV value as the unique household_number
                    ['sitio' => $data['sitio'] ?? null] // Add the sitio from the CSV if creating a new one
                );
                
                $actualHouseholdId = $household->id; // Get the actual database ID
            }

            Resident::create([
                // Basic Info
                'fname' => $data['fname'] ?? null,
                'mname' => $data['mname'] ?? null,
                'lname' => $data['lname'] ?? null,
                'suffix' => $data['suffix'] ?? null,
                'status' => !empty($data['status']) ? strtolower(trim($data['status'])) : 'active',
                'phone_number' => $data['phone_number'] ?? null,
                'birthdate' => $birthdate,
                'sex' => !empty($data['sex']) ? strtolower(trim($data['sex'])) : null,
                'civil_status' => !empty($data['civil_status']) ? strtolower(trim($data['civil_status'])) : null,
                
                // Assign the safely retrieved/created Household ID here:
                'household_id' => $actualHouseholdId,
                'relation_to_head' => $relationToHead,
                
                // Sectoral Info
                'solo_parent' => $toBool($data['solo_parent'] ?? false),
                'ofw' => $toBool($data['ofw'] ?? false),
                'is_pwd' => $toBool($data['is_pwd'] ?? false),
                'is_4ps_grantee' => $toBool($data['is_4ps'] ?? false),
                'out_of_school_children' => $toBool($data['out_of_school_children'] ?? false),
                'osa' => $toBool($data['osa'] ?? false),
                'unemployed' => $toBool($data['unemployed'] ?? false),
                'laborforce' => $toBool($data['laborforce'] ?? false),
                'isy_isc' => $toBool($data['isy_isc'] ?? false),
                'senior_citizen' => $toBool($data['senior_citizen'] ?? false),
                'voter' => $toBool($data['voter'] ?? false),
                
                // Family Details
                'mother_maiden_name' => $data['mother_maiden_name'] ?? null,
            ]);

            $importedCount++;
        }

        fclose($fileHandle);

        return back()->with('success', "Successfully imported {$importedCount} residents!");
    }

    public function exportCsv()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=residents_export_" . date('Y-m-d_H-i-s') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Headers match the exact structure of your sample CSV
        $columns = [
            'fname', 'mname', 'lname', 'suffix', 'status', 'phone_number', 'birthdate', 
            'sex', 'civil_status', 'sitio', 'household_id', 'is_family_head', 'solo_parent', 
            'ofw', 'is_pwd', 'is_4ps', 'out_of_school_children', 'osa', 'unemployed', 
            'laborforce', 'isy_isc', 'senior_citizen', 'voter', 'mother_maiden_name'
        ];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Fetch residents along with their household so we can access the "sitio"
            Resident::with('household')->chunk(500, function($residents) use(&$file) {
                foreach ($residents as $resident) {
                    $row = [
                        $resident->fname,
                        $resident->mname,
                        $resident->lname,
                        $resident->suffix,
                        $resident->status,
                        $resident->phone_number,
                        
                        // Output date in DD/MM/YYYY format
                        $resident->birthdate ? Carbon::parse($resident->birthdate)->format('d/m/Y') : '',
                        
                        $resident->sex,
                        $resident->civil_status,
                        
                        // Get sitio from the household table
                        $resident->household ? $resident->household->sitio : '',
                        $resident->household_id,
                        
                        // Convert relation back to a 1 or 0
                        $resident->relation_to_head === 'head' ? '1' : '0',
                        
                        // Output boolean fields as 1 or 0
                        $resident->solo_parent ? '1' : '0',
                        $resident->ofw ? '1' : '0',
                        $resident->is_pwd ? '1' : '0',
                        $resident->is_4ps_grantee ? '1' : '0',
                        $resident->out_of_school_children ? '1' : '0',
                        $resident->osa ? '1' : '0',
                        $resident->unemployed ? '1' : '0',
                        $resident->laborforce ? '1' : '0',
                        $resident->isy_isc ? '1' : '0',
                        $resident->senior_citizen ? '1' : '0',
                        $resident->voter ? '1' : '0',
                        
                        $resident->mother_maiden_name
                    ];

                    fputcsv($file, $row);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $residents = Resident::orderBy('lname', 'asc')->get();

        $pdf = Pdf::loadView('userdashboard.forAdmin.resident_mgt.pdf', compact('residents'))
                  ->setPaper('a4', 'landscape'); 

        return $pdf->download('residents_export_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}