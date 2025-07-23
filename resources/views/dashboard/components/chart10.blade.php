<div class="card">
    <div class="card-header">حركة المخزون خلال 6 أشهر</div>
    <div class="card-body">
        <canvas id="areaChart10" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('areaChart10'), {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [{
                label: 'رصيد المخزون',
                data: [120000, 115000, 110000, 108000, 112000, 117000],
                backgroundColor: 'rgba(0,123,255,0.2)',
                borderColor: '#007bff',
                fill: true
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 