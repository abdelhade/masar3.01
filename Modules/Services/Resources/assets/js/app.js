// Services Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-refresh booking status
    if (window.location.pathname.includes('bookings')) {
        setInterval(function() {
            // Check for new bookings or status updates
            // This could be enhanced with WebSocket or Server-Sent Events
        }, 30000); // Check every 30 seconds
    }

    // Service form enhancements
    const serviceForm = document.getElementById('service-form');
    if (serviceForm) {
        // Auto-calculate duration in hours when minutes change
        const durationDisplay = document.getElementById('duration-display');
        
    }

    // Booking form enhancements
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        const serviceSelect = document.getElementById('service_id');
        const priceInput = document.getElementById('price');
        
        if (serviceSelect && priceInput) {
            serviceSelect.addEventListener('change', function() {
                const serviceId = this.value;
                if (serviceId) {
                    // Fetch service details and update price
                    fetch(`/api/services/${serviceId}`)
                        .then(response => response.json())
                        .then(data => {
                            priceInput.value = data.price;
                            if (durationInput) {
                                // Duration is now fixed at 60 minutes
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        }
    }
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-SA', {
        style: 'currency',
        currency: 'SAR'
    }).format(amount);
}

function formatTime(timeString) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('ar-SA', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Export functions for use in other scripts
window.ServicesModule = {
    formatCurrency,
    formatTime
};
