/**
 * Employee Form Alpine.js Component - Page-based Scripts
 * 
 * Scripts for employee create/edit pages (non-modal)
 */

(function () {
    'use strict';

    /**
     * Constants for Employee Form Component
     */
    const CONSTANTS = {
        MAX_IMAGE_SIZE: 2 * 1024 * 1024, // 2MB in bytes
        VALID_IMAGE_TYPES: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'],
        DEBOUNCE_DELAY: 300, // milliseconds
        NOTIFICATION_TIMEOUT: 3000 // milliseconds
    };

    /**
     * Employee Form Manager Component
     * @param {Object} config - Component configuration
     * @returns {Object} Alpine component
     */
    function employeeFormManager(config = {}) {
        return {
            // ==========================================
            // State - synced with Livewire
            // ==========================================
            kpiIds: config.kpiIds || [],
            kpiWeights: config.kpiWeights || {},
            selectedKpiId: config.selectedKpiId || '',
            currentImageUrl: config.currentImageUrl || null,
            isEdit: config.isEdit || false,

            // ==========================================
            // Local State
            // ==========================================
            kpis: config.kpis || [],
            leaveTypes: config.leaveTypes || [],
            leaveBalances: config.leaveBalances || {},
            selectedLeaveTypeId: config.selectedLeaveTypeId || '',
            activeTab: 'personal',
            notifications: [],
            imagePreview: null,
            showPassword: false,
            selectedFileName: '',
            selectedFileSize: '',
            isDragging: false,
            imageLoading: false,

            // KPI Search state
            kpiSearch: '',
            kpiSearchOpen: false,
            kpiSearchIndex: -1,

            // Leave Type Search state
            leaveTypeSearch: '',
            leaveTypeSearchOpen: false,
            leaveTypeSearchIndex: -1,

            // ==========================================
            // Computed Properties
            // ==========================================
            get totalKpiWeight() {
                let total = 0;
                this.kpiIds.forEach(kpiId => {
                    total += parseInt(this.kpiWeights[kpiId]) || 0;
                });
                return total;
            },

            get weightStatus() {
                if (this.totalKpiWeight === 100) return 'success';
                if (this.totalKpiWeight > 100) return 'danger';
                return 'warning';
            },

            get weightMessage() {
                if (this.totalKpiWeight === 100) {
                    return 'ممتاز! تم اكتمال النسبة بنجاح. يمكنك الآن حفظ البيانات.';
                } else if (this.totalKpiWeight > 100) {
                    return `المجموع الحالي ${this.totalKpiWeight}% أكبر من 100%. يرجى تقليل الأوزان.`;
                } else {
                    return `المجموع الحالي ${this.totalKpiWeight}% أقل من 100%. يرجى إكمال الأوزان.`;
                }
            },

            get availableKpis() {
                return this.kpis.filter(kpi => !this.kpiIds.includes(kpi.id));
            },

            get filteredKpis() {
                if (!this.kpiSearch) return this.availableKpis;
                const search = this.kpiSearch.toLowerCase();
                return this.availableKpis.filter(kpi =>
                    kpi.name.toLowerCase().includes(search) ||
                    (kpi.description && kpi.description.toLowerCase().includes(search))
                );
            },

            get leaveBalanceIds() {
                return Object.keys(this.leaveBalances || {});
            },

            get availableLeaveTypes() {
                const addedLeaveTypeIds = Object.values(this.leaveBalances || {})
                    .map(b => b.leave_type_id);
                return this.leaveTypes.filter(lt => !addedLeaveTypeIds.includes(lt.id));
            },

            get filteredLeaveTypes() {
                if (!this.leaveTypeSearch) return this.availableLeaveTypes;
                const search = this.leaveTypeSearch.toLowerCase();
                return this.availableLeaveTypes.filter(lt =>
                    lt.name.toLowerCase().includes(search) ||
                    (lt.code && lt.code.toLowerCase().includes(search))
                );
            },


            // ==========================================
            // Methods
            // ==========================================

            /**
             * Safely execute a function with error handling
             */
            safeExecute(fn, errorMessage = 'حدث خطأ غير متوقع', defaultValue = null) {
                try {
                    return fn();
                } catch (error) {
                    console.error('Error in employeeFormManager:', error);
                    
                    let message = errorMessage;
                    if (error instanceof Error) {
                        if (error.message.includes('rate_limit') || error.message.includes('Rate limit')) {
                            message = 'تم تجاوز الحد المسموح. يرجى المحاولة مرة أخرى بعد قليل.';
                        } else if (error.message.includes('unauthorized') || error.message.includes('403')) {
                            message = 'ليس لديك صلاحية لتنفيذ هذا الإجراء.';
                        } else if (error.message.includes('network') || error.message.includes('fetch')) {
                            message = 'فشل الاتصال بالخادم. يرجى التحقق من الاتصال بالإنترنت.';
                        } else if (error.message) {
                            message = error.message;
                        }
                    }
                    
                    this.addNotification('error', message);
                    return defaultValue;
                }
            },

            init() {
                // Listen for Livewire notifications
                this.$wire.on('notify', (data) => {
                    if (data && data.type && data.message) {
                        this.addNotification(data.type, data.message);
                    }
                });

                // Listen for Livewire errors
                this.$wire.on('$error', (errors) => {
                    if (errors && Object.keys(errors).length > 0) {
                        const firstError = Object.values(errors)[0];
                        if (Array.isArray(firstError)) {
                            this.addNotification('error', firstError[0]);
                        } else {
                            this.addNotification('error', firstError);
                        }
                    }
                });

                // Listen for Livewire validation errors
                this.$wire.on('$validation', (errors) => {
                    if (errors && Object.keys(errors).length > 0) {
                        const errorMessages = Object.values(errors).flat();
                        errorMessages.forEach(message => {
                            this.addNotification('error', message);
                        });
                    }
                });

                // Listen for employee saved event
                this.$wire.on('employee-saved', () => {
                    this.resetImagePreview();
                });

                // Listen for KPI added event
                this.$wire.on('kpiAdded', () => {
                    this.clearKpiSelection();
                });

                // Listen for leave balance added event
                this.$wire.on('leaveBalanceAdded', () => {
                    this.clearLeaveTypeSelection();
                });

                // Setup keyboard shortcuts
                this.setupKeyboardShortcuts();
            },


            /**
             * Add a notification
             */
            addNotification(type, message) {
                const id = Date.now();
                this.notifications.push({
                    id,
                    type,
                    message
                });
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, CONSTANTS.NOTIFICATION_TIMEOUT);
            },

            /**
             * Switch to a different tab
             */
            switchTab(tab) {
                this.activeTab = tab;
            },

            // Image handling
            /**
             * Handle image file change event
             */
            handleImageChange(event) {
                this.safeExecute(() => {
                    const file = event.target.files[0];
                    if (file) {
                        // Validate file type
                        if (!CONSTANTS.VALID_IMAGE_TYPES.includes(file.type)) {
                            this.addNotification('error', 'يرجى اختيار صورة صالحة');
                            event.target.value = '';
                            return;
                        }

                        // Validate file size
                        if (file.size > CONSTANTS.MAX_IMAGE_SIZE) {
                            this.addNotification('error', 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت');
                            event.target.value = '';
                            return;
                        }

                        // Set file info
                        this.selectedFileName = file.name;
                        this.selectedFileSize = this.formatFileSize(file.size);

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.onerror = () => {
                            this.addNotification('error', 'فشل في قراءة الصورة');
                        };
                        reader.readAsDataURL(file);
                    }
                }, 'حدث خطأ أثناء معالجة الصورة');
            },

            /**
             * Handle image drop event
             */
            handleImageDrop(event) {
                this.safeExecute(() => {
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        // Validate file type
                        if (!CONSTANTS.VALID_IMAGE_TYPES.includes(file.type)) {
                            this.addNotification('error', 'يرجى اختيار صورة صالحة');
                            return;
                        }

                        // Validate file size
                        if (file.size > CONSTANTS.MAX_IMAGE_SIZE) {
                            this.addNotification('error', 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت');
                            return;
                        }

                        // Set file to input and trigger Livewire upload
                        const input = document.getElementById('employee-image-input');
                        if (input) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            input.files = dataTransfer.files;

                            // Trigger change event for Livewire
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));

                            // Set file info
                            this.selectedFileName = file.name;
                            this.selectedFileSize = this.formatFileSize(file.size);

                            // Create preview
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.imagePreview = e.target.result;
                            };
                            reader.onerror = () => {
                                this.addNotification('error', 'فشل في قراءة الصورة');
                            };
                            reader.readAsDataURL(file);

                            this.addNotification('success', 'تم اختيار الصورة بنجاح');
                        }
                    }
                }, 'حدث خطأ أثناء معالجة الصورة');
            },

            /**
             * Remove the selected image
             */
            removeImage() {
                this.imagePreview = null;
                this.selectedFileName = '';
                this.selectedFileSize = '';
                this.currentImageUrl = null;

                const input = document.getElementById('employee-image-input');
                if (input) {
                    input.value = '';
                }

                this.$wire.set('image', null);
                this.addNotification('info', 'تم إزالة الصورة');
            },

            /**
             * Format file size
             */
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            },

            /**
             * Reset image preview
             */
            resetImagePreview() {
                this.imagePreview = null;
                this.selectedFileName = '';
                this.selectedFileSize = '';
                this.isDragging = false;
                this.imageLoading = false;
            },

            /**
             * Toggle password visibility
             */
            togglePassword() {
                this.showPassword = !this.showPassword;
            },

            // KPI Management
            /**
             * Get KPI name by ID
             */
            getKpiName(kpiId) {
                const kpi = this.kpis.find(k => k.id == kpiId);
                return kpi ? kpi.name : '';
            },

            /**
             * Get KPI description by ID
             */
            getKpiDescription(kpiId) {
                const kpi = this.kpis.find(k => k.id == kpiId);
                return kpi && kpi.description ? kpi.description.substring(0, 50) + '...' : '';
            },

            /**
             * Select a KPI
             */
            selectKpi(kpi) {
                this.selectedKpiId = kpi.id;
                this.kpiSearchOpen = false;
                this.kpiSearch = '';
                this.kpiSearchIndex = -1;
            },

            /**
             * Clear KPI selection
             */
            clearKpiSelection() {
                this.selectedKpiId = '';
                this.kpiSearch = '';
            },

            /**
             * Navigate down in KPI dropdown
             */
            navigateKpiDown() {
                if (this.kpiSearchIndex < this.filteredKpis.length - 1) {
                    this.kpiSearchIndex++;
                }
            },

            /**
             * Navigate up in KPI dropdown
             */
            navigateKpiUp() {
                if (this.kpiSearchIndex > 0) {
                    this.kpiSearchIndex--;
                }
            },

            /**
             * Select the currently highlighted KPI
             */
            selectCurrentKpi() {
                if (this.kpiSearchIndex >= 0 && this.kpiSearchIndex < this.filteredKpis.length) {
                    this.selectKpi(this.filteredKpis[this.kpiSearchIndex]);
                }
            },

            // Leave Type Management
            /**
             * Get Leave Type name by ID
             */
            getLeaveTypeName(leaveTypeId) {
                const leaveType = this.leaveTypes.find(lt => lt.id == leaveTypeId);
                return leaveType ? leaveType.name : '';
            },

            /**
             * Select a Leave Type
             */
            selectLeaveType(leaveType) {
                this.selectedLeaveTypeId = leaveType.id;
                this.leaveTypeSearchOpen = false;
                this.leaveTypeSearch = '';
                this.leaveTypeSearchIndex = -1;
            },

            /**
             * Clear Leave Type selection
             */
            clearLeaveTypeSelection() {
                this.selectedLeaveTypeId = '';
                this.leaveTypeSearch = '';
            },

            /**
             * Navigate down in Leave Type dropdown
             */
            navigateLeaveTypeDown() {
                if (this.leaveTypeSearchIndex < this.filteredLeaveTypes.length - 1) {
                    this.leaveTypeSearchIndex++;
                }
            },

            /**
             * Navigate up in Leave Type dropdown
             */
            navigateLeaveTypeUp() {
                if (this.leaveTypeSearchIndex > 0) {
                    this.leaveTypeSearchIndex--;
                }
            },

            /**
             * Select the currently highlighted Leave Type
             */
            selectCurrentLeaveType() {
                if (this.leaveTypeSearchIndex >= 0 && this.leaveTypeSearchIndex < this.filteredLeaveTypes.length) {
                    this.selectLeaveType(this.filteredLeaveTypes[this.leaveTypeSearchIndex]);
                }
            },

            /**
             * Calculate remaining days for a leave balance
             */
            calculateRemainingDays(balance) {
                const opening = parseFloat(balance.opening_balance_days) || 0;
                const accrued = parseFloat(balance.accrued_days) || 0;
                const carried = parseFloat(balance.carried_over_days) || 0;
                const used = parseFloat(balance.used_days) || 0;
                const pending = parseFloat(balance.pending_days) || 0;
                const remaining = opening + accrued + carried - used - pending;
                return remaining.toFixed(1);
            },

            /**
             * Setup keyboard shortcuts
             */
            setupKeyboardShortcuts() {
                this._keyboardHandler = (event) => {
                    // Don't trigger shortcuts when typing
                    if (event.target.tagName === 'INPUT' ||
                        event.target.tagName === 'TEXTAREA' ||
                        event.target.tagName === 'SELECT') {
                        return;
                    }

                    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
                    const ctrlKey = isMac ? event.metaKey : event.ctrlKey;

                    // Esc: Close dropdowns
                    if (event.key === 'Escape') {
                        this.kpiSearchOpen = false;
                        this.leaveTypeSearchOpen = false;
                    }

                    // Ctrl+1-8: Navigate between tabs
                    if (ctrlKey && event.key >= '1' && event.key <= '8') {
                        event.preventDefault();
                        const tabIndex = parseInt(event.key) - 1;
                        const tabs = ['personal', 'location', 'job', 'salary', 'attendance', 'kpi', 'Accounting', 'leaveBalances'];
                        if (tabs[tabIndex]) {
                            this.switchTab(tabs[tabIndex]);
                        }
                    }
                };

                document.addEventListener('keydown', this._keyboardHandler);
            },

            /**
             * Cleanup
             */
            destroy() {
                if (this._keyboardHandler) {
                    document.removeEventListener('keydown', this._keyboardHandler);
                    this._keyboardHandler = null;
                }
            }
        };
    }

    // Expose function to window
    window.employeeFormManager = employeeFormManager;
})();

