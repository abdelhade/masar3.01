<div class="card">
    <div class="card-header">أعلى 5 عملاء مبيعًا</div>
    <div class="card-body">
        <canvas id="hbarChart8" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('hbarChart8'), {
        type: 'bar',
        data: {
            labels: ['شركة الأمل', 'مؤسسة النجاح', 'شركة الريادة', 'مؤسسة التميز', 'شركة التطوير'],
            datasets: [{
                label: 'إجمالي المبيعات (ريال)',
                data: [95000, 87000, 82000, 79000, 76000],
                backgroundColor: ['#fd7e14', '#6f42c1', '#20c997', '#e83e8c', '#007bff']
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script> 