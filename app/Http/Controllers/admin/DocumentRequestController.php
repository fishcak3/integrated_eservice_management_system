<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Resident;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use App\Notifications\SystemAlertNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentRequestController extends Controller
{
    public function index(Request $request)
    {
        $documentRequests = DocumentRequest::with(['user.resident', 'documentType'])
            //->filter($request->all())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Pass empty complaints paginator to satisfy the shared view
        $complaints = new LengthAwarePaginator([], 0, 10);

        $pendingDocs = DocumentRequest::where('status', 'pending')->count();
        $pendingComplaints = ComplaintRequest::where('status', 'pending')->count();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forAdmin.request_mgt.document_request.index', [
            'documentRequests' => $documentRequests,
            'complaints' => $complaints,
            'pendingDocs' => $pendingDocs,
            'pendingComplaints' => $pendingComplaints,
            'documentTypes' => $documentTypes,
            'activeTab' => 'documents'
        ]);
    }

    public function create()
    {
        $users = User::with('resident')->whereNotNull('resident_id')->get(); 
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forAdmin.request_mgt.document_request.create', compact('users', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|in:have_account,registered_resident,walk_in',
            'user_id' => 'required_if:mode,have_account|nullable|exists:users,id',
            'resident_id' => 'required_if:mode,registered_resident|nullable|exists:residents,id',
            'requestor_name' => 'required_if:mode,walk_in|nullable|string|max:255',
            'requestor_phone' => 'nullable|string|max:20',
            'requestor_address' => 'nullable|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'purpose' => 'required|string|max:500', 
        ]);

        // 1. Initialize all optional fields as null
        $userId = null;
        $residentId = null;
        $requestorName = null;
        $requestorPhone = null;
        $requestorAddress = null;

        // 2. Populate specific fields based strictly on the mode
        if ($validated['mode'] === 'walk_in') {
            // Walk-in: Only save manual details
            $requestorName = $validated['requestor_name'];
            $requestorPhone = $validated['requestor_phone'] ?? null;
            $requestorAddress = $validated['requestor_address'] ?? null;

        } elseif ($validated['mode'] === 'have_account') {
            // Have Account: Save user ID, and optionally resident ID if verified
            $userId = $validated['user_id'];
            $user = User::with('resident')->find($userId);

            // Check if the user is verified to include their resident ID
            if ($user && $user->verification_status === 'verified' && $user->resident) {
                $residentId = $user->resident->id;
            }

        } elseif ($validated['mode'] === 'registered_resident') {
            // Registered Resident: Only save resident ID
            $residentId = $validated['resident_id'];
        }

        // 3. Generate Tracking Code
        $trackingCode = 'REQ-' . date('Y') . '-' . strtoupper(Str::random(6));

        // 4. Create the Document Request
        DocumentRequest::create([
            'tracking_code' => $trackingCode,
            'user_id' => $userId,
            'resident_id' => $residentId,
            'requestor_name' => $requestorName,
            'requestor_phone' => $requestorPhone,
            'requestor_address' => $requestorAddress,
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'mode_of_request' => 'walk-in', 
            'status' => 'pending',
        ]);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document request created successfully!');
    }

    public function show($id)
    {
        $officials = User::where('role', 'admin')->get(); 
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])->findOrFail($id);

        $auditLogs = $documentRequest->activities()->latest()->get();
        return view('userdashboard.forAdmin.request_mgt.document_request.show', compact('documentRequest', 'officials', 'auditLogs'));
    }
    
    public function edit($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])->findOrFail($id);
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forAdmin.request_mgt.document_request.edit', compact('documentRequest', 'documentTypes'));
    }

    public function process($id)
    {
        // 1. Fetch the document request with necessary relationships
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])->findOrFail($id);

        // 2. Return the Workbench view
        return view('userdashboard.forAdmin.request_mgt.document_request.process', compact('documentRequest'));
    }

    public function update($id, Request $request)
    {
        $documentRequest = DocumentRequest::with('user')->findOrFail($id);

        // 1. Validate incoming data (handles both the regular Edit form AND the Workbench form)
        $validated = $request->validate([
            // Standard Fields
            'user_id' => 'nullable|exists:users,id',
            'requestor_name' => 'nullable|string|max:255',
            'requestor_phone' => 'nullable|string|max:20',
            'requestor_address' => 'nullable|string|max:255',
            'document_type_id' => 'nullable|exists:document_types,id',
            
            // Workbench Fields
            'purpose' => 'required|string|max:1000',
            'control_number' => 'nullable|string|max:255',
            'validity_period' => 'nullable|string|max:50',
            'ordinance_number' => 'nullable|string|max:255',
            'printed_name' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,processing,ready_for_pickup,rejected',
        ]);

        $oldStatus = $documentRequest->status;

        // 2. Update the record
        $documentRequest->update($validated);

        // 3. Clear cache if status changed from pending to processing
        if (isset($validated['status']) && $validated['status'] === 'processing' && $oldStatus === 'pending') {
            \Illuminate\Support\Facades\Cache::forget('sidebar_pending_docs');
        }

        // 4. Notify the user if the admin updated the request OR moved it to processing
        if ($documentRequest->user) {
            $notificationTitle = ($oldStatus !== $documentRequest->status && $documentRequest->status === 'processing') 
                ? 'Document Request Processing' 
                : 'Document Request Updated';
                
            $notificationMessage = ($oldStatus !== $documentRequest->status && $documentRequest->status === 'processing')
                ? "Your document request ({$documentRequest->tracking_code}) is now being processed."
                : "Your document request ({$documentRequest->tracking_code}) details have been updated by the admin.";

            $documentRequest->user->notify(new SystemAlertNotification(
                $notificationTitle, 
                $notificationMessage,
                route('admin.documents.index') // Or route('resident.requests.index') depending on your setup
            ));
        }

        return redirect()->route('admin.documents.show', $documentRequest->id)
            ->with('success', 'Document request saved and updated successfully!');
    }

    public function preview($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])->findOrFail($id);

        // 1. Fetch the Captain (Renamed variable to $official to match your blade file)
        $official = \App\Models\Official::whereHas('position', function ($query) {
            $query->where('title', 'like', '%Punong Barangay%')
                  ->orWhere('title', 'like', '%Captain%')
                  ->orWhere('title', 'like', '%Chairman%');
        })->with('resident')->first();

        // 2. Safely get the name using the correct database columns
        if ($official && $official->resident) {
            $fname = $official->resident->fname ?? '';
            // Get just the first letter of the middle name if it exists
            $mname = $official->resident->mname ? substr($official->resident->mname, 0, 1) . '.' : '';
            $lname = $official->resident->lname ?? '';
            $suffix = $official->resident->suffix ?? '';
            
            // Combine them and trim any extra spaces
            $captainName = strtoupper(trim("$fname $mname $lname $suffix"));
        } else {
            $captainName = 'NAME NOT SET IN OFFICIALS';
        }

        // 3. ADDED $official to the compact() array so the PDF view can access the e_signature_path
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('userdashboard.forAdmin.request_mgt.document_request.pdf_preview', compact('documentRequest', 'captainName', 'official'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]); 

        return $pdf->stream("DRAFT_{$documentRequest->tracking_code}.pdf");
    }
    public function updateStatus(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,ready_for_pickup,completed,rejected',
        ]);

        $oldStatus = $documentRequest->status;

        $documentRequest->update([
            'status' => $validated['status'],
        ]);

        if ($oldStatus !== $validated['status'] && $documentRequest->user) {
            $friendlyStatus = str_replace('_', ' ', $validated['status']); 
            $documentRequest->user->notify(new SystemAlertNotification(
                'Document Status Changed', 
                "Your document request ({$documentRequest->tracking_code}) is now: {$friendlyStatus}.",
                route('admin.documents.index') 
            ));
        }

        return back()->with('status', "Request marked as {$validated['status']}.");
    }

    public function assignOfficial(Request $request, $id)
    {
        $validated = $request->validate([
            'official_id' => 'required|exists:users,id',
        ]);

        $documentRequest = DocumentRequest::findOrFail($id);

        $documentRequest->update([
            'assigned_official_id' => $validated['official_id'],
        ]);

        $official = User::find($validated['official_id']);
        if ($official) {
            $official->notify(new SystemAlertNotification(
                'New Document Task', 
                "You have been assigned to process Document Request: {$documentRequest->tracking_code}.",
                route('official.dashboard') 
            ));
        }

        return back()->with('success', 'Official assigned to the document request successfully!');
    }

    public function approveSign($id)
    {
        $documentRequest = DocumentRequest::findOrFail($id);

        // Update the database with the columns we verified earlier
        $documentRequest->update([
            'status' => 'ready_for_pickup',
            'is_e_signed' => true,
            'approved_at' => now(), 
        ]);

        return back()->with('success', 'Document e-signed and is now ready for pickup!');
    }

    public function requestESign($id)
    {
        $documentRequest = DocumentRequest::findOrFail($id);

        // Update the status so the Captain knows it needs signing
        $documentRequest->update([
            'status' => 'pending_e_signature'
        ]);

        // If you are using spatie/laravel-activitylog, you might want to log this manually:
        // activity()->performedOn($documentRequest)->log('Requested Captain\'s E-Signature');

        return redirect()->back()->with('success', 'E-Signature requested. The document has been routed to the Captain.');
    }
}