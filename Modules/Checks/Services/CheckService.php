<?php

namespace Modules\Checks\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Checks\Models\Check;

class CheckService
{
    public function __construct(
        private CheckAccountingService $accountingService,
        private CheckPortfolioService $portfolioService
    ) {}

    /**
     * Create a new check
     */
    public function createCheck(array $data): Check
    {
        DB::beginTransaction();

        try {
            $proType = $data['type'] === 'incoming' ? 65 : 66;
            $portfolioAccount = $this->portfolioService->getPortfolioAccount($data['type']);

            if (! $portfolioAccount) {
                throw new \Exception('حافظة الأوراق المالية غير موجودة');
            }

            // Create operation head
            $oper = $this->accountingService->createOperHead(
                $data,
                $proType,
                $portfolioAccount->id
            );

            // Create journal entry
            $this->accountingService->createJournalEntry(
                $oper,
                $data,
                $portfolioAccount->id,
                $proType
            );

            // Create check - extract only valid check fields
            $checkData = [
                'check_number' => $data['check_number'],
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'] ?? '',
                'account_holder_name' => $data['account_holder_name'],
                'amount' => $data['amount'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? 'pending',
                'type' => $data['type'],
                'payee_name' => $data['payee_name'] ?? null,
                'payer_name' => $data['payer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'created_by' => Auth::id(),
                'oper_id' => $oper->id,
            ];

            $check = Check::create($checkData);

            // Dispatch event
            event(new \Modules\Checks\Events\CheckCreated($check));

            // Clear cache
            $this->clearStatisticsCache();

            DB::commit();

            return $check;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing check
     */
    public function updateCheck(Check $check, array $data): Check
    {
        $check->update($data);

        return $check->fresh();
    }

    /**
     * Delete a check
     */
    public function deleteCheck(Check $check): bool
    {
        // Delete attached files
        if (! empty($check->attachments)) {
            foreach ($check->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        return $check->delete();
    }

    /**
     * Get checks with filters
     */
    public function getChecks(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Check::query()->with(['creator', 'approver']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('check_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('bank_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('account_holder_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('payee_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('payer_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('account_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('reference_number', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('due_date', [$filters['start_date'], $filters['end_date']]);
        } elseif (isset($filters['start_date'])) {
            $query->where('due_date', '>=', $filters['start_date']);
        } elseif (isset($filters['end_date'])) {
            $query->where('due_date', '<=', $filters['end_date']);
        }

        // فلتر "حتى 30 يوم" - الشيكات المستحقة خلال الأيام القادمة
        if (isset($filters['days_ahead']) && is_numeric($filters['days_ahead'])) {
            $startDate = now()->toDateString();
            $endDate = now()->addDays((int) $filters['days_ahead'])->toDateString();
            $query->whereBetween('due_date', [$startDate, $endDate]);
        }

        if (isset($filters['bank_name']) && $filters['bank_name'] !== '') {
            $query->where('bank_name', 'like', '%'.$filters['bank_name'].'%');
        }

        if (isset($filters['account_number']) && $filters['account_number'] !== '') {
            $query->where('account_number', 'like', '%'.$filters['account_number'].'%');
        }

        if (isset($filters['payee_name']) && $filters['payee_name'] !== '') {
            $query->where('payee_name', 'like', '%'.$filters['payee_name'].'%');
        }

        if (isset($filters['payer_name']) && $filters['payer_name'] !== '') {
            $query->where('payer_name', 'like', '%'.$filters['payer_name'].'%');
        }

        if (isset($filters['amount_min']) && is_numeric($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max']) && is_numeric($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        if (isset($filters['issue_date_from'])) {
            $query->where('issue_date', '>=', $filters['issue_date_from']);
        }

        if (isset($filters['issue_date_to'])) {
            $query->where('issue_date', '<=', $filters['issue_date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get check statistics
     */
    public function getStatistics(array $dateRange): array
    {
        $cacheKey = 'checks_statistics_'.md5(json_encode($dateRange));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($dateRange) {
            return [
                'total' => Check::whereBetween('created_at', $dateRange)->count(),
                'pending' => Check::whereBetween('created_at', $dateRange)
                    ->where('status', Check::STATUS_PENDING)->count(),
                'cleared' => Check::whereBetween('created_at', $dateRange)
                    ->where('status', Check::STATUS_CLEARED)->count(),
                'bounced' => Check::whereBetween('created_at', $dateRange)
                    ->where('status', Check::STATUS_BOUNCED)->count(),
                'total_amount' => Check::whereBetween('created_at', $dateRange)->sum('amount'),
                'pending_amount' => Check::whereBetween('created_at', $dateRange)
                    ->where('status', Check::STATUS_PENDING)->sum('amount'),
                'cleared_amount' => Check::whereBetween('created_at', $dateRange)
                    ->where('status', Check::STATUS_CLEARED)->sum('amount'),
            ];
        });
    }

    /**
     * Clear statistics cache
     */
    public function clearStatisticsCache(): void
    {
        // Clear only checks-related cache keys instead of flushing all cache
        \Illuminate\Support\Facades\Cache::forget('check_portfolio_incoming');
        \Illuminate\Support\Facades\Cache::forget('check_portfolio_outgoing');

        // Clear statistics cache by pattern (if using Redis or similar)
        // Note: This is a safer approach than Cache::flush() which clears ALL cache
        $cachePrefix = 'checks_statistics_';

        // If using Redis, we could use tags, but for compatibility, we'll just let TTL expire
        // The cache will expire automatically after 300 seconds (5 minutes)
    }
}
