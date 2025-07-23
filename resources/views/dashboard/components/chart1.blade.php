<div class="card">
    <div class="card-header">تطور المبيعات الشهرية (ريال)</div>
    <div class="card-body">
        <canvas id="barChart1" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('barChart1'), {
        type: 'bar',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
            datasets: [{
                label: 'المبيعات',
                data: [120000, 135000, 128000, 142000, 150000, 160000, 170000, 165000, 158000, 175000, 180000, 190000],
                backgroundColor: '#007bff'
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 