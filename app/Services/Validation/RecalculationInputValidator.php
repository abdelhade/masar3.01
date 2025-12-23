<?php

declare(strict_types=1);

namespace App\Services\Validation;

use App\Models\Item;
use InvalidArgumentException;

/**
 * Input validator for recalculation services
 *
 * Validates all inputs before processing to prevent errors and data corruption.
 * Provides comprehensive validation for item IDs, dates, and boolean flags.
 */
class RecalculationInputValidator
{
    /**
     * Validate item IDs array
     *
     * Ensures all item IDs are positive integers. Throws exception if any ID is invalid.
     *
     * @param  array  $itemIds  Array of item IDs to validate
     *
     * @throws InvalidArgumentException if validation fails
     */
    public static function validateItemIds(array $itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }

        foreach ($itemIds as $index => $itemId) {
            if (! is_int($itemId) && ! is_numeric($itemId)) {
                throw new InvalidArgumentException(
                    "Invalid item ID at index {$index}: must be a numeric value, got ".gettype($itemId)
                );
            }

            $numericId = is_int($itemId) ? $itemId : (int) $itemId;

            if ($numericId <= 0) {
                throw new InvalidArgumentException(
                    "Invalid item ID at index {$index}: must be a positive integer, got {$numericId}"
                );
            }

            // Check if the value changed after casting (e.g., 3.5 becomes 3)
            if (is_float($itemId) && $itemId != $numericId) {
                throw new InvalidArgumentException(
                    "Invalid item ID at index {$index}: must be an integer, got float {$itemId}"
                );
            }
        }
    }

    /**
     * Validate date format
     *
     * Ensures date is in Y-m-d format (e.g., 2025-12-22).
     * Null dates are considered valid.
     *
     * @param  string|null  $date  Date string in Y-m-d format
     *
     * @throws InvalidArgumentException if date format is invalid
     */
    public static function validateDate(?string $date): void
    {
        if ($date === null) {
            return;
        }

        if (! is_string($date)) {
            throw new InvalidArgumentException(
                'Invalid date: must be a string in Y-m-d format, got '.gettype($date)
            );
        }

        // Check format using regex
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException(
                "Invalid date format: expected Y-m-d (e.g., 2025-12-22), got '{$date}'"
            );
        }

        // Validate that it's a real date
        $parts = explode('-', $date);
        $year = (int) $parts[0];
        $month = (int) $parts[1];
        $day = (int) $parts[2];

        if (! checkdate($month, $day, $year)) {
            throw new InvalidArgumentException(
                "Invalid date: '{$date}' is not a valid calendar date"
            );
        }
    }

    /**
     * Validate items exist in database
     *
     * Checks if items exist in the database and returns only existing IDs.
     * Non-existing IDs are filtered out.
     *
     * @param  array  $itemIds  Array of item IDs to check
     * @return array Array of existing item IDs
     */
    public static function validateItemsExist(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        // First validate that all IDs are valid integers
        self::validateItemIds($itemIds);

        // Query database for existing items
        $existingIds = Item::whereIn('id', $itemIds)
            ->pluck('id')
            ->toArray();

        return $existingIds;
    }

    /**
     * Validate boolean flag
     *
     * Converts various input types to boolean.
     * Accepts: true/false, 1/0, '1'/'0', 'true'/'false', 'yes'/'no'
     *
     * @param  mixed  $value  Value to validate as boolean
     * @return bool Validated boolean value
     */
    public static function validateBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));

            if (in_array($lower, ['true', '1', 'yes', 'on'])) {
                return true;
            }

            if (in_array($lower, ['false', '0', 'no', 'off', ''])) {
                return false;
            }
        }

        // For null, return false
        if ($value === null) {
            return false;
        }

        // For any other type, cast to bool
        return (bool) $value;
    }
}
