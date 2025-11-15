<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Ø£Ø­Ø¯Ø« Ø§Ù„Ø­Ø³Ø§Ø¨Ø§Øª</div>
            <div class="card-body p-0">
                @php
                use Modules\\Accounts\\Models\\AccHead;

                $lastAccounts = AccHead::with('haveParent')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();


                @endphp

                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Ø§Ù„Ø§Ø³Ù…</th>
                            <th>Ø§Ù„ØªÙ„ÙŠÙÙˆÙ†</th>
                            <th>code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lastAccounts as $acc)
                        <tr>
                            <td>{{ $acc->aname }} -> {{ $acc->haveParent?->aname }}</td>
                            <td>{{ $acc->phone }}</td>
                            <td>{{ $acc->code }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">logins</div>
            <div class="card-body p-0">
                @php
                $lastLogins = \App\Models\LoginSession::with('user')
                ->orderBy('login_at', 'desc')
                ->take(5)
                ->get();
                @endphp

                <table class="table table-responsive table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…</th>
                            <th>IP</th>
                            <th>ÙˆÙ‚Øª Ø§Ù„Ø¯Ø®ÙˆÙ„</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lastLogins as $login)
                        <tr>
                            <td>{{ $login->user->name ?? 'â€”' }}</td>
                            <td>{{ $login->ip_address }}</td>
                            <td>{{ $login->login_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
