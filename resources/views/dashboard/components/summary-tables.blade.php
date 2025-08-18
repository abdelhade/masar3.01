<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">أحدث الحسابات</div>
            <div class="card-body p-0">
                @php
                use App\Models\AccHead;

                $lastAccounts = AccHead::with('parent')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();


                @endphp

                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>التليفون</th>
                            <th>code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lastAccounts as $acc)
                        <tr>
                            <td>{{ $acc->aname }} -> {{ $acc->parent->aname }}</td>
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
                            <th>المستخدم</th>
                            <th>IP</th>
                            <th>وقت الدخول</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lastLogins as $login)
                        <tr>
                            <td>{{ $login->user->name ?? '—' }}</td>
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