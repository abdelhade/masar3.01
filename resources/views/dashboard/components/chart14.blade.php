<div class="card">
    <div class="card-header">المصروفات حسب البنود</div>
    <div class="card-body">
        <canvas id="doughnutChart14" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('doughnutChart14'), {
        type: 'doughnut',
        data: {
            labels: ['كهرباء', 'مياه', 'صيانة', 'تسويق', 'مصاريف أخرى'],
            datasets: [{
                label: 'المصروفات',
                data: [12000, 8000, 6000, 10000, 5000],
                backgroundColor: ['#dc3545', '#28a745', '#007bff', '#ffc107', '#6f42c1']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 