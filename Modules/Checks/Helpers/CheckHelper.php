<?php

namespace Modules\Checks\Helpers;

use Modules\Checks\Models\Check;

class CheckHelper
{
    /**
     * Format check number with leading zeros
     */
    public static function formatCheckNumber(string $number, int $length = 9): string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total amount for checks collection
     */
    public static function calculateTotal($checks): float
    {
        return $checks->sum('amount');
    }

    /**
     * Get status badge HTML
     */
    public static function getStatusBadge(Check $check): string
    {
        $colors = [
            Check::STATUS_PENDING => 'warning',
            Check::STATUS_CLEARED => 'success',
            Check::STATUS_BOUNCED => 'danger',
            Check::STATUS_CANCELLED => 'secondary',
        ];

        $statuses = Check::getStatuses();

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $colors[$check->status] ?? 'primary',
            $statuses[$check->status] ?? $check->status
        );
    }

    /**
     * Get type badge HTML
     */
    public static function getTypeBadge(Check $check): string
    {
        $types = Check::getTypes();
        $color = $check->type === Check::TYPE_INCOMING ? 'success' : 'info';

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            $types[$check->type] ?? $check->type
        );
    }

    /**
     * Validate check number format
     */
    public static function validateCheckNumber(string $number): bool
    {
        // Check number should be alphanumeric and between 1-50 characters
        return preg_match('/^[a-zA-Z0-9\-]{1,50}$/', $number) === 1;
    }

    /**
     * Get overdue checks count
     */
    public static function getOverdueCount(): int
    {
        return Check::overdue()->count();
    }

    /**
     * Get pending checks count
     */
    public static function getPendingCount(): int
    {
        return Check::where('status', Check::STATUS_PENDING)->count();
    }
}
