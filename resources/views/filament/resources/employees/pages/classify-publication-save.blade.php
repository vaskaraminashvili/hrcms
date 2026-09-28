<x-filament::button
    type="button"
    wire:click.async="savePublication({{ (int) $publicationId }})"
    :disabled="$isSaving"
>
    {{ $isSaving
        ? __('filament.personal_file.publications.classify_saving')
        : __('filament.save') }}
</x-filament::button>
