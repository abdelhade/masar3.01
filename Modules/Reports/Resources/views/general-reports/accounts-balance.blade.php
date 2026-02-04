@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="account-node {{ $account->is_basic ? 'basic-account' : '' }}">
        <div class="account-item">
            @if ($account->children->count())
                <span class="toggle-icon">
                    <i class="fas fa-minus-circle"></i>
                </span>
            @else
                <span class="toggle-icon" style="visibility: hidden;">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                </span>
            @endif

            <span class="account-code">{{ $account->code }}</span>
            <span class="account-name">
                {{ $account->aname }}
                @if ($account->is_basic)
                    <span class="account-type">{{ __('Primary') }} ({{ $account->children->count() }})</span>
                @endif
            </span>
            <span style="{{ $account->balance < 0 ? 'color:red' : '' }}"
                class="account-balance {{ $account->balance < 0 ? 'debit-balance' : '' }}">
                {{ number_format($account->balance, 2) }}
            </span>
        </div>

        @if ($account->children->count())
            <div class="child-accounts">
                @foreach ($account->children->sortBy('code') as $child)
                    @include('reports.partials.account-node', [
                        'account' => $child,
                        'level' => ($level ?? 0) + 1,
                    ])
                @endforeach
            </div>
        @endif
    </div>

@endsection
