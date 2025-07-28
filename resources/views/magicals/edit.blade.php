
    <form wire:submit.prevent="update" class="text-end" style="font-family: 'Cairo', sans-serif; direction: rtl;">
        <div class="mb-3">
            <label for="magic_name" class="form-label">{{ __('magic_name') }}</label>
            <input type="text" id="magic_name" wire:model.defer="magic_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="magic_link" class="form-label">{{ __('magic_link') }}</label>
            <input type="text" id="magic_link" wire:model.defer="magic_link" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" id="is_journal" wire:model.defer="is_journal" class="form-check-input">
            <label for="is_journal" class="form-check-label">{{ __('is_journal') }}</label>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('save_changes') }}</button>
    </form>
