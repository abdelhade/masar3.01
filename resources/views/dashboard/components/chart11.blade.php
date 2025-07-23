<div class="card">
    <div class="card-header">توزيع أنواع الضرائب</div>
    <div class="card-body">
        <canvas id="pieChart11" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('pieChart11'), {
        type: 'pie',
        data: {
            labels: ['قيمة مضافة', 'دخل', 'استقطاع', 'زكاة'],
            datasets: [{
                label: 'الضرائب',
                data: [18000, 9000, 4000, 6000],
                backgroundColor: ['#20c997', '#e83e8c', '#6f42c1', '#ffc107']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 