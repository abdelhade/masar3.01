<li>
    <div class="tree-item {{ $account->is_basic == 1 ? 'basic-account' : '' }}">
        <div class="account-info">
            @if ($account->children?->count() ?? 0)
                <i class="fas fa-folder-open text-warning" style="font-size: 1.3rem;"></i>
            @else
                <i class="fas fa-file-invoice-dollar text-info" style="font-size: 1.2rem;"></i>
            @endif

            <span class="account-code">{{ $account->code }}</span>
            <span class="account-name">{{ $account->aname }}</span>

            @if ($account->is_basic == 1)
                <span class="basic-badge">حساب أساسي</span>
            @endif

            @if ($account->is_basic == 1 && ($account->children?->count() ?? 0))
                <span class="children-count">{{ $account->children->count() }}</span>
            @endif

            <span class="account-balance {{ $account->balance < 0 ? 'negative' : '' }}">
                {{ number_format($account->balance ?? 0, 2) }}
            </span>
        </div>

        @if ($account->children?->count() ?? 0)
            <span class="toggle-icon">
                <i class="fas fa-minus"></i>
            </span>
        @endif
    </div>

    @if ($account->children?->count() ?? 0)
        <ul class="nested">
            @foreach ($account->children as $child)
                @include('reports::accounts-reports.partials.account-node', ['account' => $child])
            @endforeach
        </ul>
    @endif
</li>
