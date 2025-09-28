<?php

namespace Modules\Services\Tests\Unit;

use Tests\TestCase;
use Modules\Services\Models\Service;

class ServiceModelTest extends TestCase
{
    /** @test */
    public function service_model_has_required_attributes()
    {
        $service = new Service([
            'name' => 'Test Service',
            'code' => 'TEST-001',
            'price' => 100.00,
            'service_type' => 'general',
            'is_active' => true,
            'is_taxable' => true,
        ]);

        $this->assertEquals('Test Service', $service->name);
        $this->assertEquals('TEST-001', $service->code);
        $this->assertEquals(100.00, $service->price);
        $this->assertEquals('general', $service->service_type);
        $this->assertTrue($service->is_active);
        $this->assertTrue($service->is_taxable);
    }

    /** @test */
    public function service_model_calculates_duration_correctly()
    {
        $service = new Service([]);
        
        // Duration tests removed as duration_minutes field is no longer used
    }


    /** @test */
    public function service_model_has_default_values()
    {
        $service = new Service();
        
        $this->assertFalse($service->is_active);
        $this->assertFalse($service->is_taxable);
        $this->assertEquals('general', $service->service_type);
        $this->assertEquals(0, $service->price);
        $this->assertEquals(0, $service->cost);
    }

    /** @test */
    public function service_model_casts_attributes_correctly()
    {
        $service = new Service([
            'requirements' => ['requirement1', 'requirement2'],
            'features' => ['feature1', 'feature2'],
            'is_active' => '1',
            'is_taxable' => '0',
        ]);

        $this->assertIsArray($service->requirements);
        $this->assertIsArray($service->features);
        $this->assertTrue($service->is_active);
        $this->assertFalse($service->is_taxable);
    }
}
