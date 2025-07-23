<div class="card">
    <div class="card-header">توزيع المصروفات السنوية</div>
    <div class="card-body">
        <canvas id="pieChart2" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('pieChart2'), {
        type: 'pie',
        data: {
            labels: ['رواتب', 'إيجارات', 'مشتريات', 'خدمات', 'أخرى'],
            datasets: [{
                label: 'المصروفات',
                data: [350000, 90000, 120000, 40000, 20000],
                backgroundColor: ['#17a2b8', '#6f42c1', '#fd7e14', '#28a745', '#dc3545']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 