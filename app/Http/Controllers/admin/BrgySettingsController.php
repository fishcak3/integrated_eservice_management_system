<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrgySetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Spatie\DbDumper\Databases\MySql;
use Illuminate\Support\Facades\Artisan;
use App\Models\DocumentType;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\File;

class BrgySettingsController extends Controller
{
    public function index()
    {
        return view('userdashboard.forAdmin.system_settings_mgt.brgy_profile.index', [
            'global_sitios' => BrgySetting::get('sitios'),
            'barangay_name' => BrgySetting::get('barangay_name'),
            'municipality' => BrgySetting::get('municipality'),
            'province' => BrgySetting::get('province'),
            'region' => BrgySetting::get('region'),
            'postal_code' => BrgySetting::get('postal_code'),
            'population' => BrgySetting::get('population'),
            'address'       => BrgySetting::get('address'),
            'contact_phone' => BrgySetting::get('contact_phone'),
            'office_email' => BrgySetting::get('office_email'),
            'logo' => BrgySetting::get('barangay_logo'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'sitios' => 'nullable|array',
            'sitios.*' => 'required|string|max:255',
            'barangay_name' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'population' => 'nullable|integer',
            'address'       => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'office_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $cleanSitios = $request->sitios ? array_filter($request->sitios) : [];
        BrgySetting::set('sitios', json_encode(array_values($cleanSitios)));
        BrgySetting::set('barangay_name', $request->barangay_name);
        BrgySetting::set('municipality', $request->municipality);
        BrgySetting::set('province', $request->province);
        BrgySetting::set('region', $request->region);
        BrgySetting::set('postal_code', $request->postal_code);
        BrgySetting::set('population', $request->population);
        BrgySetting::set('address', $request->address);
        BrgySetting::set('contact_phone', $request->contact_phone);
        BrgySetting::set('office_email', $request->office_email);

        if ($request->hasFile('logo')) {
            $oldLogo = BrgySetting::get('barangay_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            BrgySetting::set('barangay_logo', $path);
        }

        Cache::forget('global_brgy_settings');

        return redirect()->route('settings.index', ['tab' => 'general'])
            ->with('status', 'Settings updated successfully!');
    }

    // ==========================================
    // BACKUP & MAINTENANCE METHODS
    // ==========================================

    public function backup()
    {
        // 1. Use the exact same absolute path as your generateBackup method
        $backupFolder = storage_path('app/backups');

        if (!File::exists($backupFolder)) {
            File::makeDirectory($backupFolder, 0755, true);
        }

        // 2. Fetch all files directly using the File facade
        $files = File::files($backupFolder);
        $backups = [];

        foreach ($files as $file) {
            // Optional: Only grab .sql files just in case other files end up here
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(), // Automatically strips the folder path
                    'size' => number_format($file->getSize() / 1048576, 2) . ' MB',
                    'date' => \Carbon\Carbon::createFromTimestamp($file->getMTime())->format('M d, Y - h:i A'),
                    'timestamp' => $file->getMTime()
                ];
            }
        }

        // 3. Sort backups (newest first)
        usort($backups, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return view('userdashboard.forAdmin.system_settings_mgt.backup.index', compact('backups'));
    }

    public function generateBackup()
    {
        $fileName = 'barangay_backup_' . date('Y_m_d_H_i_s') . '.sql';
        
        // 1. Force absolute path for the backups folder and ensure it exists
        $backupFolder = storage_path('app/backups');
        if (!is_dir($backupFolder)) {
            mkdir($backupFolder, 0755, true);
        }

        // 2. Create the exact file path
        $filePath = $backupFolder . '/' . $fileName;
        
        try {
            \Spatie\DbDumper\Databases\MySql::create()
                // Use the exact path you found, but with forward slashes!
                ->setDumpBinaryPath('C:/Program Files/MySQL/MySQL Server 9.6/bin')
                ->setDbName(env('DB_DATABASE'))
                ->setUserName(env('DB_USERNAME'))
                ->setPassword(env('DB_PASSWORD'))
                ->setHost(env('DB_HOST', '127.0.0.1'))
                ->dumpToFile($filePath);

            return back()->with('status', 'Backup generated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate backup: ' . $e->getMessage());
        }
    }

    public function downloadBackup($fileName)
    {
        $filePath = storage_path('app/backups/' . $fileName);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return back()->with('error', 'Backup file not found.');
    }

    public function deleteBackup($fileName)
    {
        $filePath = storage_path('app/backups/' . $fileName);

        if (file_exists($filePath)) {
            unlink($filePath);
            return back()->with('status', 'Backup deleted successfully.');
        }

        return back()->with('error', 'Backup file not found.');
    }

    // ==========================================
    // CACHE & MAINTENANCE METHODS
    // ==========================================

    public function clearCache()
    {
        // Clears application and config cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        
        return back()->with('status', 'Application cache cleared successfully!');
    }

    public function clearViews()
    {
        // Clears compiled view files
        Artisan::call('view:clear');
        
        return back()->with('status', 'View cache cleared successfully!');
    }

    public function toggleMaintenance()
    {
        if (app()->isDownForMaintenance()) {
            // Bring the application back online
            Artisan::call('up');
            return back()->with('status', 'System is now ONLINE and accessible to residents.');
        } else {
            // Put the application in maintenance mode. 
            // We use a "secret" so you (the admin) don't lock yourself out!
            $secret = 'admin-bypass-' . date('Y');
            Artisan::call('down', ['--secret' => $secret]);
            
            // Redirects you to the bypass URL so your browser gets a free pass to stay logged in
            return redirect('/' . $secret)->with('status', 'System is now OFFLINE. Maintenance mode enabled.');
        }
    }

    public function requests(Request $request)
    {
        $searchTerm = $request->input('search');

        // Fetch Documents, filtering by name if a search term exists
        $documentTypes = DocumentType::query()
            ->when($searchTerm, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('requirements', 'like', "%{$search}%");
            })
            ->get();

        // Fetch Complaints, filtering by name or description if a search term exists
        $complaintTypes = ComplaintType::query()
            ->when($searchTerm, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->get();

        return view('userdashboard.forAdmin.system_settings_mgt.requests.index', compact('documentTypes', 'complaintTypes'));
    }

    public function createRequestType(Request $request)
    {
        // We get the type from the URL query parameter (e.g., ?type=document)
        $type = $request->query('type', 'document');

        // Make sure it's a valid type to prevent errors
        if (!in_array($type, ['document', 'complaint'])) {
            abort(404); 
        }

        return view('userdashboard.forAdmin.system_settings_mgt.requests.create', compact('type'));
    }

    public function storeRequestType(Request $request)
    {
        // Get the type we are trying to save from the query parameter
        $type = $request->query('type', 'document');

        if ($type === 'document') {
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'fee' => 'required|numeric|min:0',
                'requirements' => 'nullable|string',
            ]);
            
            // Checkboxes only send data if checked, so we check for presence
            $validated['is_active'] = $request->has('is_active');
            
            DocumentType::create($validated);
            
            $message = 'Document Type created successfully!';

        } elseif ($type === 'complaint') {
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'severity_level' => 'required|in:low,medium,high,critical',
                'description' => 'nullable|string',
            ]);
            
            ComplaintType::create($validated);
            
            $message = 'Complaint Type created successfully!';

        } else {
            abort(404);
        }

