<div class="card">
    <div class="card-header">الزكاة المدفوعة سنويًا</div>
    <div class="card-body">
        <canvas id="barChart12" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('barChart12'), {
        type: 'bar',
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023'],
            datasets: [{
                label: 'الزكاة (ريال)',
                data: [12000, 13500, 14000, 15000, 15500],
                backgroundColor: ['#fd7e14', '#007bff', '#28a745', '#6f42c1', '#dc3545']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 