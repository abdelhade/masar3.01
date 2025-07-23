<div class="card">
    <div class="card-header">مقارنة الإيرادات والمصروفات (شهريًا)</div>
    <div class="card-body">
        <canvas id="lineChart3" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('lineChart3'), {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [
                {
                    label: 'الإيرادات',
                    data: [150000, 160000, 155000, 170000, 180000, 175000],
                    borderColor: '#007bff',
                    fill: false
                },
                {
                    label: 'المصروفات',
                    data: [90000, 95000, 92000, 98000, 100000, 97000],
                    borderColor: '#dc3545',
                    fill: false
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 