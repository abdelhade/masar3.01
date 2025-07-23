<div class="card">
    <div class="card-header">توزيع الرواتب حسب الأقسام</div>
    <div class="card-body">
        <canvas id="radarChart5" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('radarChart5'), {
        type: 'radar',
        data: {
            labels: ['إدارة', 'مبيعات', 'إنتاج', 'مخازن', 'مالية'],
            datasets: [{
                label: 'الرواتب',
                data: [35000, 25000, 40000, 15000, 20000],
                backgroundColor: 'rgba(40,167,69,0.2)',
                borderColor: '#28a745'
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 