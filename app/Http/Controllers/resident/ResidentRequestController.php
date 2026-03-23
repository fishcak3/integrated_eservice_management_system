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
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

class ResidentRequestController extends Controller
{
    private function ensureVerified()
    {
        if (Auth::user()->verification_status !== 'verified') {
            abort(redirect()->route('profile.edit') 
                ->with('error', 'Restricted: You must verify your account with a valid ID before requesting documents.'));
        }
    }

    public function index(Request $request)
    {
        // 1. Fetch Document Requests
        $documentRequests = DocumentRequest::query()
            ->where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('tracking_code', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->with(['documentType', 'attachments'])
            ->latest()
            ->paginate(10);



        // 3. Get Counts for the Header Badges 
        $pendingDocs = DocumentRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();


        // 4. Return view 
        return view('userdashboard.forResident.requests.request_document.index', compact(
            'documentRequests', 
            'pendingDocs', 
        ));
    }

    public function create()
    {
        $this->ensureVerified();
        $documentTypes = DocumentType::where('is_active', true)->get();
        return view('userdashboard.forResident.requests.request_document.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $this->ensureVerified();

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose'          => 'required|string|max:255',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $trackingCode = 'REQ-' . date('Y') . '-' . strtoupper(Str::random(6));
        $user = Auth::user();
        $resident = $user->resident; 

        $address = null;
        if ($resident) {
            $addressParts = [];
            if ($resident->sitio) $addressParts[] = 'Sitio ' . $resident->sitio;
            if ($resident->household_id) $addressParts[] = 'Household ID: ' . $resident->household_id;
            $address = implode(', ', $addressParts); 
        } else {
            $address = $user->address ?? null;
        }

        // 1. Save Document Request
        $documentRequest = DocumentRequest::create([
            'tracking_code'     => $trackingCode,
            'user_id'           => $user->id,
            'resident_id'       => $resident ? $resident->id : null, 
            'requestor_name'    => $resident ? trim("{$resident->fname} {$resident->lname}") : $user->name, 
            'requestor_phone'   => $resident->phone_number ?? $user->phone_number ?? null,
            'requestor_address' => $address,     
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

        $admins = User::where('role', 'admin')->get();
        $requestorName = $documentRequest->requestor_name;
        
        Notification::send($admins, new SystemAlertNotification(
            'New Document Request', 
            "{$requestorName} has requested a document ({$trackingCode}).",
            route('resident.requests.index') 
        ));
        // --------------------------------

        return redirect()->route('resident.requests.index')
            ->with('success', 'Request submitted successfully! Your tracking code is ' . $trackingCode);
    }

    public function show($id)
    {
        $request = DocumentRequest::with(['documentType', 'attachments'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('userdashboard.forResident.requests.request_document.show', [
            'documentRequest' => $request
        ]);
    }

    public function edit($id)
    {
        $documentRequest = DocumentRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'You cannot edit a request that is already processing.');
        }

        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('userdashboard.forResident.requests.request_document.edit', compact('documentRequest', 'documentTypes'));
    }

    public function update(Request $httpRequest, $id)
    {
        $documentRequest = DocumentRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($documentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Cannot update a request that is currently processing.');
        }

        $validated = $httpRequest->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'purpose'          => 'required|string|max:255',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $documentRequest->update([
            'document_type_id' => $validated['document_type_id'],
            'purpose'          => $validated['purpose'],
        ]);

        if ($httpRequest->has('delete_attachments')) {
            foreach ($httpRequest->delete_attachments as $attachmentId) {
                $attachment = $documentRequest->attachments()->find($attachmentId);
                if ($attachment) {
                    Storage::delete($attachment->file_path); 
                    $attachment->delete(); 
                }
            }
        }

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