<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ResidentRequestController extends Controller
{
    /**
     * Display a listing of the resident's requests.
     */
    public function index(Request $request)
    {
        // Fetch requests belonging to the logged-in user
        // Filter by tracking code if 'search' is present
        $documentRequests = DocumentRequest::query()
            ->where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('tracking_code', 'like', "%{$search}%");
            })
            ->with(['type']) // Eager load the document type
            ->latest()
            ->paginate(10);

        return view('userdashboard.forResident.requests.index', compact('documentRequests'));
    }

    /**
     * Show the form for creating a new request.
     */
    public function create()
    {
        // Get all available document types for the dropdown
        $documentTypes = DocumentType::all(); // You might want to filter active types here

        return view('userdashboard.forResident.requests.request_document.create', compact('documentTypes'));
    }

    /**
     * Store a newly created request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose'          => 'required|string|max:255',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB per file
        ]);

        // 1. Generate a unique Tracking Code (e.g., DOC-20231025-ABCD)
        $trackingCode = 'DOC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        // 2. Create the Request Record
        $documentRequest = DocumentRequest::create([
            'tracking_code'     => $trackingCode,
            'user_id'           => Auth::id(),
            // We autofill these from the auth user to keep a snapshot record
            'requestor_name'    => Auth::user()->name, 
            'requestor_phone'   => Auth::user()->phone_number ?? null, // Assuming phone is in User or Resident model
            'requestor_address' => Auth::user()->address ?? null,      // Assuming address is in User or Resident model
            'document_type_id'  => $validated['document_type_id'],
            'purpose'           => $validated['purpose'],
            'status'            => 'pending',
        ]);

        // 3. Handle File Uploads (if any)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store in 'storage/app/public/request_attachments'
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
        // Find request or fail, ensuring it belongs to the current user
        $request = DocumentRequest::with(['type', 'attachments'])
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
                    \Illuminate\Support\Facades\Storage::delete($attachment->file_path); 
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

        // Only allow cancellation if pending
        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'You cannot cancel a request that has already been processed.');
        }

        // Delete associated files from storage
        foreach ($documentRequest->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Delete the record (Cascade will delete attachment DB rows)
        $documentRequest->delete();

        return redirect()->route('resident.requests.index')
            ->with('success', 'Request cancelled and deleted successfully.');
    }
}
