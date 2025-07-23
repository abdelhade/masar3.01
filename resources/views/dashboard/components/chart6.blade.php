<div class="card">
    <div class="card-header">توزيع العملاء حسب المناطق</div>
    <div class="card-body">
        <canvas id="polarChart6" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('polarChart6'), {
        type: 'polarArea',
        data: {
            labels: ['الرياض', 'جدة', 'الدمام', 'مكة'],
            datasets: [{
                label: 'العملاء',
                data: [80, 60, 40, 30],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 