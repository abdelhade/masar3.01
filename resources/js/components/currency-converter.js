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
        useInverseRate: (config.useInverseRate !== undefined) ? config.useInverseRate : true,
        initialRate: config.initialRate || null,

        // Data
        currencies: [],
        conversionRate: 1, // Store the raw rate (Base -> Target)
        customRate: null,  // This will store the Rate or Price depending on useInverseRate
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
                if (this.initialRate !== null) {
                    this.processIncomingRate(this.initialRate);
                    this.convertWithCustomRate();
                } else {
                    await this.convert();
                }
            }

            // UNIFIED PRECISION WATCHER
            this.$watch("customRate", (value) => {
                if (value !== null && value !== undefined && !this.loading) {
                    const rounded = parseFloat(Number(value).toFixed(3));
                    if (value !== rounded) {
                        this.customRate = rounded;
                    }
                }
            });
            
            // Re-convert if parameter changes
            this.$watch("amount", () => this.convertWithCustomRate());
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
                    this.processIncomingRate(data.rate);
                    this.lastRateUpdate = new Date().toLocaleString("ar-EG");
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
                    this.lastRateUpdate = new Date().toLocaleString("ar-EG");
                    this.processIncomingRate(data.rate);
                    
                    // Local recalculation
                    this.convertWithCustomRate();

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

        // Internal helper to handle rate conversion and precision
        processIncomingRate(rawRate) {
            this.conversionRate = rawRate;
            
            if (this.useInverseRate && rawRate > 0) {
                // New Logic: rawRate IS the Price (Base/Target)
                // Just use it as is for customRate (display)
                this.customRate = parseFloat(Number(rawRate).toFixed(3));
            } else if (rawRate > 0) {
                // If not using inverse mode, then we display Target/Base
                this.customRate = parseFloat((1 / rawRate).toFixed(3));
            } else {
                this.customRate = 0;
            }
        },

        // Convert using custom (user-entered) rate
        convertWithCustomRate() {
            // Force precision on customRate
            if (this.customRate !== null && this.customRate !== undefined) {
                this.customRate = parseFloat(Number(this.customRate).toFixed(3));
            }

            const activeRate = this.customRate;
            if (!activeRate || activeRate <= 0) {
                this.convertedAmount = 0;
                this.updateTargetField();
                return;
            }

            if (this.useInverseRate) {
                // Result (Target) = Amount (Base) / Price
                this.convertedAmount = this.amount / activeRate;
            } else {
                // Result (Target) = Amount (Base) * Rate
                this.convertedAmount = this.amount * activeRate;
            }

            // Force decimals on converted amount based on target currency
            const targetCurrency = this.currencies.find(c => c.code === this.toCurrency);
            const decimals = targetCurrency ? targetCurrency.decimal_places : 2;
            this.convertedAmount = parseFloat(this.convertedAmount.toFixed(decimals));

            this.updateTargetField();

            // Dispatch event with normalized rate
            let systemRate = activeRate;
            if (!this.useInverseRate) {
                // If we were displaying Target/Base, invert it back for System (Base/Target)
                systemRate = activeRate > 0 ? 1 / activeRate : 0;
            }

            this.$dispatch("currency-converted", {
                from: this.fromCurrency,
                to: this.toCurrency,
                rate: systemRate,
                amount: this.amount,
                converted: this.convertedAmount,
                isCustomRate: true,
                displayRate: activeRate
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

        // Format amount for display
        formatAmount(amount, currencyCode) {
            const currency = this.currencies.find(
                (c) => c.code === currencyCode
            );
            const decimals = (currency && currency.decimal_places !== undefined) ? currency.decimal_places : 2;
            return parseFloat(amount || 0).toFixed(decimals);
        },

        // Swap currencies
        swapCurrencies() {
            const temp = this.fromCurrency;
            this.fromCurrency = this.toCurrency;
            this.toCurrency = temp;

            this.convert();
        },

        // Reset custom rate to API rate
        resetRate() {
            this.processIncomingRate(this.conversionRate);
            this.convertWithCustomRate();
        },
    }));

    return true;
}

// Register as early as possible
if (typeof Alpine !== "undefined") {
    registerCurrencyConverter();
}
document.addEventListener("alpine:init", registerCurrencyConverter);
document.addEventListener("livewire:init", registerCurrencyConverter);
