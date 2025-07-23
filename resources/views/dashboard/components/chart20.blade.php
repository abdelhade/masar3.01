<div class="card">
    <div class="card-header">تطور متوسط الرواتب (سنويًا)</div>
    <div class="card-body">
        <canvas id="areaChart20" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('areaChart20'), {
        type: 'line',
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023'],
            datasets: [{
                label: 'متوسط الرواتب (ريال)',
                data: [6500, 6700, 7000, 7200, 7500],
                backgroundColor: 'rgba(32,201,151,0.2)',
                borderColor: '#20c997',
                fill: true
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 