<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\ResidentUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads; 

new class extends Component {
    use ProfileValidationRules;
    use WithFileUploads; 

    // User Table Field
    public string $email = '';
    public $profile_photo;

    // Resident Table Fields
    public string $fname = '';
    public string $mname = '';
    public string $lname = '';
    public ?string $suffix = '';
    public ?string $phone_number = '';
    public ?string $birthdate = '';
    public ?string $birth_place = '';
    public ?string $sex = '';
    public ?string $civil_status = '';
    public ?string $citizenship = '';
    public ?string $mother_maiden_name = '';

    // File Uploads
    public $supporting_document; // Used for initial Account Verification
    public $update_supporting_document; // Used for Profile Update Requests

    public bool $showVerificationModal = false;

    #[Computed]
    public function latestUpdate()
    {
        return ResidentUpdateRequest::where('user_id', Auth::id())
            ->where('request_type', 'profile_update')
            ->latest()
            ->first();
    }

    #[Computed]
    public function isPending(): bool
    {
        return $this->latestUpdate?->status === 'pending';
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->email = $user->email;

        if ($user->resident) {
            $r = $user->resident;
            $this->fname = $r->fname ?? '';
            $this->mname = $r->mname ?? '';
            $this->lname = $r->lname ?? '';
            $this->suffix = $r->suffix ?? '';
            $this->phone_number = $r->phone_number ?? '';
            $this->birthdate = $r->birthdate ?? '';
            $this->birth_place = $r->birth_place ?? '';
            $this->sex = $r->sex ?? '';
            $this->civil_status = $r->civil_status ?? '';
            $this->citizenship = $r->citizenship ?? '';
            $this->mother_maiden_name = $r->mother_maiden_name ?? '';
        }
    }

    public function updateAccountInformation(): void
    {
        $user = Auth::user();

        // 1. Validate Base User Information Only
        $validatedUser = $this->validate([
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // 2. Update User Details Instantly
        $user->fill(['email' => $validatedUser['email']]);
        
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($this->profile_photo) {
            $user->profile_photo = $this->profile_photo->store('profile-photos', 'public');
            $this->profile_photo = null; // Clears the temporary preview to show the saved image
        }
        
        $user->save();

        $this->dispatch('profile-updated', name: 'Account details');
    }

    public function requestResidentUpdate(): void
    {
        $user = Auth::user();

        // Make sure only verified users with a resident record can trigger this
        if ($user->verification_status !== 'verified' || !$user->resident) {
            return; 
        }

        // 1. Validate Resident Information Only
        $validatedResident = $this->validate([
            'fname'                      => ['required', 'string', 'max:255'],
            'mname'                      => ['nullable', 'string', 'max:255'],
            'lname'                      => ['required', 'string', 'max:255'],
            'suffix'                     => ['nullable', 'string', 'max:50'],
            'phone_number'               => ['nullable', 'string', 'max:20'],
            'birthdate'                  => ['nullable', 'date'],
            'birth_place'                => ['nullable', 'string', 'max:255'],
            'sex'                        => ['nullable', 'in:male,female,other'],
            'civil_status'               => ['nullable', 'in:single,married,widowed'],
            'citizenship'                => ['nullable', 'string', 'max:100'],
            'mother_maiden_name'         => ['nullable', 'string', 'max:255'],
            'update_supporting_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $resident = $user->resident;

        $requestedResidentData = [
            'fname'              => $this->fname,
            'mname'              => $this->mname,
            'lname'              => $this->lname,
            'suffix'             => $this->suffix,
            'phone_number'       => $this->phone_number,
            'birthdate'          => $this->birthdate,
            'birth_place'        => $this->birth_place,
            'sex'                => $this->sex,
            'civil_status'       => $this->civil_status,
            'citizenship'        => $this->citizenship,
            'mother_maiden_name' => $this->mother_maiden_name,
        ];

        $currentResidentData = [
            'fname'              => $resident->fname,
            'mname'              => $resident->mname,
            'lname'              => $resident->lname,
            'suffix'             => $resident->suffix,
            'phone_number'       => $resident->phone_number,
            'birthdate'          => $resident->birthdate,
            'birth_place'        => $resident->birth_place,
            'sex'                => $resident->sex,
            'civil_status'       => $resident->civil_status,
            'citizenship'        => $resident->citizenship,
            'mother_maiden_name' => $resident->mother_maiden_name,
        ];

        // If ANY of the resident data changed...
        if ($requestedResidentData !== $currentResidentData) {
            
            $documentPath = null;
            if ($this->update_supporting_document) {
                $documentPath = $this->update_supporting_document->store('update-requests', 'public');
            }

            if ($user->role === 'admin') {
                $resident->update($requestedResidentData);
            } 
            elseif (!$this->isPending) { 
                ResidentUpdateRequest::create([
                    'user_id'             => $user->id,
                    'resident_id'         => $resident->id,
                    'request_type'        => 'profile_update', 
                    'current_data'        => $currentResidentData,
                    'requested_data'      => $requestedResidentData,
                    'supporting_document' => $documentPath,
                    'status'              => 'pending'
                ]);

                $this->update_supporting_document = null; 
                
                unset($this->latestUpdate);
                unset($this->isPending);

                $this->fill($currentResidentData);
            }
        }

        $this->dispatch('profile-updated', name: 'Resident request');
    }

    public function submitVerification(): void
    {
        $user = Auth::user();

        if ($user->verification_status === 'verified' || ($user->verification_status === 'pending' && $user->supporting_document)) {
            $this->showVerificationModal = false;
            $this->addError('supporting_document', 'You cannot submit a verification request at this time.');
            return;
        }

        $this->validate([
            'supporting_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], 
        ]);

        if ($user->verification_status === 'rejected' && $user->supporting_document) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->supporting_document);
        }

        $path = $this->supporting_document->store('verification-documents', 'public');

        $user->update([
            'supporting_document' => $path,
            'verification_status' => 'pending',
        ]);

        $this->showVerificationModal = false;
        $this->supporting_document = null; 

        $this->dispatch('verification-submitted');
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full space-y-12">
    {{-- 1. BASIC ACCOUNT SETTINGS (Always Visible & Instant) --}}
    <div>
        @include('partials.settings-heading')
        <x-pages::settings.layout :heading="__('Account Settings')" :subheading="__('Update your basic login details. Changes to these fields are applied instantly.')">
            <form wire:submit="updateAccountInformation" class="my-6 w-full space-y-6">
                
                {{-- Profile Photo --}}
                <div class="flex items-center gap-6">
                    <div class="shrink-0">
                        @if ($profile_photo)
                            <img src="{{ $profile_photo->temporaryUrl() }}" class="size-20 rounded-full object-cover border">
                        @elseif (Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" class="size-20 rounded-full object-cover border">
                        @else
                            <div class="size-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 border">
                                <flux:icon.user class="size-8" />
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <flux:input wire:model="profile_photo" :label="__('Profile Photo')" type="file" accept="image/*" />
                    </div>
                </div>

                <flux:separator />

                {{-- Account Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="email" :label="__('Email Address')" type="email" required />
                </div>

                {{-- Account Save Button (Removed the if unverified check so everyone can save their photo) --}}
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Account Details') }}
                    </flux:button>
                </div>
            </form>
        </x-pages::settings.layout>
    </div>

    <flux:separator />

    {{-- 2. RESIDENT INFORMATION & VERIFICATION STATUS --}}
    <div>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('Resident Information') }}</flux:heading>
            <flux:subheading>{{ __('Official records on file with the Barangay. Changes require admin approval.') }}</flux:subheading>
        </div>

        @if(Auth::user()->verification_status === 'verified')
            @php $r = Auth::user()->resident; @endphp
            
            {{-- Verified Badge --}}
            <div class="mb-6 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 rounded-lg flex justify-between items-center">
                <div class="flex items-center gap-2 text-green-700 dark:text-green-400 font-medium">
                    <flux:icon.check-circle class="size-5" />
                    <span>Verified Resident</span>
                </div>
                <span class="text-xs text-green-600 dark:text-green-500 font-mono uppercase tracking-wider">
                    ID: {{ $r->household_id ?? 'N/A' }}
                </span>
            </div>

            {{-- UPDATE REQUEST STATUS BANNERS --}}
            @if($this->latestUpdate)
                @if($this->latestUpdate->status === 'pending')
                    <div class="rounded-md bg-blue-50 dark:bg-blue-900/30 p-4 border border-blue-200 dark:border-blue-800 mb-6">
                        <div class="flex">
                            <flux:icon.information-circle class="size-5 text-blue-400 mt-0.5" />
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Update Request Pending</h3>
                                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">You have submitted changes to your official record. Your profile will reflect the new information once the Barangay approves it. The fields below are temporarily locked.</p>
                            </div>
                        </div>
                    </div>
                @elseif($this->latestUpdate->status === 'rejected')
                    <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800 mb-6">
                        <div class="flex">
                            <flux:icon.x-circle class="size-5 text-red-500 mt-0.5" />
                            <div class="ml-3 space-y-2">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Update Request Rejected</h3>
                                <p class="text-sm text-red-700 dark:text-red-400">Your recent profile update request was not approved.</p>
                                
                                {{-- Show Admin Notes if they exist --}}
                                @if($this->latestUpdate->admin_notes)
                                    <div class="bg-red-100 dark:bg-red-900/50 p-3 rounded text-sm text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
                                        <strong>Reason:</strong> {{ $this->latestUpdate->admin_notes }}
                                    </div>
                                @endif
                                
                                <p class="text-sm text-red-700 dark:text-red-400">You may correct your information below and submit a new request.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Resident Update Form Wrapper added here! --}}
            <form wire:submit="requestResidentUpdate" class="space-y-6">
                {{-- Name Details --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <flux:input wire:model="fname" :label="__('First Name')" type="text" required :disabled="$this->isPending" class="md:col-span-1" />
                    <flux:input wire:model="mname" :label="__('Middle Name')" type="text" :disabled="$this->isPending" class="md:col-span-1" />
                    <flux:input wire:model="lname" :label="__('Last Name')" type="text" required :disabled="$this->isPending" class="md:col-span-1" />
                    <flux:input wire:model="suffix" :label="__('Suffix (e.g., Jr., III)')" type="text" :disabled="$this->isPending" class="md:col-span-1" />
                </div>

                {{-- Contact Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="phone_number" :label="__('Phone Number')" type="tel" :disabled="$this->isPending" />
                </div>

                {{-- Personal Details --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model="birthdate" :label="__('Birthdate')" type="date" :disabled="$this->isPending" />
                    <div class="md:col-span-2">
                        <flux:input wire:model="birth_place" :label="__('Place of Birth')" type="text" :disabled="$this->isPending" />
                    </div>
                    
                    <flux:select wire:model="sex" :label="__('Sex')" :disabled="$this->isPending">
                        <option value="">Select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </flux:select>

                    <flux:select wire:model="civil_status" :label="__('Civil Status')" :disabled="$this->isPending">
                        <option value="">Select...</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="widowed">Widowed</option>
                    </flux:select>

                    <flux:input wire:model="citizenship" :label="__('Citizenship')" type="text" :disabled="$this->isPending" />
                </div>

                {{-- Family Details --}}
                <flux:input wire:model="mother_maiden_name" :label="__('Mother\'s Maiden Name')" type="text" :disabled="$this->isPending" />

                <flux:separator />

                {{-- Supporting Document for Updates --}}
                <div>
                    <flux:input 
                        type="file" 
                        wire:model="update_supporting_document" 
                        :label="__('Supporting Document (Optional)')"
                        :description="__('If you are changing sensitive information, please upload a supporting document. Allowed: JPG, PNG, PDF.')"
                        :disabled="$this->isPending"
                    />
                    <div wire:loading wire:target="update_supporting_document" class="text-sm text-zinc-500 mt-2">
                        Uploading...
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" :disabled="$this->isPending">
                        {{ $this->isPending ? __('Request Pending') : __('Save & Request Update') }}
                    </flux:button>
                </div>
            </form>

        @else
            {{-- UNVERIFIED STATE UI --}}
            <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-orange-600">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0A1.5 1.5 0 0 0 4.5 12v6.75a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5V12a1.5 1.5 0 0 0-1.5-1.5h-13.5Z" />
                    </svg>
                </div>
                
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                    @if(Auth::user()->verification_status === 'pending' && Auth::user()->supporting_document)
                        Verification in Progress
                    @elseif(Auth::user()->verification_status === 'rejected')
                        Verification Rejected
                    @else
                        Identity Verification Required
                    @endif
                </h3>

                <p class="mt-1 text-sm text-zinc-500 mb-6 max-w-md mx-auto">
                    @if(Auth::user()->verification_status === 'pending' && Auth::user()->supporting_document)
                        You have successfully submitted your documents. The barangay is currently reviewing your request.
                    @elseif(Auth::user()->verification_status === 'rejected')
                        Your previous request was not approved. Please review your documents and try submitting again.
                    @else
                        To view your full resident record and access barangay services, you must verify your identity by uploading a valid ID.
                    @endif
                </p>

                @if(Auth::user()->verification_status !== 'pending' || (Auth::user()->verification_status === 'pending' && !Auth::user()->supporting_document))
                    <flux:button type="button" wire:click="$set('showVerificationModal', true)" variant="primary">
                        {{ Auth::user()->verification_status === 'rejected' ? 'Try Again' : 'Verify Account' }}
                    </flux:button>
                @else
                     <flux:button disabled>
                        Waiting for Approval...
                    </flux:button>
                @endif
            </div>
        @endif
    </div>

    {{-- MODALS & NOTIFICATIONS --}}
    <flux:modal wire:model="showVerificationModal" class="md:w-[500px]">
        <form wire:submit="submitVerification" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Verify Your Account') }}</flux:heading>
                <flux:subheading>{{ __('Please upload a valid ID to verify your identity and access barangay services.') }}</flux:subheading>
            </div>

            <div>
                <flux:input 
                    type="file" 
                    wire:model="supporting_document" 
                    :label="__('Valid ID / Supporting Document')"
                    :description="__('Allowed formats: JPG, PNG, PDF. Max size: 5MB.')"
                    required 
                />
                
                <div wire:loading wire:target="supporting_document" class="text-sm text-zinc-500 mt-2">
                    Uploading preview...
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showVerificationModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Submit Document') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    @if ($this->showDeleteUser)
        <flux:separator />
        <livewire:pages::settings.delete-user-form />
    @endif
    
    <x-action-message on="verification-submitted">
        {{ __('Verification documents submitted successfully.') }}
    </x-action-message>
    
    <x-action-message on="profile-updated">
        {{ __('Settings successfully saved.') }}
    </x-action-message>
</section>