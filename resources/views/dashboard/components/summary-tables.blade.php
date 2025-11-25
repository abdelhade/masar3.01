<div class="grid-2-col mb-6 gap-4">
    <!-- Latest Accounts Table -->
    <div class="card hover-lift transition-base">
        <div class="card-header border-b border-border-light p-4" style="border-left: 4px solid #34d3a3;">
            <h3 class="text-section-title mb-0">أحدث الحسابات</h3>
        </div>
        <div class="container-table">
            @php
            use Modules\Accounts\Models\AccHead;

            $lastAccounts = AccHead::with('haveParent')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
            @endphp

            <table class="table table-sticky">
                <thead>
                    <tr>
                        <th class="text-table-header">الاسم</th>
                        <th class="text-table-header">التليفون</th>
                        <th class="text-table-header">كود</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lastAccounts as $acc)
                    <tr>
                        <td class="text-table">{{ $acc->aname }} -> {{ $acc->haveParent?->aname }}</td>
                        <td class="text-table">{{ $acc->phone }}</td>
                        <td class="text-table">{{ $acc->code }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Latest Logins Table -->
    <div class="card hover-lift transition-base">
        <div class="card-header border-b border-border-light p-4" style="border-left: 4px solid #1aa1c4;">
            <h3 class="text-section-title mb-0">آخر تسجيلات الدخول</h3>
        </div>
        <div class="container-table">
            @php
            $lastLogins = \App\Models\LoginSession::with('user')
            ->orderBy('login_at', 'desc')
            ->take(5)
            ->get();
            @endphp

            <table class="table table-sticky">
                <thead>
                    <tr>
                        <th class="text-table-header">المستخدم</th>
                        <th class="text-table-header">IP</th>
                        <th class="text-table-header">وقت الدخول</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lastLogins as $login)
                    <tr>
                        <td class="text-table">{{ $login->user->name ?? '—' }}</td>
                        <td class="text-table">{{ $login->ip_address }}</td>
                        <td class="text-table">{{ $login->login_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
