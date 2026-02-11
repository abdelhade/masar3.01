@props(['branches', 'selected' => null, 'model' => null])

@if ($branches->count() > 1)
    <div class="mb-3 col-lg-2">
        <label for="branch_id">{{ __('Branch') }}</label>
        <select class="form-control" id="branch_id" name="branch_id"
            @if ($model) wire:model="{{ $model }}" @endif>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" {{ (old('branch_id') ?? $selected) == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
@elseif($branches->count() === 1)
    <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}"
        @if ($model) wire:model="{{ $model }}" @endif>
    {{-- <div class="mb-3 col-lg-3">
        <label class="form-label">{{ __('Branch') }}</label>
        <input type="text" class="form-control" value="{{ $branches->first()->name }}" disabled>
    </div> --}}
@endif
