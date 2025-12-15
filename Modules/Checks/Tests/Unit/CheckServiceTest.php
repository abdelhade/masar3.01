<?php

namespace Modules\Checks\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checks\Models\Check;
use Modules\Checks\Services\CheckAccountingService;
use Modules\Checks\Services\CheckPortfolioService;
use Modules\Checks\Services\CheckService;
use Tests\TestCase;

class CheckServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckService $checkService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkService = new CheckService(
            $this->createMock(CheckAccountingService::class),
            $this->createMock(CheckPortfolioService::class)
        );
    }

    public function test_can_get_checks_with_filters(): void
    {
        Check::factory()->count(5)->create(['type' => 'incoming']);
        Check::factory()->count(3)->create(['type' => 'outgoing']);

        $checks = $this->checkService->getChecks(['type' => 'incoming']);

        $this->assertCount(5, $checks->items());
    }

    public function test_can_get_checks_with_search_filter(): void
    {
        Check::factory()->create(['check_number' => '123456']);
        Check::factory()->create(['check_number' => '789012']);

        $checks = $this->checkService->getChecks(['search' => '123456']);

        $this->assertCount(1, $checks->items());
        $this->assertEquals('123456', $checks->items()[0]->check_number);
    }

    public function test_can_get_statistics(): void
    {
        Check::factory()->count(10)->create(['status' => Check::STATUS_PENDING]);
        Check::factory()->count(5)->create(['status' => Check::STATUS_CLEARED]);

        $stats = $this->checkService->getStatistics([
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);

        $this->assertEquals(15, $stats['total']);
        $this->assertEquals(10, $stats['pending']);
        $this->assertEquals(5, $stats['cleared']);
    }
}
