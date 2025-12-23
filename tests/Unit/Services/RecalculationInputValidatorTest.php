<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Services\Validation\RecalculationInputValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RecalculationInputValidatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validateItemIds with valid inputs
     */
    public function test_validate_item_ids_with_valid_inputs(): void
    {
        // Should not throw exception
        RecalculationInputValidator::validateItemIds([1, 2, 3, 100]);
        RecalculationInputValidator::validateItemIds([]);

        $this->assertTrue(true); // If we reach here, validation passed
    }

    /**
     * Test validateItemIds with negative numbers
     */
    public function test_validate_item_ids_rejects_negative_numbers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a positive integer');

        RecalculationInputValidator::validateItemIds([1, -5, 3]);
    }

    /**
     * Test validateItemIds with zero
     */
    public function test_validate_item_ids_rejects_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a positive integer');

        RecalculationInputValidator::validateItemIds([0]);
    }

    /**
     * Test validateItemIds with non-integer values
     */
    public function test_validate_item_ids_rejects_non_integers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an integer');

        RecalculationInputValidator::validateItemIds([1, 3.5, 2]);
    }

    /**
     * Test validateItemIds with non-numeric values
     */
    public function test_validate_item_ids_rejects_non_numeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a numeric value');

        RecalculationInputValidator::validateItemIds([1, 'abc', 3]);
    }

    /**
     * Test validateItemIds with string numbers
     */
    public function test_validate_item_ids_accepts_string_numbers(): void
    {
        // String numbers should be accepted
        RecalculationInputValidator::validateItemIds(['1', '2', '100']);

        $this->assertTrue(true);
    }

    /**
     * Test validateDate with valid dates
     */
    public function test_validate_date_with_valid_dates(): void
    {
        RecalculationInputValidator::validateDate('2025-12-22');
        RecalculationInputValidator::validateDate('2024-01-01');
        RecalculationInputValidator::validateDate('2023-06-15');
        RecalculationInputValidator::validateDate(null);

        $this->assertTrue(true);
    }

    /**
     * Test validateDate with invalid format
     */
    public function test_validate_date_rejects_invalid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected Y-m-d');

        RecalculationInputValidator::validateDate('22-12-2025');
    }

    /**
     * Test validateDate with invalid format (US style)
     */
    public function test_validate_date_rejects_us_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected Y-m-d');

        RecalculationInputValidator::validateDate('12/22/2025');
    }

    /**
     * Test validateDate with invalid calendar date
     */
    public function test_validate_date_rejects_invalid_calendar_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not a valid calendar date');

        RecalculationInputValidator::validateDate('2025-02-30');
    }

    /**
     * Test validateDate with non-string value
     */
    public function test_validate_date_rejects_non_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a string');

        RecalculationInputValidator::validateDate(20251222);
    }

    /**
     * Test validateItemsExist with existing items
     */
    public function test_validate_items_exist_returns_existing_ids(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $existingIds = RecalculationInputValidator::validateItemsExist([
            $item1->id,
            $item2->id,
            99999, // Non-existing ID
        ]);

        $this->assertCount(2, $existingIds);
        $this->assertContains($item1->id, $existingIds);
        $this->assertContains($item2->id, $existingIds);
        $this->assertNotContains(99999, $existingIds);
    }

    /**
     * Test validateItemsExist with no existing items
     */
    public function test_validate_items_exist_returns_empty_for_non_existing(): void
    {
        $existingIds = RecalculationInputValidator::validateItemsExist([99998, 99999]);

        $this->assertEmpty($existingIds);
    }

    /**
     * Test validateItemsExist with empty array
     */
    public function test_validate_items_exist_handles_empty_array(): void
    {
        $existingIds = RecalculationInputValidator::validateItemsExist([]);

        $this->assertEmpty($existingIds);
    }

    /**
     * Test validateBoolean with boolean values
     */
    public function test_validate_boolean_with_boolean_values(): void
    {
        $this->assertTrue(RecalculationInputValidator::validateBoolean(true));
        $this->assertFalse(RecalculationInputValidator::validateBoolean(false));
    }

    /**
     * Test validateBoolean with integer values
     */
    public function test_validate_boolean_with_integer_values(): void
    {
        $this->assertTrue(RecalculationInputValidator::validateBoolean(1));
        $this->assertFalse(RecalculationInputValidator::validateBoolean(0));
        $this->assertTrue(RecalculationInputValidator::validateBoolean(5));
        $this->assertTrue(RecalculationInputValidator::validateBoolean(-1));
    }

    /**
     * Test validateBoolean with string values
     */
    public function test_validate_boolean_with_string_values(): void
    {
        $this->assertTrue(RecalculationInputValidator::validateBoolean('true'));
        $this->assertTrue(RecalculationInputValidator::validateBoolean('TRUE'));
        $this->assertTrue(RecalculationInputValidator::validateBoolean('1'));
        $this->assertTrue(RecalculationInputValidator::validateBoolean('yes'));
        $this->assertTrue(RecalculationInputValidator::validateBoolean('on'));

        $this->assertFalse(RecalculationInputValidator::validateBoolean('false'));
        $this->assertFalse(RecalculationInputValidator::validateBoolean('FALSE'));
        $this->assertFalse(RecalculationInputValidator::validateBoolean('0'));
        $this->assertFalse(RecalculationInputValidator::validateBoolean('no'));
        $this->assertFalse(RecalculationInputValidator::validateBoolean('off'));
        $this->assertFalse(RecalculationInputValidator::validateBoolean(''));
    }

    /**
     * Test validateBoolean with null
     */
    public function test_validate_boolean_with_null(): void
    {
        $this->assertFalse(RecalculationInputValidator::validateBoolean(null));
    }

    /**
     * Test validateBoolean with other types
     */
    public function test_validate_boolean_with_other_types(): void
    {
        $this->assertTrue(RecalculationInputValidator::validateBoolean([1, 2])); // Non-empty array
        $this->assertFalse(RecalculationInputValidator::validateBoolean([])); // Empty array
        $this->assertTrue(RecalculationInputValidator::validateBoolean((object) ['key' => 'value']));
    }
}
