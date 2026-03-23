<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplaintRequest;
use App\Models\ComplaintType;
use App\Models\User;
use App\Models\Resident; 
use App\Models\DocumentRequest;
use App\Models\ComplaintStatusHistory; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Cache;

class OfficialComplaintController extends Controller
{
    public function index(Request $request)
    {
        $officialId = Auth::id();

        // 1. Shared Navigation Badges 
        $pendingDocs = DocumentRequest::where('assigned_official_id', $officialId)
            ->where('status', 'pending')->count();
        $pendingComplaints = ComplaintRequest::where('assigned_official_id', $officialId)
            ->where('status', 'pending')->count();

        // 2. Fetch Complaint specific data
        $complaints = ComplaintRequest::with(['type'])
            ->where('assigned_official_id', $officialId)
            ->filter($request->all())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 3. Return Complaint View
        return view('userdashboard.forOfficial.request_mgt.complaint_request.index', compact(
            'complaints', 
            'pendingDocs', 
            'pendingComplaints'
        ));
    }

    public function create()
    {
        $residents = User::whereHas('resident')->get(); 
        $complaintTypes = ComplaintType::all();

        return view('userdashboard.forOfficial.request_mgt.complaint_request.complaintCreate', compact('residents', 'complaintTypes'));
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

        $data = [
            'case_number'          => 'CASE-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'complaint_type_id'    => $validated['complaint_type_id'],
            'incident_at'          => $validated['incident_at'], 
            'location'             => $validated['location'],
            'incident_details'     => $validated['incident_details'],
            'status'               => 'pending',
            'mode_of_request'      => 'walk-in', 
            'user_id'              => null,
            'resident_id'          => null,
            'complainant_name'     => null,
            'complainant_phone'    => null,
            'complainant_address'  => null,
            'assigned_official_id' => Auth::id(), 
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
            $data['complainant_name']   = $validated['complainant_name'];
            $data['complainant_phone']  = $validated['complainant_phone'];
            $data['complainant_address']= $validated['complainant_address'];
        }

        // 2. Process Respondent
        $respondent = Resident::find($validated['respondent_id']);
        
        $data['respondent_name'] = "{$respondent->fname} {$respondent->lname}";
        $data['respondent_resident_id'] = $respondent->id;
        
        $respondentUser = User::whereHas('resident', function($query) use ($respondent) {
            $query->where('id', $respondent->id);
        })->first();
        
        $data['respondent_user_id'] = $respondentUser ? $respondentUser->id : null;

        // 3. Prevent Self-Complaints (Strict Comparison)
        if ($data['resident_id'] && (int)$data['resident_id'] === (int)$data['respondent_resident_id']) {
            throw ValidationException::withMessages([
                'respondent_id' => 'A complainant cannot file a complaint against themselves.'
            ]);
        }

        // 4. Create the Complaint
        ComplaintRequest::create($data);

        // 5. Clear Caches
        Cache::forget('admin_pending_complaints');
        Cache::forget('official_pending_complaints_' . Auth::id());
        
        if ($data['user_id']) {
            Cache::forget('resident_pending_complaints_' . $data['user_id']);
        }

        return redirect()->route('official.complaints.index')
            ->with('success', 'Complaint filed successfully!');
    }

    public function show($id)
    {
        // Security lock intact
        $complaint = ComplaintRequest::with('statusHistories.changer')
            ->where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();
        
        $officials = User::where('role', 'official')->get(); 

        return view('userdashboard.forOfficial.request_mgt.complaint_request.complaintShow', compact('complaint', 'officials')); 
    }

    public function updateStatus(Request $request, $id)
    {
        // Security lock intact
        $complaint = ComplaintRequest::where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();
            
        $oldStatus = $complaint->status;

        $validated = $request->validate([
            'status'              => 'required|in:pending,investigation,hearing,settled,dismissed',
            'investigation_notes' => 'nullable|string',
            'hearing_date'        => 'nullable|date',
            'resolution'          => 'nullable|in:founded,unfounded,settled,dismissed',
            'resolution_notes'    => 'nullable|string',
            'official_remarks'    => 'nullable|string', 
        ]);

        // Cleaned up to use strict $validated array mapping
        $complaint->update([
            'status'              => $validated['status'],
            'investigation_notes' => $validated['investigation_notes'] ?? $complaint->investigation_notes,
            'hearing_date'        => $validated['hearing_date'] ?? $complaint->hearing_date,
            'resolution'          => $validated['resolution'] ?? $complaint->resolution,
            'resolution_notes'    => $validated['resolution_notes'] ?? $complaint->resolution_notes,
            'admin_remarks'       => $validated['official_remarks'] ?? $complaint->admin_remarks, 
        ]);

        if ($oldStatus !== $validated['status'] || !empty($validated['official_remarks'])) {
            ComplaintStatusHistory::create([
                'complaint_request_id' => $complaint->id,
                'old_status'           => $oldStatus,
                'new_status'           => $validated['status'],
                'remarks'              => $validated['official_remarks'], 
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

        return redirect()->route('official.complaints.show', $id)->with('success', "Case updated and saved successfully!");
    }
}