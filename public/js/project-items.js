$(document).ready(function() {
    // حساب الكمية اليومية التلقائية
    $('.calculate-daily').on('change', function() {
        const total = parseFloat($('input[name="total_quantity"]').val()) || 0;
        const start = new Date($('input[name="start_date"]').val());
        const end = new Date($('input[name="end_date"]').val());
        
        if (start && end && start < end) {
            const days = (end - start) / (1000 * 60 * 60 * 24) + 1;
            const daily = total / days;
            $('input[name="daily_quantity"]').val(daily.toFixed(2));
        }
    });
});