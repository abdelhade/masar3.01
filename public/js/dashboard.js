/**
 * Dashboard - Enhanced JavaScript
 * Features: Validation, Skeleton Loaders, Animations, Charts
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ====================================
    // 1. Skeleton Loaders
    // ====================================
    
    function showSkeletonLoaders() {
        const statsContainer = document.querySelector('.stats-container');
        if (!statsContainer) return;
        
        const skeleton = `
            <div class="col-md-3 mb-4">
                <div class="skeleton skeleton-card"></div>
            </div>
        `.repeat(4);
        
        statsContainer.innerHTML = skeleton;
    }
    
    function hideSkeletonLoaders() {
        // يتم إخفاءها تلقائياً عند تحميل البيانات الحقيقية
    }
    
    
    // ====================================
    // 2. Filter Validation
    // ====================================
    
    const filterForm = document.getElementById('filterForm');
    const employeeSelect = document.getElementById('employee_id');
    const projectSelect = document.getElementById('project_id');
    const dateRangeInput = document.getElementById('date_range');
    const applyFilterBtn = document.getElementById('applyFilters');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Reset all validations
            clearValidationErrors();
            
            // Validate date_range
            if (dateRangeInput && dateRangeInput.value) {
                const value = parseInt(dateRangeInput.value);
                
                if (isNaN(value) || value < 1) {
                    showValidationError(dateRangeInput, 'يجب أن يكون عدد الأيام رقماً موجباً');
                    isValid = false;
                } else if (value > 365) {
                    showValidationError(dateRangeInput, 'عدد الأيام لا يمكن أن يتجاوز 365 يوماً');
                    isValid = false;
                }
            }
            
            if (isValid) {
                // Show loading
                if (applyFilterBtn) {
                    applyFilterBtn.disabled = true;
                    const originalHTML = applyFilterBtn.innerHTML;
                    applyFilterBtn.innerHTML = '<span class="loading-spinner me-2"></span>جاري التحميل...';
                }
                
                // Submit form
                this.submit();
            }
        });
    }
    
    function showValidationError(input, message) {
        input.classList.add('is-invalid');
        
        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }
    
    // Clear validation on input
    if (dateRangeInput) {
        dateRangeInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const feedback = this.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        });
    }
    
    
    // ====================================
    // 3. Stats Animation (Count Up)
    // ====================================
    
    function animateValue(element, start, end, duration) {
        if (!element) return;
        
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Animate stats on page load
    const statsValues = document.querySelectorAll('.stats-card .value');
    statsValues.forEach(valueEl => {
        const finalValue = parseInt(valueEl.textContent.replace(/,/g, '')) || 0;
        valueEl.textContent = '0';
        
        // Add intersection observer for animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        animateValue(valueEl, 0, finalValue, 1500);
                    }, 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(valueEl);
    });
    
    
    // ====================================
    // 4. Enhanced Chart Tooltips
    // ====================================
    
    // Custom tooltip for Chart.js
    window.customChartTooltip = {
        enabled: true,
        mode: 'index',
        intersect: false,
        backgroundColor: 'rgba(0, 0, 0, 0.9)',
        titleColor: '#fff',
        bodyColor: '#fff',
        borderColor: '#4361ee',
        borderWidth: 2,
        cornerRadius: 8,
        padding: 12,
        displayColors: true,
        callbacks: {
            label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                    label += ': ';
                }
                if (context.parsed.y !== null) {
                    label += context.parsed.y.toLocaleString();
                }
                return label;
            },
            title: function(tooltipItems) {
                return tooltipItems[0].label;
            },
            afterLabel: function(context) {
                // Add percentage if needed
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                return `(${percentage}%)`;
            }
        }
    };
    
    // Apply to all charts
    if (typeof Chart !== 'undefined') {
        Chart.defaults.plugins.tooltip = window.customChartTooltip;
        
        // Smooth animations
        Chart.defaults.animation = {
            duration: 1500,
            easing: 'easeInOutQuart'
        };
    }
    
    
    // ====================================
    // 5. Progress Bar Animation
    // ====================================
    
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 200);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(bar);
    });
    
    
    // ====================================
    // 6. Print Functionality
    // ====================================
    
    window.printDashboard = function() {
        // Add print date
        const printDate = document.createElement('div');
        printDate.className = 'print-header d-none';
        printDate.innerHTML = `
            <h2>لوحة المعلومات</h2>
            <p class="print-date">تاريخ الطباعة: ${new Date().toLocaleDateString('ar-SA')}</p>
        `;
        document.body.insertBefore(printDate, document.body.firstChild);
        
        // Print
        window.print();
        
        // Remove print header after print
        setTimeout(() => {
            printDate.remove();
        }, 100);
    };
    
    
    // ====================================
    // 7. Auto-refresh (Optional)
    // ====================================
    
    let autoRefreshInterval = null;
    
    window.toggleAutoRefresh = function(enable) {
        if (enable) {
            // Refresh every 5 minutes
            autoRefreshInterval = setInterval(() => {
                location.reload();
            }, 5 * 60 * 1000);
            
            showToast('تم تفعيل التحديث التلقائي (كل 5 دقائق)', 'success');
        } else {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
            showToast('تم إيقاف التحديث التلقائي', 'info');
        }
    };
    
    
    // ====================================
    // 8. Toast Notifications
    // ====================================
    
    function showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.custom-toast').forEach(t => t.remove());
        
        const toast = document.createElement('div');
        const bgClass = type === 'success' ? 'alert-success' : 
                        type === 'error' ? 'alert-danger' : 
                        type === 'warning' ? 'alert-warning' : 'alert-info';
        
        toast.className = `alert ${bgClass} custom-toast position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }
    
    window.showToast = showToast;
    
    
    // ====================================
    // 9. Responsive Table
    // ====================================
    
    function makeTablesResponsive() {
        const tables = document.querySelectorAll('.table');
        tables.forEach(table => {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }
    
    makeTablesResponsive();
    
    
    // ====================================
    // 10. Keyboard Shortcuts
    // ====================================
    
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P = Print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.printDashboard();
        }
        
        // Ctrl/Cmd + R = Refresh filters
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            if (applyFilterBtn) {
                applyFilterBtn.click();
            }
        }
    });
    
});

// ====================================
// CSS Animations
// ====================================

const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

