<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Accounts\Models\AccHead;
use Modules\HR\Models\Employee;
use Illuminate\Support\Facades\DB;

$empId = 2; // ess
$employee = Employee::find($empId);

if (!$employee) {
    echo "Employee not found.\n";
    exit;
}

echo "=== Accounts for Employee: {$employee->name} (ID: {$empId}) ===\n";
$accounts = AccHead::where('accountable_id', $empId)
    ->where('accountable_type', Employee::class)
    ->with('haveParent')
    ->get();

foreach ($accounts as $acc) {
    $parentCode = $acc->haveParent->code ?? 'N/A';
    echo "ID: {$acc->id} | Name: {$acc->aname} | Parent Code: {$parentCode} | Balance: {$acc->balance}\n";
}

echo "\n=== Journals for Employee 2 (pro_types 75, 76, 79) ===\n";
$journals = DB::table('operhead')
    ->where('emp_id', $empId)
    ->whereIn('pro_type', [75, 76, 79])
    ->get();

if ($journals->isEmpty()) {
    echo "No settlement journals found.\n";
} else {
    foreach ($journals as $j) {
        $details = DB::table('journal_details')
            ->join('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
            ->where('journal_details.op_id', $j->id)
            ->select('acc_head.aname', 'journal_details.debit', 'journal_details.credit')
            ->get();
            
        echo "OpID: {$j->id} | Type: {$j->pro_type} | Date: {$j->pro_date} | Info: {$j->info}\n";
        foreach ($details as $d) {
            echo "   -> Acc: {$d->aname} | Dr: {$d->debit} | Cr: {$d->credit}\n";
        }
    }
}
