// Function to register the component
function registerCurrencyConverter() {
    if (typeof Alpine === "undefined" || typeof Alpine.data !== "function") {
        return false;
    }

    Alpine.data("currencyConverter", (config = {}) => ({
        // Configuration
        fromCurrency: config.fromCurrency || "",
        toCurrency: config.toCurrency || "",
        amount: config.amount || 0,
        sourceField: config.sourceField || null,
        targetField: config.targetField || null,
        editableRate: config.editableRate !== false, // Default true

        // Data
        currencies: [],
        conversionRate: 1,
        customRate: null, // User-entered custom rate
        convertedAmount: 0,
        loading: false,
        error: null,
        lastRateUpdate: null,

        // Initialize
        async init() {
            await this.fetchCurrencies();

            // Watch source field if provided
            if (this.sourceField) {
                this.watchSourceField();
            }

            // Auto convert if both currencies set
            if (this.fromCurrency && this.toCurrency) {
                await this.convert();
            }
        },

        // Fetch active currencies
        async fetchCurrencies() {
            this.loading = true;

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                const response = await fetch("/currencies/active", {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        ...(csrfToken && { "X-CSRF-TOKEN": csrfToken }),
                    },
                    credentials: "same-origin",
                });

                if (!response.ok) {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("text/html")) {
                        throw new Error("يرجى تسجيل الدخول وتحديث الصفحة");
                    }
                    throw new Error(
                        `HTTP ${response.status}: ${response.statusText}`
                    );
                }

                const data = await response.json();

                if (
                    data.success &&
                    data.currencies &&
                    Array.isArray(data.currencies)
                ) {
                    this.currencies = data.currencies;

                    // Set default currency if not set
                    if (!this.fromCurrency && this.currencies.length > 0) {
                        const defaultCurrency = this.currencies.find(
                            (c) => c.is_default
                        );
                        this.fromCurrency = defaultCurrency
                            ? defaultCurrency.code
                            : this.currencies[0].code;
                    }
                } else {
                    throw new Error("Invalid response format");
                }
            } catch (err) {
                this.error = "فشل تحميل العملات: " + err.message;
            } finally {
                this.loading = false;
            }
        },

        // Watch source field for changes
        watchSourceField() {
            const sourceElement = document.querySelector(this.sourceField);
            if (sourceElement) {
                this.amount = parseFloat(sourceElement.value) || 0;

                sourceElement.addEventListener("input", (e) => {
                    this.amount = parseFloat(e.target.value) || 0;
                    if (this.fromCurrency && this.toCurrency) {
                        this.convertWithCustomRate();
                    }
                });

                sourceElement.addEventListener("change", (e) => {
                    this.amount = parseFloat(e.target.value) || 0;
                    if (this.fromCurrency && this.toCurrency) {
                        this.convertWithCustomRate();
                    }
                });
            }
        },

        // Fetch rate from API
        async fetchRate() {
            if (!this.fromCurrency || !this.toCurrency) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams({
                    from: this.fromCurrency,
                    to: this.toCurrency,
                    amount: 1,
                });

                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                const response = await fetch(`/currencies/convert?${params}`, {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        ...(csrfToken && { "X-CSRF-TOKEN": csrfToken }),
                    },
                    credentials: "same-origin",
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.conversionRate = data.rate;
                    this.customRate = data.rate; // Set custom rate to API rate
                    this.lastRateUpdate = new Date().toLocaleString("ar-EG");

                    // Auto-convert with new rate
                    this.convertWithCustomRate();
                } else {
                    throw new Error(data.message || "فشل جلب السعر");
                }
            } catch (err) {
                this.error = "فشل جلب سعر الصرف: " + err.message;
            } finally {
                this.loading = false;
            }
        },

        // Convert using API rate
        async convert() {
            if (!this.fromCurrency || !this.toCurrency) {
                return;
            }

            // If same currency, rate is 1
            if (this.fromCurrency === this.toCurrency) {
                this.conversionRate = 1;
                this.customRate = 1;
                this.convertedAmount = this.amount;
                this.updateTargetField();
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams({
                    from: this.fromCurrency,
                    to: this.toCurrency,
                    amount: this.amount || 0,
                });

                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                const response = await fetch(`/currencies/convert?${params}`, {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        ...(csrfToken && { "X-CSRF-TOKEN": csrfToken }),
                    },
                    credentials: "same-origin",
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.conversionRate = data.rate;
                    // Only set customRate if not already set by user
                    if (
                        this.customRate === null ||
                        this.customRate === undefined
                    ) {
                        this.customRate = data.rate;
                    }
                    this.convertedAmount = data.converted;
                    this.lastRateUpdate = new Date().toLocaleString("ar-EG");

                    this.updateTargetField();
                    this.dispatchConversionEvent(data);
                } else {
                    throw new Error(data.message || "فشل التحويل");
                }
            } catch (err) {
                this.error = "حدث خطأ أثناء التحويل: " + err.message;
            } finally {
                this.loading = false;
            }
        },

        // Convert using custom (user-entered) rate
        convertWithCustomRate() {
            const rate = this.customRate || this.conversionRate;
            if (!rate || rate <= 0) {
                this.convertedAmount = 0;
                return;
            }

            this.convertedAmount = this.amount * rate;

            this.updateTargetField();

            // Dispatch event
            this.$dispatch("currency-converted", {
                from: this.fromCurrency,
                to: this.toCurrency,
                rate: rate,
                amount: this.amount,
                converted: this.convertedAmount,
                isCustomRate: this.customRate !== this.conversionRate,
            });
        },

        // Update target field
        updateTargetField() {
            if (this.targetField) {
                const targetElement = document.querySelector(this.targetField);
                if (targetElement) {
                    targetElement.value = this.convertedAmount;
                    targetElement.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }
            }
        },

        // Dispatch conversion event
        dispatchConversionEvent(data) {
            this.$dispatch("currency-converted", {
                from: data.from,
                to: data.to,
                rate: data.rate,
                amount: data.amount,
                converted: data.converted,
            });
        },

        // Get currency symbol
        getCurrencySymbol(code) {
            const currency = this.currencies.find((c) => c.code === code);
            return currency?.symbol || code;
        },

        // Format amount
        formatAmount(amount, currencyCode) {
            const currency = this.currencies.find(
                (c) => c.code === currencyCode
            );
            const decimals = currency?.decimal_places || 2;
            return parseFloat(amount || 0).toFixed(decimals);
        },

        // Swap currencies
        swapCurrencies() {
            const temp = this.fromCurrency;
            this.fromCurrency = this.toCurrency;
            this.toCurrency = temp;

            // Invert the rate if set
            if (this.customRate && this.customRate > 0) {
                this.customRate = 1 / this.customRate;
            }

            this.convert();
        },

        // Reset custom rate to API rate
        resetRate() {
            this.customRate = this.conversionRate;
            this.convertWithCustomRate();
        },
    }));

    return true;
}

// Strategy 1: livewire:init
document.addEventListener("livewire:init", () => {
    registerCurrencyConverter();
});

// Strategy 2: alpine:init
document.addEventListener("alpine:init", () => {
    registerCurrencyConverter();
});

// Strategy 3: DOMContentLoaded with retry
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            if (!registerCurrencyConverter()) {
                setTimeout(registerCurrencyConverter, 500);
            }
        }, 100);
    });
} else {
    setTimeout(() => {
        if (!registerCurrencyConverter()) {
            setTimeout(registerCurrencyConverter, 500);
        }
    }, 100);
}
