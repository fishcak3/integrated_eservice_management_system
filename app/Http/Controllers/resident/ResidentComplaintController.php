<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplaintRequest;
use App\Models\ComplaintType;
use App\Models\RequestAttachment;
use App\Models\User;
use App\Models\Resident;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

use Illuminate\Support\Facades\Auth;

class ResidentComplaintController extends Controller
{
public function index(Request $request)
    {
        $userId = Auth::id();

        // 2. Fetch Complaints (This was missing in this controller!)
        $complaints = ComplaintRequest::query()
            ->where('user_id', $userId) 
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('case_number', 'like', "%{$search}%")
                      ->orWhere('respondent_name', 'like', "%{$search}%");
                });
            })

            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        $pendingComplaints = ComplaintRequest::where('user_id', $userId)->where('status', 'pending')->count();

        return view('userdashboard.forResident.requests.file_complaint.index', compact(
            'complaints',
            'pendingComplaints'
        ));
    }
    
    // Inside your Admin/Staff Complaint Controller:

    public function create()
    {
        // Fetch complaint types
        $complaintTypes = ComplaintType::orderBy('name')->get();

        $residents = User::whereHas('resident')->get(); 

        // Make sure this points to your ADMIN view file path
        return view('userdashboard.forResident.requests.file_complaint.create', compact('complaintTypes', 'residents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'incident_date'     => 'required|date|before_or_equal:today',
            'location'          => 'required|string|max:255',
            'incident_details'  => 'required|string',
            'resident_id'       => 'nullable|exists:residents,id', 
        ]);

        $year = date('Y');
        $lastComplaint = ComplaintRequest::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastComplaint ? ((int) substr($lastComplaint->case_number, -4)) + 1 : 1;
        $caseNumber = 'CASE-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $respondentName = 'Unknown Respondent';
        $respondentResidentId = null;

        if (!empty($validated['resident_id'])) {
            $resident = Resident::find($validated['resident_id']);
            if ($resident) {
                $respondentName = $resident->fname . ' ' . $resident->lname; 
                $respondentResidentId = $resident->id;
            }
        }

        $user = Auth::user();
        $complainantName = $user->resident ? ($user->resident->fname . ' ' . $user->resident->lname) : $user->name;

        // 1. Save the complaint
        $complaint = ComplaintRequest::create([
            'case_number'            => $caseNumber,
            'user_id'                => $user->id,
            'resident_id'            => $user->resident->id ?? null, 
            'complainant_name'       => $complainantName, 
            'respondent_name'        => $respondentName,
            'respondent_resident_id' => $respondentResidentId,
            'complaint_type_id'      => $validated['complaint_type_id'],
            'incident_at'            => $validated['incident_date'], 
            'location'               => $validated['location'],
            'incident_details'       => $validated['incident_details'],
            'status'                 => 'pending',
        ]);

        // --- ADDED NOTIFICATION LOGIC ---
        // 2. Notify Admins
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new SystemAlertNotification(
            'New Complaint Filed', 
            "{$complainantName} has filed a new complaint ({$caseNumber}).",
            route('admin.complaints.index') 
        ));
        // --------------------------------

        return redirect()->route('resident.complaints.index', ['type' => 'complaints'])
            ->with('success', "Complaint submitted successfully. Case Number: {$caseNumber}");
    }
    
    public function show($id)
    {
        $complaint = ComplaintRequest::with(['type', 'statusHistories.changer'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('userdashboard.forResident.requests.file_complaint.show', compact('complaint'));
    }

    public function edit($id)
    {
        $complaint = ComplaintRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($complaint->status !== 'pending') {
            return redirect()->route('resident.requests.index', ['type' => 'complaints'])
                ->with('error', 'You can no longer edit this complaint as it is already being processed.');
        }

        $complaintTypes = ComplaintType::orderBy('name')->get();

        return view('userdashboard.forResident.requests.file_complaint.edit', compact('complaint', 'complaintTypes'));
    }

    public function update(Request $request, $id)
    {
        $complaint = ComplaintRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($complaint->status !== 'pending') {
            return redirect()->route('resident.requests.index', ['type' => 'complaints'])
                ->with('error', 'Cannot update a complaint that is already being processed.');
        }

        $validated = $request->validate([
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'incident_date'     => 'required|date|before_or_equal:today',
            'location'          => 'required|string|max:255',
            'incident_details'  => 'required|string',
        ]);

        $complaint->update([
            'complaint_type_id' => $validated['complaint_type_id'],
            'incident_at'       => $validated['incident_date'],
            'location'          => $validated['location'],
            'incident_details'  => $validated['incident_details'],
        ]);

        return redirect()->route('resident.complaints.show', $complaint->id)
            ->with('success', 'Complaint details updated successfully.');
    }

    public function destroy($id)
    {
        $complaint = ComplaintRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($complaint->status !== 'pending') {
            return redirect()->route('resident.requests.index', ['type' => 'complaints'])
                ->with('error', 'You cannot cancel a complaint that is already under investigation.');
        }

        $complaint->delete();

        return redirect()->route('resident.requests.index', ['type' => 'complaints'])
            ->with('success', 'Complaint cancelled and deleted successfully.');
    }
}