@props(['account', 'level' => 0, 'type' => 'revenue'])

@php
    $balance = $account->balance ?? 0;
    $childrenTotal = $account->childrenTotal ?? 0;
    $totalWithChildren = $account->totalWithChildren ?? 0;
    $hasChildren = isset($account->children) && $account->children->count() > 0;
    $textColor = $type === 'revenue' ? 'text-success' : 'text-danger';
    $paddingRight = $level * 25 . 'px';
    $showRow = $totalWithChildren != 0;
@endphp

@if ($showRow)
    <tr class="account-row {{ $hasChildren ? 'has-children' : '' }} level-{{ $level }}"
        data-level="{{ $level }}" data-account-id="{{ $account->id }}">
        <td style="padding-right: {{ $paddingRight }};">
            @if ($hasChildren)
                <i class="fas fa-minus-square toggle-icon text-primary" style="cursor: pointer; margin-left: 8px;"
                    onclick="toggleChildren(this, {{ $account->id }})"></i>
                <strong>{{ $account->code }}</strong>
            @else
                <i class="fas fa-circle text-muted" style="font-size: 6px; margin-left: 8px;"></i>
                {{ $account->code }}
            @endif
        </td>
        <td>
            @if ($hasChildren)
                <strong>{{ $account->aname }}</strong>
            @else
                {{ $account->aname }}
            @endif
        </td>
        <td class="text-end {{ $hasChildren ? 'fw-bold' : '' }} {{ $textColor }}">
            @if ($balance > 0)
                {{ number_format($balance, 2) }}
            @endif
        </td>
        <td class="text-end fw-bold {{ $textColor }}">
            @if ($totalWithChildren > 0)
                {{ number_format($totalWithChildren, 2) }}
            @endif
        </td>
    </tr>

    @if ($hasChildren)
        @foreach ($account->children as $child)
            <x-reports::account-tree-row :account="$child" :level="$level + 1" :type="$type" />
        @endforeach
    @endif
@endif
