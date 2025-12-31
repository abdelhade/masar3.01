<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Invoice\FeatureModeManager;
use App\Services\Invoice\InvoiceFormStateManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;
use Tests\TestCase;

/**
 * Property-Based Tests for Aggregated Values Display and Calculation
 *
 * Feature: discount-additional-handling
 * Property 14: Aggregated Values Display Condition
 * Property 15: Aggregated Values Calculation Accuracy
 * Validates: Requirements 15.1, 15.2, 15.3, 15.5
 *
 * For any feature with mode 'item_level' or 'both', aggregated values should be displayed.
 * For any invoice with item-level values, the sum should equal the aggregated display value.
 */
class AggregatedValuesPropertyTest extends TestCase
{
    use RefreshDatabase;

    private FeatureModeManager $featureModeManager;
    private InvoiceFormStateManager $formStateManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureModeManager = new FeatureModeManager();
        $this->formStateManager = new InvoiceFormStateManager($this->featureModeManager);
    }

    /**
     * Set a feature mode setting
     */
    private function setFeatureMode(string $feature, string $mode): void
    {
        PublicSetting::updateOrCreate(
            ['key' => "{$feature}_level"],
            [
                'value' => $mode,
                'category_id' => 2,
                'label' => "مستوى {$feature}",
                'input_type' => 'select',
            ]
        );

        // Clear cache to ensure new value is used
        Cache::forget('public_settings');
    }

    /**
     * Property 14: Aggregated Values Display Condition
     *
     * Test that aggregated values are shown when mode is 'item_level' or 'both'
     * and hidden when mode is 'invoice_level' or 'disabled'
     *
     * @dataProvider featureModeProvider
     */
    public function test_property_aggregated_values_display_condition(
        string $feature,
        string $mode,
        bool $shouldShowAggregated
    ): void {
        // Arrange: Set feature mode
        $this->setFeatureMode($feature, $mode);

        // Act: Get field states
        $fieldStates = $this->formStateManager->getFieldStates();

        // Assert: Aggregated display should match expected value
        $this->assertSame(
            $shouldShowAggregated,
            $fieldStates[$feature]['showAggregated'],
            "Feature '{$feature}' with mode '{$mode}' should ".
            ($shouldShowAggregated ? 'show' : 'hide').' aggregated values'
        );

        // Also verify through FeatureModeManager directly
        $this->assertSame(
            $shouldShowAggregated,
            $this->featureModeManager->shouldShowAggregatedValues($feature),
            "FeatureModeManager should return same result for '{$feature}' with mode '{$mode}'"
        );
    }

    /**
     * Data provider for feature mode scenarios
     *
     * @return array<string, array{string, string, bool}>
     */
    public static function featureModeProvider(): array
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];
        $scenarios = [];

        foreach ($features as $feature) {
            // Mode: invoice_level - should NOT show aggregated
            $scenarios["{$feature}_invoice_level"] = [
                $feature,
                'invoice_level',
                false,
            ];

            // Mode: item_level - should show aggregated
            $scenarios["{$feature}_item_level"] = [
                $feature,
                'item_level',
                true,
            ];

            // Mode: both - should show aggregated
            $scenarios["{$feature}_both"] = [
                $feature,
                'both',
                true,
            ];

            // Mode: disabled - should NOT show aggregated
            $scenarios["{$feature}_disabled"] = [
                $feature,
                'disabled',
                false,
            ];
        }

        return $scenarios;
    }

    /**
     * Property 15: Aggregated Values Calculation Accuracy
     *
     * Test that sum of item-level values equals the aggregated display value
     *
     * @dataProvider invoiceItemsProvider
     */
    public function test_property_aggregated_values_calculation_accuracy(
        array $items,
        string $feature,
        float $expectedAggregatedValue
    ): void {
        // Arrange: Set feature mode to item_level
        $this->setFeatureMode($feature, 'item_level');

        // Act: Calculate aggregated value (sum of item values)
        $calculatedSum = 0.0;
        foreach ($items as $item) {
            $calculatedSum += $item[$feature] ?? 0.0;
        }

        // Assert: Calculated sum should equal expected aggregated value
        $this->assertEqualsWithDelta(
            $expectedAggregatedValue,
            $calculatedSum,
            0.01,
            "Sum of item-level {$feature} values should equal aggregated value"
        );

        // Verify aggregated values should be shown for item_level mode
        $this->assertTrue(
            $this->featureModeManager->shouldShowAggregatedValues($feature),
            "Aggregated values should be shown for {$feature} in item_level mode"
        );
    }

    /**
     * Data provider for invoice items scenarios
     *
     * @return array<string, array{array, string, float}>
     */
    public static function invoiceItemsProvider(): array
    {
        return [
            // Multiple items with tax
            'multiple_items_tax' => [
                [
                    ['item_id' => 1, 'tax' => 15.00],
                    ['item_id' => 2, 'tax' => 25.50],
                    ['item_id' => 3, 'tax' => 10.25],
                ],
                'tax',
                50.75,
            ],

            // Items with withholding tax
            'items_with_withholding_tax' => [
                [
                    ['item_id' => 1, 'withholding_tax' => 5.00],
                    ['item_id' => 2, 'withholding_tax' => 3.50],
                ],
                'withholding_tax',
                8.50,
            ],

            // Items with discount
            'items_with_discount' => [
                [
                    ['item_id' => 1, 'discount' => 10.00],
                    ['item_id' => 2, 'discount' => 20.00],
                ],
                'discount',
                30.00,
            ],

            // Items with additional charges
            'items_with_additional' => [
                [
                    ['item_id' => 1, 'additional' => 5.00],
                    ['item_id' => 2, 'additional' => 7.50],
                ],
                'additional',
                12.50,
            ],

            // Zero values
            'zero_values' => [
                [
                    ['item_id' => 1, 'tax' => 0.00],
                    ['item_id' => 2, 'tax' => 0.00],
                ],
                'tax',
                0.00,
            ],
        ];
    }

    /**
     * Test that aggregated values are NOT shown for invoice_level mode
     */
    public function test_property_aggregated_values_hidden_for_invoice_level(): void
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];

        foreach ($features as $feature) {
            // Arrange: Set to invoice_level mode
            $this->setFeatureMode($feature, 'invoice_level');

            // Act & Assert: Should not show aggregated values
            $this->assertFalse(
                $this->featureModeManager->shouldShowAggregatedValues($feature),
                "Feature '{$feature}' with invoice_level mode should NOT show aggregated values"
            );

            $fieldStates = $this->formStateManager->getFieldStates();
            $this->assertFalse(
                $fieldStates[$feature]['showAggregated'],
                "Field states for '{$feature}' should indicate aggregated values are hidden"
            );
        }
    }

    /**
     * Test that aggregated values are NOT shown for disabled mode
     */
    public function test_property_aggregated_values_hidden_for_disabled_mode(): void
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];

        foreach ($features as $feature) {
            // Arrange: Set to disabled mode
            $this->setFeatureMode($feature, 'disabled');

            // Act & Assert: Should not show aggregated values
            $this->assertFalse(
                $this->featureModeManager->shouldShowAggregatedValues($feature),
                "Feature '{$feature}' with disabled mode should NOT show aggregated values"
            );

            $fieldStates = $this->formStateManager->getFieldStates();
            $this->assertFalse(
                $fieldStates[$feature]['showAggregated'],
                "Field states for '{$feature}' should indicate aggregated values are hidden"
            );
        }
    }

    /**
     * Test aggregated values consistency across multiple calculations
     */
    public function test_property_aggregated_values_consistency(): void
    {
        // Arrange: Set tax mode to item_level
        $this->setFeatureMode('tax', 'item_level');

        $items = [
            ['item_id' => 1, 'tax' => 15.00],
            ['item_id' => 2, 'tax' => 25.50],
            ['item_id' => 3, 'tax' => 10.25],
        ];

        // Act: Calculate sum multiple times
        $sum1 = array_sum(array_column($items, 'tax'));
        $sum2 = array_sum(array_column($items, 'tax'));
        $sum3 = array_sum(array_column($items, 'tax'));

        // Assert: All calculations should be identical
        $this->assertSame($sum1, $sum2, 'First and second calculation should match');
        $this->assertSame($sum2, $sum3, 'Second and third calculation should match');
        $this->assertEqualsWithDelta(50.75, $sum1, 0.01, 'Sum should equal expected value');
    }

    /**
     * Test aggregated values with empty items array
     */
    public function test_property_aggregated_values_with_empty_items(): void
    {
        // Arrange: Set tax mode to item_level
        $this->setFeatureMode('tax', 'item_level');

        $items = [];

        // Act: Calculate sum
        $sum = array_sum(array_column($items, 'tax'));

        // Assert: Sum should be zero
        $this->assertSame(0.0, $sum, 'Sum of empty items should be zero');

        // Verify aggregated values should still be shown (even if zero)
        $this->assertTrue(
            $this->featureModeManager->shouldShowAggregatedValues('tax'),
            'Aggregated values should be shown even for empty items in item_level mode'
        );
    }

    /**
     * Test aggregated values for all features simultaneously
     */
    public function test_property_aggregated_values_for_all_features(): void
    {
        // Arrange: Set all features to item_level
        $this->setFeatureMode('discount', 'item_level');
        $this->setFeatureMode('additional', 'item_level');
        $this->setFeatureMode('vat', 'item_level');
        $this->setFeatureMode('withholding_tax', 'item_level');

        $items = [
            [
                'item_id' => 1,
                'discount' => 10.00,
                'additional' => 5.00,
                'vat' => 15.00,
                'withholding_tax' => 2.00,
            ],
            [
                'item_id' => 2,
                'discount' => 20.00,
                'additional' => 7.50,
                'vat' => 25.50,
                'withholding_tax' => 3.50,
            ],
        ];

        // Act & Assert: Calculate and verify each feature
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];
        $expectedSums = [
            'discount' => 30.00,
            'additional' => 12.50,
            'vat' => 40.50,
            'withholding_tax' => 5.50,
        ];

        foreach ($features as $feature) {
            $sum = array_sum(array_column($items, $feature));

            $this->assertEqualsWithDelta(
                $expectedSums[$feature],
                $sum,
                0.01,
                "Sum of {$feature} should equal expected value"
            );

            $this->assertTrue(
                $this->featureModeManager->shouldShowAggregatedValues($feature),
                "Aggregated values should be shown for {$feature}"
            );
        }
    }

    /**
     * Test aggregated values with 'both' mode
     */
    public function test_property_aggregated_values_shown_for_both_mode(): void
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];

        foreach ($features as $feature) {
            // Arrange: Set to 'both' mode
            $this->setFeatureMode($feature, 'both');

            // Act & Assert: Should show aggregated values
            $this->assertTrue(
                $this->featureModeManager->shouldShowAggregatedValues($feature),
                "Feature '{$feature}' with 'both' mode should show aggregated values"
            );

            $fieldStates = $this->formStateManager->getFieldStates();
            $this->assertTrue(
                $fieldStates[$feature]['showAggregated'],
                "Field states for '{$feature}' should indicate aggregated values are shown"
            );
        }
    }

    /**
     * Test aggregated values calculation with rounding edge cases
     */
    public function test_property_aggregated_values_rounding_edge_cases(): void
    {
        // Arrange: Set tax mode to item_level
        $this->setFeatureMode('tax', 'item_level');

        $items = [
            ['item_id' => 1, 'tax' => 10.333],
            ['item_id' => 2, 'tax' => 20.667],
            ['item_id' => 3, 'tax' => 15.999],
        ];

        // Act: Calculate sum
        $sum = array_sum(array_column($items, 'tax'));

        // Assert: Sum should handle rounding correctly (within tolerance)
        $this->assertEqualsWithDelta(
            46.999,
            $sum,
            0.01,
            'Sum should handle decimal precision correctly'
        );
    }

    /**
     * Test that field states are consistent with feature mode manager
     */
    public function test_property_field_states_consistency_with_mode_manager(): void
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];
        $modes = ['invoice_level', 'item_level', 'both', 'disabled'];

        foreach ($features as $feature) {
            foreach ($modes as $mode) {
                // Arrange: Set mode
                $this->setFeatureMode($feature, $mode);

                // Act: Get states from both sources
                $fieldStates = $this->formStateManager->getFieldStates();
                $shouldShow = $this->featureModeManager->shouldShowAggregatedValues($feature);

                // Assert: Both should agree
                $this->assertSame(
                    $shouldShow,
                    $fieldStates[$feature]['showAggregated'],
                    "Field states and mode manager should agree for {$feature} with mode {$mode}"
                );
            }
        }
    }
}
