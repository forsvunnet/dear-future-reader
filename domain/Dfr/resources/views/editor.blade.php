<form wire:submit="save" class="p-4">
    @if ($error !== null)
        <p role="alert" class="mb-2 text-red-700 dark:text-red-400">{{ $error }}</p>
    @endif
    <label for="content" class="sr-only">Content of {{ $fileName }}</label>
    <textarea id="content" wire:model="content" rows="20"
        class="w-full rounded border border-stone-300 bg-white p-2 font-mono text-sm dark:border-stone-700 dark:bg-stone-900"></textarea>
    <div class="mt-2 flex gap-4">
        <button type="submit" class="rounded bg-stone-900 px-3 py-1 text-white dark:bg-stone-100 dark:text-stone-900">Save</button>
        <button type="button" wire:click="cancel" class="underline">Cancel</button>
    </div>
</form>
