<li>
    <div class="tree-item">
        @if($account->children->count())
            <span class="toggle-icon"><i class="fa fa-folder-open"></i></span>
        @else
            <span class="no-toggle-icon"></span>
        @endif

        <span style="font-size: 1.2rem;" class="account-name {{ $account->is_basic == 1 ? 'text-primary' : '' }}">{{ $account->code }}-{{ $account->aname }} - ( {{ $account->is_basic == 1 ? $account->children->count() : '' }})</span>
    </div>

    @if($account->children->count())
        <ul class="nested hidden">
            @foreach($account->children as $child)
                @include('reports.partials.account-node', ['account' => $child])
            @endforeach
        </ul>
    @endif
</li>
