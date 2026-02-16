<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use App\Models\ComplaintType;
use App\Models\ComplaintRequest;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        // 1. Capture Inputs
        $search = $request->input('search');
        $activeTab = $request->input('type', 'documents');

        // 2. Always get the "Counts"
        $pendingDocs = DocumentRequest::where('status', 'pending')->count();
        $pendingComplaints = ComplaintRequest::where('status', 'pending')->count();

        // 3. Initialize EMPTY PAGINATORS (Not Collections)
        // This creates a paginator with 0 items, total 0, per page 10.
        $documentRequests = new LengthAwarePaginator([], 0, 10);
        $complaints = new LengthAwarePaginator([], 0, 10);

        // 4. Load ONLY the data for the Active Tab
        if ($activeTab === 'documents') {
            
            $query = DocumentRequest::with(['user.resident', 'documentType']);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                    ->orWhere('requestor_name', 'like', "%{$search}%")
                    ->orWhereHas('user.resident', function($subQ) use ($search) {
                        $subQ->where('fname', 'like', "%{$search}%")
                            ->orWhere('lname', 'like', "%{$search}%");
                    });
                });
            }
            // Overwrite the empty paginator with real data
            $documentRequests = $query->latest()->paginate(10)->withQueryString();

        } elseif ($activeTab === 'complaints') {
            
            $query = ComplaintRequest::with(['complainant']); 

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('case_number', 'like', "%{$search}%")
                    ->orWhere('respondent_name', 'like', "%{$search}%")
                    ->orWhere('walkin_name', 'like', "%{$search}%"); // Added walkin_name search
                });
            }
            // Overwrite the empty paginator with real data
            $complaints = $query->latest()->paginate(10)->withQueryString();
        }

        // 5. Return the View
        return view('userdashboard.forAdmin.request_mgt.index', compact(
            'documentRequests',
            'complaints',
            'pendingDocs',
            'pendingComplaints'
        ));
    }

    public function create()
    {
        // 1. Get Users who have a linked Resident profile
        // We use 'with' to eagerly load the data to avoid N+1 performance issues
        $users = User::with('resident')
            ->whereNotNull('resident_id') // Ensure they are linked to a resident
            ->get(); 
        
        // 2. Get active document types
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forAdmin.request_mgt.document_request.create', compact('users', 'documentTypes'));
    }

    public function store(Request $request)
    {
        // 1. Validate
        // We ensure that EITHER 'user_id' OR 'requestor_name' is present.
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'requestor_name' => 'required_without:user_id|nullable|string|max:255', // Required if no user selected
            'requestor_phone' => 'nullable|string|max:20',
            'requestor_address' => 'nullable|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:255',
        ]);

        // 2. Generate Tracking Code
        $trackingCode = 'REQ-' . date('Y') . '-' . strtoupper(Str::random(6));

        // 3. Create Request
        DocumentRequest::create([
            'tracking_code' => $trackingCode,
            
            // Link User if selected, otherwise null
            'user_id' => $validated['user_id'] ?? null,
            
            // Save Walk-in details if User ID is null
            'requestor_name' => empty($validated['user_id']) ? $validated['requestor_name'] : null,
            'requestor_phone' => empty($validated['user_id']) ? ($validated['requestor_phone'] ?? null) : null,
            'requestor_address' => empty($validated['user_id']) ? ($validated['requestor_address'] ?? null) : null,

            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        return redirect()->route('admin.requests.index')
            ->with('success', 'Document request created successfully!');
    }

    public function show($id)
    {
        // Fetch request with linked User (resident) and Resident Profile
        $documentRequest = DocumentRequest::with(['resident.resident', 'documentType'])->findOrFail($id);

        return view('userdashboard.forAdmin.request_mgt.document_request.show', compact('documentRequest'));
    }

    public function updateStatus(Request $request, DocumentRequest $documentRequest)
    {
        // 1. Validate that the status is one of the allowed options
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,ready_for_pickup,rejected',
        ]);

        // 2. Update the record
        $documentRequest->update([
            'status' => $validated['status'],
            // Optional: If you have a 'remarks' field for rejections, you can update it here too
        ]);

        // 3. Redirect back with a success message
        // (If you are using Flux toasts, you might flash a toast here)
        return back()->with('status', "Request marked as {$validated['status']}.");
    }

    public function complaintCreate()
    {
        // 1. Get all users who are residents (assuming you have a role or type)
        // If you don't have roles yet, just use User::all()
        $residents = User::whereHas('resident')->get(); 

        // 2. Get all complaint types (e.g., Noise, Theft)
        $complaintTypes = ComplaintType::all();

        return view('userdashboard.forAdmin.request_mgt.complaint_request.complaintCreate', compact('residents', 'complaintTypes'));
    }

    public function complaintStore(Request $request)
    {
        // 1. Conditional Validation
        // We check if "isWalkIn" (or just the ID field) is used.
        $validated = $request->validate([
            // If complainant_id is NOT present, walkin_name is required.
            'complainant_id'    => 'nullable|exists:users,id',
            
            // "required_without" means: If they didn't pick a resident, they MUST type a name.
            'walkin_name'       => 'required_without:complainant_id|nullable|string|max:255',
            'walkin_phone'      => 'nullable|string|max:20',
            'walkin_address'    => 'nullable|string|max:255',

            // Incident Details
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'respondent_name'   => 'required|string|max:255',
            'incident_date'     => 'required|date',
            'location'          => 'required|string|max:255',
            'incident_details'  => 'required|string',
        ]);

        // 2. Generate Case Number (e.g., CASE-2023-ABC123)
        $caseNumber = 'CASE-' . date('Y') . '-' . strtoupper(Str::random(6));

        // 3. Prepare Data
        // If a resident was selected, we can optionally fill the walk-in fields 
        // with their data for easier searching later, OR just rely on the relationship.
        
        $complainantId = $request->input('complainant_id');
        
        // If it's a resident, we might want to grab their name just in case we need to display it without a join later
        // But usually, saving the ID is enough.
        
        $walkinName = $request->input('walkin_name');
        $walkinPhone = $request->input('walkin_phone');
        $walkinAddress = $request->input('walkin_address');

        // Logic: If Resident is selected, clear the manual fields (or fill them automatically)
        if ($complainantId) {
            $user = User::with('resident')->find($complainantId);
            // Optional: If you want to "freeze" their name at the time of complaint:
            // $walkinName = $user->resident ? $user->resident->fname . ' ' . $user->resident->lname : $user->name;
        }

        // 4. Create Complaint
        ComplaintRequest::create([
            'case_number'       => $caseNumber,
            
            // Parties
            'complainant_id'    => $complainantId, // Can be NULL
            'walkin_name'       => $walkinName,    // Filled if walk-in
            'walkin_phone'      => $walkinPhone,
            'walkin_address'    => $walkinAddress,
            'respondent_name'   => $validated['respondent_name'],

            // Incident Data
            'complaint_type_id' => $validated['complaint_type_id'],
            'incident_date'     => $validated['incident_date'],
            'location'          => $validated['location'],
            'incident_details'  => $validated['incident_details'],
            
            'status'            => 'pending',
        ]);

        return redirect()->route('admin.requests.index', ['type' => 'complaints'])
            ->with('success', 'Complaint filed successfully!');
    }

    public function complaintShow($id)
    {
        $complaint = ComplaintRequest::findOrFail($id);

        return view('userdashboard.forAdmin.request_mgt.complaint_request.complaintShow', compact('complaint')); 
    }

    public function complaintUpdateStatus(Request $request, $id)
    {
        // 1. Find the complaint
        $complaint = ComplaintRequest::findOrFail($id);

        // 2. Validate allowed statuses
        $validated = $request->validate([
            'status' => 'required|in:pending,investigation,hearing,settled,dismissed',
        ]);

        // 3. Update
        $complaint->update([
            'status' => $validated['status'],
        ]);

        // 4. Redirect
        return back()->with('status', "Case marked as {$validated['status']}.");
    }

}