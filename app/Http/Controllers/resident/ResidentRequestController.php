<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use App\Models\DocumentType;
use App\Models\RequestAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ResidentRequestController extends Controller
{
    private function ensureVerified()
    {
        if (Auth::user()->verification_status !== 'verified') {
            abort(redirect()->route('profile.edit') // Assuming this is your settings route
                ->with('error', 'Restricted: You must verify your account with a valid ID before requesting documents.'));
        }
    }

public function index(Request $request)
    {
        // 1. Fetch requests
        $documentRequests = DocumentRequest::query()
            ->where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('tracking_code', 'like', "%{$search}%");
            })
            ->with(['documentType', 'attachments'])
            ->latest()
            ->paginate(10);

        // 2. Get Counts for the Header Badges
        $pendingDocs = DocumentRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        // 3. Get Complaint Counts (Assuming you have a Complaint model)
        // If you don't have a Complaint model yet, just set this to 0
        $pendingComplaints = ComplaintRequest::where('complainant_id', Auth::id())
             ->where('status', 'pending')
             ->count();

        $complaints = \App\Models\ComplaintRequest::query()
        ->where('complainant_id', Auth::id()) // Ensure this matches your column name
        ->when($request->search, function ($query, $search) {
            $query->where('case_number', 'like', "%{$search}%")
                  ->orWhere('respondent_name', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10);

        return view('userdashboard.forResident.requests.index', compact('documentRequests', 'pendingDocs', 'pendingComplaints', 'complaints'));
    }

    public function create()
    {
        // 1. BLOCK: Check Verification Status
        $this->ensureVerified();

        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forResident.requests.request_document.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        // 1. BLOCK: Check Verification Status (Double check for security)
        $this->ensureVerified();

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose'          => 'required|string|max:255',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $trackingCode = 'DOC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        $documentRequest = DocumentRequest::create([
            'tracking_code'     => $trackingCode,
            'user_id'           => Auth::id(),
            'requestor_name'    => Auth::user()->name, 
            'requestor_phone'   => Auth::user()->phone_number ?? null,
            'requestor_address' => Auth::user()->address ?? null,     
            'document_type_id'  => $validated['document_type_id'],
            'purpose'           => $validated['purpose'],
            'status'            => 'pending',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('request_attachments', 'public');

                RequestAttachment::create([
                    'document_request_id' => $documentRequest->id,
                    'file_path'           => $path,
                    'file_name'           => $file->getClientOriginalName(),
                    'file_type'           => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('resident.requests.index')
            ->with('success', 'Request submitted successfully! Your tracking code is ' . $trackingCode);
    }

    /**
     * Display the specified request.
     */
    public function show($id)
    {
        $request = DocumentRequest::with(['documentType', 'attachments'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('userdashboard.forResident.requests.request_document.show', [
            'documentRequest' => $request
        ]);
    }

    /**
     * Show the form for editing the specified request.
     */
    public function edit($id)
    {
        // Fetch the model using a distinct name
        $documentRequest = DocumentRequest::where('user_id', Auth::id())->findOrFail($id);

        // Security: Only allow editing if the status is pending
        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'You cannot edit a request that is already processing.');
        }

        $documentTypes = DocumentType::where('is_active', true)->get();

        // Pass 'documentRequest' (matches your Blade file) instead of 'request'
        return view('userdashboard.forResident.requests.request_document.edit', compact('documentRequest', 'documentTypes'));
    }

    /**
     * Update the specified request in storage.
     */
    public function update(Request $httpRequest, $id)
    {
        // Fetch the model
        $documentRequest = DocumentRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Cannot update a request that is currently processing.');
        }

        // VALIDATION: Use $httpRequest (the form data), NOT $documentRequest (the database model)
        $validated = $httpRequest->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose'          => 'required|string|max:255',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // UPDATE: Update the database model
        $documentRequest->update([
            'document_type_id' => $validated['document_type_id'],
            'purpose'          => $validated['purpose'],
        ]);

        // HANDLE FILE DELETIONS (from the checkbox in edit.blade.php)
        if ($httpRequest->has('delete_attachments')) {
            foreach ($httpRequest->delete_attachments as $attachmentId) {
                $attachment = $documentRequest->attachments()->find($attachmentId);
                if ($attachment) {
                    Storage::delete($attachment->file_path); 
                    $attachment->delete(); 
                }
            }
        }

        // HANDLE NEW FILE UPLOADS
        if ($httpRequest->hasFile('attachments')) {
            foreach ($httpRequest->file('attachments') as $file) {
                $path = $file->store('request_attachments', 'public');
                
                $documentRequest->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('resident.requests.show', $documentRequest->id)
            ->with('success', 'Request details updated successfully.');
    }

    /**
     * Remove the specified request from storage (Cancel Request).
     */
    public function destroy($id)
    {
        $documentRequest = DocumentRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'You cannot cancel a request that has already been processed.');
        }

        foreach ($documentRequest->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $documentRequest->delete();

        return redirect()->route('resident.requests.index')
            ->with('success', 'Request cancelled and deleted successfully.');
    }
}
