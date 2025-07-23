<div class="card">
    <div class="card-header">توزيع المخزون حسب الفئات</div>
    <div class="card-body">
        <canvas id="doughnutChart4" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('doughnutChart4'), {
        type: 'doughnut',
        data: {
            labels: ['مواد خام', 'منتجات تامة', 'منتجات تحت التشغيل', 'قطع غيار'],
            datasets: [{
                label: 'المخزون',
                data: [50000, 120000, 30000, 15000],
                backgroundColor: ['#dc3545', '#007bff', '#ffc107', '#28a745']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 