<?php

namespace Modules\Checks\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Accounts\Models\AccHead;

class CheckPortfolioService
{
    /**
     * Get incoming checks portfolio account
     */
    public function getIncomingPortfolio(): ?AccHead
    {
        return Cache::remember('check_portfolio_incoming', 3600, function () {
            return AccHead::where('code', '110501')
                ->where('isdeleted', 0)
                ->first();
        });
    }

    /**
     * Get outgoing checks portfolio account
     */
    public function getOutgoingPortfolio(): ?AccHead
    {
        return Cache::remember('check_portfolio_outgoing', 3600, function () {
            return AccHead::where('code', '210301')
                ->where('isdeleted', 0)
                ->first();
        });
    }

    /**
     * Get portfolio account by check type
     */
    public function getPortfolioAccount(string $type): ?AccHead
    {
        return match ($type) {
            'incoming' => $this->getIncomingPortfolio(),
            'outgoing' => $this->getOutgoingPortfolio(),
            default => null,
        };
    }

    /**
     * Clear portfolio cache
     */
    public function clearCache(): void
    {
        Cache::forget('check_portfolio_incoming');
        Cache::forget('check_portfolio_outgoing');
    }
}