        // Redirect back to the index page, ensuring we land on the correct tab
        return redirect()->route('settings.request', ['type' => $type])
                         ->with('status', $message);
    }

    public function editRequestType($type, $id)
    {
        // Check which type we are editing to grab the right model
        if ($type === 'document') {
            $item = DocumentType::findOrFail($id);
        } elseif ($type === 'complaint') {
            $item = ComplaintType::findOrFail($id);
        } else {
            abort(404); // Invalid type
        }

        return view('userdashboard.forAdmin.system_settings_mgt.requests.edit', compact('item', 'type'));
    }

    public function updateRequestType(Request $request, $type, $id)
    {
        if ($type === 'document') {
            $item = DocumentType::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'fee' => 'required|numeric|min:0',
                'requirements' => 'nullable|string',
            ]);
            
            // The switch only sends a value if checked, so we check if it exists in the request
            $validated['is_active'] = $request->has('is_active');
            
            $item->update($validated);
            
        } elseif ($type === 'complaint') {
            $item = ComplaintType::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'severity_level' => 'required|in:low,medium,high,critical',
                'description' => 'nullable|string',
            ]);
            
            $item->update($validated);
        } else {
            abort(404);
        }

        return redirect()->route('settings.request', ['type' => $type])
                         ->with('status', ucfirst($type) . ' updated successfully!');
    }
}