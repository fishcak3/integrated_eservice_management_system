<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads; // Required for file uploads
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use ProfileValidationRules;
    use WithFileUploads; // Enable file handling

    public string $fname = '';
    public string $mname = '';
    public string $lname = '';
    public string $email = '';
    
    // Added profile photo property
    public $profile_photo;

    // verification properties
    public $supporting_document; 
    public bool $showVerificationModal = false;

    public function mount(): void
    {
        $user = Auth::user();
        
        $this->fname = optional($user->resident)->fname ?? '';
        $this->mname = optional($user->resident)->mname ?? '';
        $this->lname = optional($user->resident)->lname ?? '';
        
        $this->email = $user->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'profile_photo' => ['nullable', 'image', 'max:2048'], // Added validation
            'fname'  => ['required', 'string', 'max:255'],
            'mname'  => ['nullable', 'string', 'max:255'],
            'lname'  => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill(['email' => $validated['email']]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Added Profile Photo saving logic
        if ($this->profile_photo) {
            $user->profile_photo = $this->profile_photo->store('profile-photos', 'public');
        }

        $user->save();

        if ($user->resident) {
            $user->resident->update([
                'fname' => $validated['fname'],
                'mname' => $validated['mname'],
                'lname' => $validated['lname'],
            ]);
        }

        $this->dispatch('profile-updated', name: $validated['fname']);
    }

    /**
     * Handle the document upload and verification request
     */
    public function submitVerification(): void
    {
        $this->validate([
            'supporting_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        $path = $this->supporting_document->store('verification-documents', 'public');

        Auth::user()->update([
            'supporting_document' => $path,
            'verification_status' => 'pending',
        ]);

        $this->showVerificationModal = false;
        $this->supporting_document = null; // Reset the file input

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
    <div>
        @include('partials.settings-heading')
        <x-pages::settings.layout :heading="__('Account Settings')" :subheading="__('Update your login details.')">
            <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
                
                {{-- Added Profile Photo Section --}}
                <div class="flex items-center gap-6">
                    <div class="shrink-0">
                        @if ($profile_photo)
                            {{-- Preview the uploaded file --}}
                            <img src="{{ $profile_photo->temporaryUrl() }}" class="size-20 rounded-full object-cover border border-zinc-200 dark:border-zinc-700">
                        @elseif (Auth::user()->profile_photo)
                            {{-- Show existing photo --}}
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" class="size-20 rounded-full object-cover border border-zinc-200 dark:border-zinc-700">
                        @else
                            {{-- Fallback --}}
                            <div class="size-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <flux:input wire:model="profile_photo" :label="__('Profile Photo')" type="file" accept="image/*" />
                        <div wire:loading wire:target="profile_photo" class="mt-2 text-xs text-zinc-500">Uploading...</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="fname" :label="__('First Name')" type="text" required />
                    <flux:input wire:model="mname" :label="__('Middle Name')" type="text" />
                    <div class="md:col-span-2">
                        <flux:input wire:model="lname" :label="__('Last Name')" type="text" required />
                    </div>
                </div>

                <flux:input wire:model="email" :label="__('Email')" type="email" required />

                @if ($this->hasUnverifiedEmail)
                    {{-- Existing logic --}}
                @endif

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                </div>
            </form>
        </x-pages::settings.layout>
    </div>

    <flux:separator />

    <div>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('Resident Information') }}</flux:heading>
            <flux:subheading>{{ __('Official records on file with the Barangay.') }}</flux:subheading>
        </div>

        @if(Auth::user()->verification_status === 'verified')
            @php $r = Auth::user()->resident; @endphp
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-2 text-green-700 dark:text-green-400 font-medium">
                        <flux:icon.check-circle class="size-5" />
                        <span>Verified Resident</span>
                    </div>
                    <span class="text-xs text-green-600 dark:text-green-500 font-mono uppercase tracking-wider">
                        ID: {{ $r->household_id ?? 'N/A' }}
                    </span>
                </div>

                <div class="p-6 md:p-8 space-y-8">
                    <div>
                        <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4">Personal Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <span class="block text-sm text-zinc-500">Birthdate</span>
                                <span class="font-medium">{{ $r->birthdate ? \Carbon\Carbon::parse($r->birthdate)->format('F d, Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Sex</span>
                                <span class="font-medium capitalize">{{ $r->sex ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Civil Status</span>
                                <span class="font-medium capitalize">{{ $r->civil_status ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Phone Number</span>
                                <span class="font-medium">{{ $r->phone_number ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Voter Status</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $r->voter ? 'bg-blue-100 text-blue-800' : 'bg-zinc-100 text-zinc-800' }}">
                                    {{ $r->voter ? 'Registered Voter' : 'Non-Voter' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Mother's Maiden Name</span>
                                <span class="font-medium">{{ $r->mother_maiden_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <flux:separator />

                    <div>
                        <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4">Current Address</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <span class="block text-sm text-zinc-500">Full Address</span>
                                <span class="font-medium">
                                    {{ collect([$r->sitio, $r->purok, $r->zone, $r->street, $r->barangay, $r->municipality, $r->province])->filter()->join(', ') }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-sm text-zinc-500">Region</span>
                                <span class="font-medium">{{ $r->region ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
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
                    <flux:button wire:click="$set('showVerificationModal', true)" variant="primary">
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

    <flux:modal wire:model="showVerificationModal" class="md:w-96">
        <div class="p-6">
            <div class="mb-4">
                <flux:heading size="lg">{{ __('Submit Verification') }}</flux:heading>
                <flux:subheading>{{ __('Please upload a valid Government ID or Barangay Certificate.') }}</flux:subheading>
            </div>

            <form wire:submit="submitVerification" class="space-y-6">
                <flux:input 
                    type="file" 
                    wire:model="supporting_document" 
                    label="Supporting Document"
                    description="Allowed: JPG, PNG, PDF. Max 5MB."
                    required
                />
                
                <div wire:loading wire:target="supporting_document" class="text-sm text-zinc-500">
                    Uploading...
                </div>

                <div class="flex gap-2 justify-end">
                    <flux:button wire:click="$set('showVerificationModal', false)" variant="subtle">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Submit') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    @if ($this->showDeleteUser)
        <flux:separator />
        <livewire:pages::settings.delete-user-form />
    @endif
    
    <x-action-message on="verification-submitted">
        {{ __('Verification documents submitted successfully.') }}
    </x-action-message>
</section>