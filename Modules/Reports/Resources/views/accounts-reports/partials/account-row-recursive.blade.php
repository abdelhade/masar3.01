{{-- عرض الحساب الحالي --}}
@php
    $hasChildren = $account->relationLoaded('children') && $account->children->isNotEmpty();
    $rowClasses = 'account-row level-' . $level;
    if (isset($parentId)) {
        $rowClasses .= ' children-' . $parentId;
    }
    $buttonClasses = 'collapse-toggle me-2';
    $parentIdentifier = isset($parentId) ? (string) $parentId : '';
    $balanceValue = (float) ($account->totalWithChildren ?? $account->balance ?? 0);
@endphp

<tr class="{{ $rowClasses }} {{ $account->is_basic == 1 ? 'fw-bold' : '' }}"
    data-level="{{ $level }}"
    data-account-id="{{ $account->id }}"
    data-parent-id="{{ $parentIdentifier }}"
    data-has-children="{{ $hasChildren ? 1 : 0 }}"
    data-balance="{{ $balanceValue }}">
    <td class="align-middle">
        @if($hasChildren)
            <button type="button"
                class="{{ $buttonClasses }}"
                data-account-id="{{ $account->id }}"
                aria-expanded="true"
                onclick="toggleChildren(this)">
                <i class="fas fa-minus-square toggle-icon"></i>
            </button>
            <i class="fas fa-folder-open text-warning me-2"></i>
        @else
            <span class="placeholder-toggle me-2"></span>
            <i class="fas fa-file text-muted me-2"></i>
        @endif
        <span class="{{ $account->is_basic == 1 ? 'fw-bold' : '' }}">
            {{ $account->code }} - {{ $account->aname }}
        </span>
    </td>
    <td class="text-end align-middle {{ $account->is_basic == 1 ? 'fw-bold' : '' }}">
        {{ number_format($account->balance ?? 0, 2) }}
    </td>
</tr>

@if($hasChildren)
    @foreach($account->children as $child)
        @include('reports::accounts-reports.partials.account-row-recursive', [
            'account' => $child,
            'level' => $level + 1,
            'parentId' => $account->id,
        ])
    @endforeach
@endif
