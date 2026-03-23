<x-layouts::app :title="__('Edit Complaint')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 lg:p-8 max-w-3xl mx-auto">
        
        <div class="flex items-center gap-4 mb-2">
            <flux:button href="{{ route('resident.requests.index', ['type' => 'complaints']) }}" variant="ghost" icon="arrow-left" size="sm" />
            <div>
                <flux:heading size="lg">Edit Complaint: {{ $complaint->case_number }}</flux:heading>
                <flux:subheading>Update your incident details before the investigation begins.</flux:subheading>
            </div>
        </div>

        <flux:card>
            <form action="{{ route('resident.complaints.update', $complaint->id) }}" method="POST" class="flex flex-col gap-6">
                @csrf
                @method('PUT')

                {{-- Note: Respondent isn't editable here by design, as it's tied to system logic during creation. If you want to make it editable, you can add a text input for it! --}}

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select name="complaint_type_id" label="Type of Complaint" required>
                        <option value="" disabled>Select category...</option>
                        @foreach($complaintTypes as $type)
                            <option value="{{ $type->id }}" {{ $complaint->complaint_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </flux:select>

                    <flux:input 
                        type="date" 
                        name="incident_date" 
                        label="Date of Incident" 
                        value="{{ $complaint->incident_at->format('Y-m-d') }}" 
                        max="{{ date('Y-m-d') }}" 
                        required 
                    />
                </div>

                <flux:input 
                    name="location" 
                    label="Exact Location of Incident" 
                    value="{{ $complaint->location }}" 
                    placeholder="e.g. In front of Brgy Hall, Purok 2" 
                    required 
                />

                <flux:textarea 
                    name="incident_details" 
                    label="Full Narrative / Details" 
                    rows="6" 
                    required 
                    placeholder="Describe exactly what happened..."
                >{{ $complaint->incident_details }}</flux:textarea>

                <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('resident.requests.index', ['type' => 'complaints']) }}" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </form>
        </flux:card>
    </div>

</x-layouts::app>