@props(['component', 'level'])

<tr class="{{ $level > 0 ? 'bg-light' : '' }}">
    <td style="padding-left: {{ $level * 20 + 10 }}px;">
        @if($level > 0) <i class="fas fa-level-up-alt fa-rotate-90 me-2 text-muted"></i> @endif
        {{ $component['name'] }}
        @if($component['has_recipe'])
            <span class="badge bg-info ms-1" style="font-size: 0.7em;">{{ __('Manufactured') }}</span>
        @endif
    </td>
    <td class="text-center">{{ number_format($component['quantity_needed'], 2) }}</td>
    <td class="text-center">{{ number_format($component['unit_cost'], 2) }}</td>
    <td class="text-center">{{ number_format($component['total_cost'], 2) }}</td>
</tr>

@if(!empty($component['components']))
    @foreach($component['components'] as $child)
        @include('manufacturing::livewire.partials.manufacturing-cost-row', ['component' => $child, 'level' => $level + 1])
    @endforeach
@endif
