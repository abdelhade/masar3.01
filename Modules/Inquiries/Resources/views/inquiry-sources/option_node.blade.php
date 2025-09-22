<option value="{{ $source->id }}" {{ old('parent_id') == $source->id ? 'selected' : '' }}>
    {{ $prefix }}{{ $source->name }}
</option>

@if ($source->childrenRecursive && $source->childrenRecursive->count())
    @foreach ($source->childrenRecursive as $child)
        @include('inquiries::inquiry-sources.option_node', [
            'source' => $child,
            'prefix' => $prefix . '— ',
        ])
    @endforeach
@endif
