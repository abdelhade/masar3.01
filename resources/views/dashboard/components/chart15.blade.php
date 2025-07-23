<div class="card">
    <div class="card-header">حركة النقدية (أشهر)</div>
    <div class="card-body">
        <canvas id="radarChart15" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('radarChart15'), {
        type: 'radar',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو'],
            datasets: [{
                label: 'النقدية',
                data: [25000, 27000, 26000, 28000, 29000],
                backgroundColor: 'rgba(220,53,69,0.2)',
                borderColor: '#dc3545'
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 