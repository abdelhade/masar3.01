<?php

declare(strict_types=1);

namespace Modules\Invoices\Services\Invoice;

/**
 * InvoiceFormStateManager Service
 *
 * Manages dynamic state of invoice form fields based on feature modes.
 * Determines which fields should be enabled/disabled in the invoice form UI.
 *
 * @package App\Services\Invoice
 */
class InvoiceFormStateManager
{
    /**
     * Valid field types
     */
    private const VALID_FIELD_TYPES = ['invoice', 'item'];

    /**
     * Create a new InvoiceFormStateManager instance.
     *
     * @param FeatureModeManager $featureModeManager Feature mode manager instance
     */
    public function __construct(
        private readonly FeatureModeManager $featureModeManager
    ) {
    }

    /**
     * Get field states for invoice form.
     *
     * Returns an array structure indicating which fields should be enabled/disabled
     * and whether aggregated values should be shown for each feature.
     *
     * @return array<string, array<string, bool>> Field states with enabled/disabled status
     *
     * Example return structure:
     * [
     *     'discount' => [
     *         'invoice' => true,
     *         'item' => false,
     *         'showAggregated' => false,
     *     ],
     *     'additional' => [...],
     *     'vat' => [...],
     *     'withholding_tax' => [...],
     * ]
     */
    public function getFieldStates(): array
    {
        $features = ['discount', 'additional', 'vat', 'withholding_tax'];
        $states = [];

        foreach ($features as $feature) {
            $states[$feature] = [
                'invoice' => $this->featureModeManager->isInvoiceLevelEnabled($feature),
                'item' => $this->featureModeManager->isItemLevelEnabled($feature),
                'showAggregated' => $this->featureModeManager->shouldShowAggregatedValues($feature),
            ];
        }

        return $states;
    }

    /**
     * Get JavaScript configuration for dynamic form control.
     *
     * Returns a configuration array suitable for passing to frontend JavaScript
     * (Alpine.js or similar) to control field states dynamically.
     *
     * @return array<string, mixed> JavaScript config with field states and modes
     *
     * Example return structure:
     * [
     *     'fieldStates' => [...],
     *     'modes' => [
     *         'discount' => 'invoice_level',
     *         'additional' => 'invoice_level',
     *         'vat' => 'disabled',
     *         'withholding_tax' => 'disabled',
     *     ],
     * ]
     */
    public function getJavaScriptConfig(): array
    {
        return [
            'fieldStates' => $this->getFieldStates(),
            'modes' => $this->featureModeManager->getAllModes(),
        ];
    }

    /**
     * Check if a specific field should be enabled.
     *
     * @param string $fieldType Type of field ('invoice' or 'item')
     * @param string $feature Feature name (discount, additional, vat, withholding_tax)
     * @return bool True if field should be enabled
     * @throws \InvalidArgumentException if field type is invalid
     */
    public function isFieldEnabled(string $fieldType, string $feature): bool
    {
        if (!in_array($fieldType, self::VALID_FIELD_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid field type: {$fieldType}. Must be 'invoice' or 'item'.");
        }

        if ($fieldType === 'invoice') {
            return $this->featureModeManager->isInvoiceLevelEnabled($feature);
        }

        return $this->featureModeManager->isItemLevelEnabled($feature);
    }
}

