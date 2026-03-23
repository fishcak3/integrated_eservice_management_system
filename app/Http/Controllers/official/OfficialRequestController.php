<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use App\Models\ComplaintType;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Notifications\SystemAlertNotification;

class OfficialRequestController extends Controller
{
    public function index(Request $request)
    {
        $officialId = Auth::id();

        $pendingDocs = DocumentRequest::where('assigned_official_id', $officialId)
            ->where('status', 'pending')->count();
        $pendingComplaints = ComplaintRequest::where('assigned_official_id', $officialId)
            ->where('status', 'pending')->count();

        $documentTypes = DocumentType::all();
        
        $documentRequests = DocumentRequest::with(['documentType', 'user.resident'])
            ->where('assigned_official_id', $officialId)
            ->filter($request->all())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('userdashboard.forOfficial.request_mgt.document_request.index', compact(
            'documentRequests', 
            'documentTypes',
            'pendingDocs', 
            'pendingComplaints'
        ));
    }

    public function create()
    {
        $users = User::with('resident')->whereNotNull('resident_id')->get(); 
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forOfficial.request_mgt.document_request.create', compact('users', 'documentTypes'));
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

        $requestorName = $validated['requestor_name'] ?? null;
        $requestorPhone = $validated['requestor_phone'] ?? null;
        $requestorAddress = $validated['requestor_address'] ?? null;
        $residentId = null; 

        if ($request->mode === 'registered_resident') {
            $resident = \App\Models\Resident::find($request->resident_id);
            if ($resident) {
                $requestorName = trim("{$resident->fname} {$resident->lname}");
                $requestorPhone = $resident->phone_number ?? null; 
                $requestorAddress = $resident->sitio ?? null;
                $residentId = $resident->id;
            }
        }

        if ($request->mode === 'have_account') {
            $user = User::with('resident')->find($request->user_id);
            $resident = $user?->resident;
            if ($resident) {
                $requestorName = trim("{$resident->fname} {$resident->lname}");
                $requestorPhone = $resident->phone_number ?? null; 
                $requestorAddress = $resident->sitio ?? null;
                $residentId = $resident->id;
            }
        }

        $trackingCode = 'REQ-' . date('Y') . '-' . strtoupper(Str::random(6));

        DocumentRequest::create([
            'tracking_code' => $trackingCode,
            'user_id' => $request->mode === 'have_account' ? $validated['user_id'] : null,
            'resident_id' => $residentId,
            'requestor_name' => $requestorName,
            'requestor_phone' => $requestorPhone,
            'requestor_address' => $requestorAddress,
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'mode_of_request' => 'walk-in', 
            'status' => 'pending',
            'assigned_official_id' => Auth::id(), 
        ]);

        return redirect()->route('official.documents.index')
            ->with('success', 'Document request created and assigned successfully!');
    }

    public function show($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])
            ->where('id', $id)
            ->where('assigned_official_id', Auth::id()) 
            ->firstOrFail();

        // ✅ ADDED: Fetch audit logs for the view
        $auditLogs = $documentRequest->activities()->latest()->get();

        return view('userdashboard.forOfficial.request_mgt.document_request.show', compact('documentRequest', 'auditLogs'));
    }

    public function edit($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])
            ->where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail(); 

        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forOfficial.request_mgt.document_request.edit', compact('documentRequest', 'documentTypes'));
    }

    public function update($id, Request $request)
    {
        $documentRequest = DocumentRequest::where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();

        // ✅ UPDATED: Include Workbench fields similar to Admin
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'requestor_name' => 'required_without:user_id|nullable|string|max:255',
            'requestor_phone' => 'nullable|string|max:20',
            'requestor_address' => 'nullable|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            
            // Workbench Fields
            'purpose' => 'required|string|max:1000',
            'control_number' => 'nullable|string|max:255',
            'validity_period' => 'nullable|string|max:50',
            'ordinance_number' => 'nullable|string|max:255',
            'printed_name' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,processing,pending_e_signature,ready_for_pickup,completed,rejected',
        ]);

        $documentRequest->update($validated);

        return redirect()->route('official.documents.show', $documentRequest->id)
            ->with('success', 'Document request updated successfully!');
    }

    public function process($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])
            ->where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();

        // ✅ UPDATED: The 'Process' button is an anchor tag (`href`), so it expects to view a workbench page, just like Admin.
        return view('userdashboard.forOfficial.request_mgt.document_request.process', compact('documentRequest'));
    }

    public function updateStatus(Request $request, DocumentRequest $documentRequest)
    {
        if ($documentRequest->assigned_official_id !== Auth::id()) {
            abort(403, 'You are not authorized to update this request.');
        }

        // ✅ UPDATED: Allowed missing statuses from the Blade file
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,pending_e_signature,ready_for_pickup,completed,rejected',
        ]);

        $documentRequest->update([
            'status' => $validated['status'],
        ]);

        if ($documentRequest->user) {
            $friendlyStatus = ucwords(str_replace('_', ' ', $validated['status']));
            $documentRequest->user->notify(new SystemAlertNotification(
                'Document Status Changed', 
                "Your document request ({$documentRequest->tracking_code}) is now: {$friendlyStatus}.",
                route('resident.requests.index')
            ));
        }

        return back()->with('status', "Request marked as {$validated['status']}.");
    }

    // ==========================================
    // ✅ NEW METHODS REQUIRED BY SHOW.BLADE.PHP
    // ==========================================

    public function requestESign($id)
    {
        $documentRequest = DocumentRequest::where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();

        $documentRequest->update([
            'status' => 'pending_e_signature'
        ]);

        return redirect()->back()->with('success', 'E-Signature requested. The document has been routed to the Captain.');
    }

    public function approveSign($id)
    {
        $documentRequest = DocumentRequest::where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();

        // Verify the user acting is actually the Captain
        if (!auth()->user()->isCurrentOfficialPosition('Barangay Captain')) {
            abort(403, 'Only the Barangay Captain can approve e-signatures.');
        }

        $documentRequest->update([
            'status' => 'ready_for_pickup',
            'is_e_signed' => true,
            'approved_at' => now(), 
        ]);

        return back()->with('success', 'Document e-signed and is now ready for pickup!');
    }

    public function preview($id)
    {
        $documentRequest = DocumentRequest::with(['user.resident', 'documentType'])
            ->where('id', $id)
            ->where('assigned_official_id', Auth::id())
            ->firstOrFail();

        $official = \App\Models\Official::whereHas('position', function ($query) {
            $query->where('title', 'like', '%Punong Barangay%')
                  ->orWhere('title', 'like', '%Captain%')
                  ->orWhere('title', 'like', '%Chairman%');
        })->with('resident')->first();

        if ($official && $official->resident) {
            $fname = $official->resident->fname ?? '';
            $mname = $official->resident->mname ? substr($official->resident->mname, 0, 1) . '.' : '';
            $lname = $official->resident->lname ?? '';
            $suffix = $official->resident->suffix ?? '';
            $captainName = strtoupper(trim("$fname $mname $lname $suffix"));
        } else {
            $captainName = 'NAME NOT SET IN OFFICIALS';
        }

        // Make sure you have a preview PDF view setup for officials or point this to the shared one
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('userdashboard.forAdmin.request_mgt.document_request.pdf_preview', compact('documentRequest', 'captainName', 'official'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]); 

        return $pdf->stream("DRAFT_{$documentRequest->tracking_code}.pdf");
    }
}