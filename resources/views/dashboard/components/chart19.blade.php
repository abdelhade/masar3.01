<div class="card">
    <div class="card-header">مقارنة عدد العملاء والموردين (سنويًا)</div>
    <div class="card-body">
        <canvas id="mixedChart19" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('mixedChart19'), {
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023'],
            datasets: [
                {
                    type: 'bar',
                    label: 'العملاء',
                    data: [120, 135, 150, 170, 180],
                    backgroundColor: '#20c997'
                },
                {
                    type: 'line',
                    label: 'الموردين',
                    data: [80, 90, 100, 110, 120],
                    borderColor: '#fd7e14',
                    fill: false
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 