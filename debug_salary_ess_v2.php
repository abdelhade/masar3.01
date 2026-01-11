<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Accounts\Models\AccHead;
use Modules\HR\Models\Employee;
use Modules\HR\Models\AttendanceProcessing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$empId = 2; // ess
$employee = Employee::find($empId);

echo "=== Employee: {$employee->name} ===\n";

echo "=== Processing Periods ===\n";
$processings = AttendanceProcessing::where('employee_id', $empId)->get();
foreach ($processings as $p) {
    echo "ID: {$p->id} | Start: {$p->period_start} | End: {$p->period_end} | Status: {$p->status}\n";
}

echo "\n=== AccHead Hierarchy for Employee ===\n";
$accounts = AccHead::where('accountable_id', $empId)
    ->where('accountable_type', Employee::class)
    ->get();

foreach ($accounts as $acc) {
    $parent = DB::table('acc_head')->where('id', $acc->parent_id)->first();
    echo "Acc ID: {$acc->id} | Name: {$acc->aname} | Parent ID: {$acc->parent_id} | Parent Code: " . ($parent->code ?? 'N/A') . " | Parent Name: " . ($parent->aname ?? 'N/A') . "\n";
}

echo "\n=== Verifying getSettled* Logic Manually ===\n";
$startDate = Carbon::parse('2026-01-01');
$endDate = Carbon::parse('2026-01-11');

$settled = DB::table('journal_details')
    ->join('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
    ->join('operhead', 'journal_details.op_id', '=', 'operhead.id')
    ->join('acc_head as parents', 'acc_head.parent_id', '=', 'parents.id')
    ->where('acc_head.accountable_type', Employee::class)
    ->where('acc_head.accountable_id', $empId)
    // ->where('parents.code', '110602')
    ->whereIn('operhead.pro_type', [75, 76, 79])
    ->select('journal_details.*', 'acc_head.aname as account_name', 'parents.code as parent_code', 'operhead.pro_date', 'operhead.pro_type')
    ->get();

foreach ($settled as $s) {
    echo "Date: {$s->pro_date} | Type: {$s->pro_type} | Acc: {$s->account_name} | Parent Code: {$s->parent_code} | Dr: {$s->debit} | Cr: {$s->credit}\n";
}
