<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Resident;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use App\Models\ComplaintType;
use App\Models\ComplaintRequest;
use App\Models\ComplaintStatusHistory;
use App\Notifications\SystemAlertNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ComplaintRequestController extends Controller
{
    public function index(Request $request)
    {
        $complaints = ComplaintRequest::with(['type', 'resident'])
            ->filter($request->all())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Pass empty docs paginator to satisfy shared view
        $documentRequests = new LengthAwarePaginator([], 0, 10);

        $pendingDocs = DocumentRequest::where('status', 'pending')->count();
        $pendingComplaints = ComplaintRequest::where('status', 'pending')->count();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forAdmin.request_mgt.complaint_request.index', [
            'documentRequests'  => $documentRequests,
            'complaints'        => $complaints,
            'pendingDocs'       => $pendingDocs,
            'pendingComplaints' => $pendingComplaints,
            'documentTypes'     => $documentTypes,
            'activeTab'         => 'complaints'
        ]);
    }

    public function create()
    {
        $residents = User::whereHas('resident')->get(); 
        $complaintTypes = ComplaintType::all();

        return view('userdashboard.forAdmin.request_mgt.complaint_request.complaintCreate', compact('residents', 'complaintTypes'));
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'mode'                => 'required|in:have_account,registered_resident,walk_in',
            'user_id'             => 'nullable|required_if:mode,have_account|exists:users,id',
            'resident_id'         => 'nullable|required_if:mode,registered_resident|exists:residents,id',
            'complainant_name'    => 'nullable|required_if:mode,walk_in|string|max:255',
            'complainant_phone'   => 'nullable|string|max:20',
            'complainant_address' => 'nullable|string|max:255',
            'respondent_id'       => 'required|exists:residents,id', 
            'complaint_type_id'   => 'required|exists:complaint_types,id',
            'incident_at'         => 'required|date', 
            'location'            => 'required|string|max:255',
            'incident_details'    => 'required|string',
        ]);

        // Base data structure
        $data = [
            'case_number'         => 'CASE-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'complaint_type_id'   => $validated['complaint_type_id'],
            'incident_at'         => $validated['incident_at'], 
            'location'            => $validated['location'],
            'incident_details'    => $validated['incident_details'],
            'status'              => 'pending',
            'mode_of_request'     => 'walk-in', 
            'user_id'             => null,
            'resident_id'         => null,
            'complainant_name'    => null,
            'complainant_phone'   => null,
            'complainant_address' => null,
        ];

        // 1. Process Complainant
        if ($validated['mode'] === 'have_account') {
            $user = User::with('resident')->find($validated['user_id']);
            $data['user_id'] = $user->id;
            $data['resident_id'] = $user->resident?->id;

        } elseif ($validated['mode'] === 'registered_resident') {
            $resident = Resident::find($validated['resident_id']);
            $data['resident_id'] = $resident->id;

        } else { 
            // Walk-in
            $data['complainant_name']    = $validated['complainant_name'];
            $data['complainant_phone']   = $validated['complainant_phone'];
            $data['complainant_address'] = $validated['complainant_address'];
        }

        // 2. Process Respondent 
        $respondent = Resident::find($validated['respondent_id']);
        
        $data['respondent_name'] = "{$respondent->fname} {$respondent->lname}";
        $data['respondent_resident_id'] = $respondent->id;
        
        // Find if this resident has an attached User account
        $respondentUser = User::whereHas('resident', function($query) use ($respondent) {
            $query->where('id', $respondent->id);
        })->first();
        
        $data['respondent_user_id'] = $respondentUser ? $respondentUser->id : null;

        // 3. Prevent Self-Complaints (Using strict integer comparison)
        if ($data['resident_id'] && (int)$data['resident_id'] === (int)$data['respondent_resident_id']) {
            throw ValidationException::withMessages([
                'respondent_id' => 'A complainant cannot file a complaint against themselves.'
            ]);
        }

        ComplaintRequest::create($data);

        // 4. Clear Cache to Update Sidebars Instantly
        Cache::forget('admin_pending_complaints');
        
        // If the complainant has a user account, clear their sidebar cache too
        if ($data['user_id']) {
            Cache::forget('resident_pending_complaints_' . $data['user_id']);
        }

        return redirect()->route('admin.complaints.index', ['type' => 'complaints'])
            ->with('success', 'Complaint filed successfully!');
    }
    
    public function show($id)
    {
        $complaint = ComplaintRequest::with('statusHistories.changer')->findOrFail($id);
        $officials = User::where('role', 'official')->get(); 

        return view('userdashboard.forAdmin.request_mgt.complaint_request.complaintShow', compact('complaint', 'officials')); 
    }

    public function updateStatus(Request $request, $id)
    {
        $complaint = ComplaintRequest::with('user')->findOrFail($id);
        $oldStatus = $complaint->status;
        
        // Standardized cache key to match store() method
        Cache::forget('admin_pending_complaints');
        
        $validated = $request->validate([
            'status'              => 'required|in:pending,investigation,hearing,settled,dismissed',
            'investigation_notes' => 'nullable|string',
            'hearing_date'        => 'nullable|date',
            'resolution'          => 'nullable|in:founded,unfounded,settled,dismissed',
            'resolution_notes'    => 'nullable|string',
            'admin_remarks'       => 'nullable|string',
        ]);

        // Using $validated array safely
        $complaint->update([
            'status'              => $validated['status'],
            'investigation_notes' => $validated['investigation_notes'] ?? $complaint->investigation_notes,
            'hearing_date'        => $validated['hearing_date'] ?? $complaint->hearing_date,
            'resolution'          => $validated['resolution'] ?? $complaint->resolution,
            'resolution_notes'    => $validated['resolution_notes'] ?? $complaint->resolution_notes,
            'admin_remarks'       => $validated['admin_remarks'] ?? $complaint->admin_remarks,
        ]);

        $statusChanged = $oldStatus !== $validated['status'];

        if ($statusChanged || !empty($validated['admin_remarks'])) {
            ComplaintStatusHistory::create([
                'complaint_request_id' => $complaint->id,
                'old_status'           => $oldStatus,
                'new_status'           => $validated['status'],
                'remarks'              => $validated['admin_remarks'],
                'changed_by_id'        => auth()->id(), 
            ]);

            if ($complaint->user) {
                $complaint->user->notify(new SystemAlertNotification(
                    'Complaint Case Update', 
                    "There is an update on your filed complaint ({$complaint->case_number}). Current status: " . ucfirst($validated['status']) . ".",
                    route('resident.complaints.index')
                ));
            }
        }

        return back()->with('success', "Case updated and saved successfully!");
    }

    public function assignOfficial(Request $request, $id)
    {
        $validated = $request->validate([
            'official_id' => 'required|exists:users,id',
        ]);

        $complaint = ComplaintRequest::findOrFail($id);

        $complaint->update([
            'assigned_official_id' => $validated['official_id'],
        ]);

        $official = User::find($validated['official_id']);
        if ($official) {
            $official->notify(new SystemAlertNotification(
                'New Case Assigned', 
                "You have been assigned to handle Complaint: {$complaint->case_number}.",
                route('official.dashboard') 
            ));
        }

        return back()->with('success', 'Official assigned to the case successfully!');
    }
}