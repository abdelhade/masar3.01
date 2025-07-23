<div class="card">
    <div class="card-header">تطور عدد الموظفين (سنويًا)</div>
    <div class="card-body">
        <canvas id="hbarChart18" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('hbarChart18'), {
        type: 'bar',
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023'],
            datasets: [{
                label: 'عدد الموظفين',
                data: [45, 52, 60, 67, 70],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script> 