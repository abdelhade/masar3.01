{{-- عرض الحساب الحالي --}}
@php
    $hasChildren = $account->relationLoaded('children') && $account->children->isNotEmpty();
    $isBasic = $account->is_basic == 1;
    $rowClasses = 'account-row level-' . $level;
    if (isset($parentId)) {
        $rowClasses .= ' children-' . $parentId;
    }
    if ($isBasic) {
        $rowClasses .= ' account-basic';
    } else {
        $rowClasses .= ' account-sub';
    }
    $buttonClasses = 'collapse-toggle me-2';
    $parentIdentifier = isset($parentId) ? (string) $parentId : '';
    // استخدام balance للعرض في الجدول، و totalWithChildren للبيانات عند طي الحسابات الفرعية
    $balanceValue = (float) ($account->balance ?? 0);
    $totalWithChildrenValue = (float) ($account->totalWithChildren ?? $account->balance ?? 0);
    $section = $section ?? null;
@endphp

<tr class="{{ $rowClasses }}"
    data-level="{{ $level }}"
    data-account-id="{{ $account->id }}"
    data-parent-id="{{ $parentIdentifier }}"
    data-has-children="{{ $hasChildren ? 1 : 0 }}"
    data-balance="{{ $balanceValue }}"
    data-total-with-children="{{ $totalWithChildrenValue }}"
    @if($section) data-section="{{ $section }}" @endif>
    <td class="align-middle">
        @if($hasChildren)
            <button type="button"
                class="{{ $buttonClasses }}"
                data-account-id="{{ $account->id }}"
                aria-expanded="true"
                onclick="toggleChildren(this)">
                <i class="fas fa-minus-square toggle-icon"></i>
            </button>
            @if($isBasic)
                <i class="fas fa-folder-open text-primary me-2"></i>
            @else
                <i class="fas fa-folder text-info me-2"></i>
            @endif
        @else
            <span class="placeholder-toggle me-2"></span>
            @if($isBasic)
                <i class="fas fa-file-alt text-primary me-2"></i>
            @else
                <i class="fas fa-file text-secondary me-2"></i>
            @endif
        @endif
        <span class="{{ $isBasic ? 'fw-bold text-primary' : 'text-dark' }}">
            {{ $account->code }} - {{ $account->aname }}
        </span>
        @if($isBasic)
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2 small">{{ __('أساسي') }}</span>
        @endif
    </td>
    <td class="text-end align-middle {{ $isBasic ? 'fw-bold text-primary' : 'text-dark' }}">
        {{ number_format($balanceValue, 2) }}
    </td>
</tr>

@if($hasChildren)
    @foreach($account->children as $child)
        @include('reports::accounts-reports.partials.account-row-recursive', [
            'account' => $child,
            'level' => $level + 1,
            'parentId' => $account->id,
            'section' => $section ?? null,
        ])
    @endforeach
@endif
