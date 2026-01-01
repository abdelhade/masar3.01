<?php

declare(strict_types=1);

namespace Modules\Invoices\Services\Invoice;

/**
 * FeatureModeManager Service
 *
 * Manages feature mode settings for invoice features (discount, additional, vat, withholding_tax).
 * Determines where each feature can be applied: invoice level, item level, both, or disabled.
 *
 * @package App\Services\Invoice
 */
class FeatureModeManager
{
    /**
     * Valid mode values
     */
    private const VALID_MODES = [
        'invoice_level',
        'item_level',
        'both',
        'disabled',
    ];

    /**
     * Valid feature names
     */
    private const VALID_FEATURES = [
        'discount',
        'additional',
        'vat',
        'withholding_tax',
    ];

    /**
     * Default mode values for each feature
     */
    private const DEFAULT_MODES = [
        'discount' => 'invoice_level',
        'additional' => 'invoice_level',
        'vat' => 'disabled',
        'withholding_tax' => 'disabled',
    ];

    /**
     * Get the current mode for a feature.
     *
     * @param string $feature Feature name (discount, additional, vat, withholding_tax)
     * @return string Mode value (invoice_level, item_level, both, disabled)
     * @throws \InvalidArgumentException if feature name is invalid
     */
    public function getMode(string $feature): string
    {
        if (!in_array($feature, self::VALID_FEATURES, true)) {
            throw new \InvalidArgumentException("Invalid feature name: {$feature}");
        }

        // Use new naming convention: {feature}_level
        $settingKey = "{$feature}_level";
        $mode = setting($settingKey, self::DEFAULT_MODES[$feature]);

        // Validate the mode value
        if (!$this->isValidMode($mode)) {
            // If invalid mode in database, return default
            return self::DEFAULT_MODES[$feature];
        }

        return $mode;
    }

    /**
     * Check if invoice-level field should be enabled for a feature.
     *
     * @param string $feature Feature name (discount, additional, vat, withholding_tax)
     * @return bool True if invoice field should be enabled
     * @throws \InvalidArgumentException if feature name is invalid
     */
    public function isInvoiceLevelEnabled(string $feature): bool
    {
        $mode = $this->getMode($feature);

        return in_array($mode, ['invoice_level', 'both'], true);
    }

    /**
     * Check if item-level field should be enabled for a feature.
     *
     * @param string $feature Feature name (discount, additional, vat, withholding_tax)
     * @return bool True if item field should be enabled
     * @throws \InvalidArgumentException if feature name is invalid
     */
    public function isItemLevelEnabled(string $feature): bool
    {
        $mode = $this->getMode($feature);

        return in_array($mode, ['item_level', 'both'], true);
    }

    /**
     * Check if aggregated values should be displayed for a feature.
     *
     * Aggregated values are shown when item-level is enabled (item_level or both modes).
     *
     * @param string $feature Feature name (discount, additional, vat, withholding_tax)
     * @return bool True if aggregated values should be shown
     * @throws \InvalidArgumentException if feature name is invalid
     */
    public function shouldShowAggregatedValues(string $feature): bool
    {
        $mode = $this->getMode($feature);

        return in_array($mode, ['item_level', 'both'], true);
    }

    /**
     * Get all feature modes as array.
     *
     * Returns an associative array with feature names as keys and their modes as values.
     *
     * @return array<string, string> Feature modes keyed by feature name
     */
    public function getAllModes(): array
    {
        $modes = [];

        foreach (self::VALID_FEATURES as $feature) {
            $modes[$feature] = $this->getMode($feature);
        }

        return $modes;
    }

    /**
     * Validate mode value.
     *
     * @param string $mode Mode to validate
     * @return bool True if valid
     */
    private function isValidMode(string $mode): bool
    {
        return in_array($mode, self::VALID_MODES, true);
    }
}
