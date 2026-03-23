<div class="flex flex-col gap-6">

    {{-- Search and Actions --}}
    <flux:card>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-between">

            {{-- Search --}}
            <flux:input
                wire:model.live="search"
                placeholder="Search resident name..."
                icon="magnifying-glass"
                class="w-full sm:w-1/3"
            />

        </div>
    </flux:card>


    {{-- Residents Table --}}
    <flux:card>
        <flux:table>

            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Gender</flux:table.column>
                <flux:table.column>Birthdate</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($residents as $resident)
                    <flux:table.row>

                        {{-- Name --}}
                        <flux:table.cell>
                            {{ $resident->first_name }} {{ $resident->last_name }}
                        </flux:table.cell>

                        {{-- Gender --}}
                        <flux:table.cell>
                            {{ $resident->gender }}
                        </flux:table.cell>

                        {{-- Birthdate --}}
                        <flux:table.cell>
                            {{ $resident->birthdate }}
                        </flux:table.cell>

                        {{-- Status --}}
                        <flux:table.cell>
                            <flux:badge color="green">
                                Active
                            </flux:badge>
                        </flux:table.cell>

                        {{-- Actions --}}
                        <flux:table.cell>

                            <div class="flex gap-2">

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="view({{ $resident->id }})"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    wire:click="edit({{ $resident->id }})"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $resident->id }})"
                                />

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-gray-500">
                            No residents found.
                        </flux:table.cell>
                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $residents->links() }}
        </div>

    </flux:card>

</div>