<div class="card">
    <div class="card-header">تطور الأرباح (سنويًا)</div>
    <div class="card-body">
        <canvas id="polarChart16" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('polarChart16'), {
        type: 'polarArea',
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023'],
            datasets: [{
                label: 'الأرباح',
                data: [18000, 22000, 25000, 27000, 30000],
                backgroundColor: ['#fd7e14', '#20c997', '#e83e8c', '#6f42c1', '#007bff']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 