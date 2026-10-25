<form wire:submit="save" class="p-4">
    @if ($error !== null)
        <p role="alert" class="dfr-danger mb-2">{{ $error }}</p>
    @endif
    <label for="content" class="sr-only">Content of {{ $fileName }}</label>
    <textarea id="content" wire:model="content" rows="20" class="dfr-input w-full"></textarea>
    <div class="mt-2 flex gap-2">
        <button type="submit" class="dfr-button dfr-button-primary">Save</button>
        <button type="button" wire:click="cancel" class="dfr-button">Cancel</button>
    </div>
</form>
