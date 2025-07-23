<div class="card">
    <div class="card-header">الإيرادات حسب الفروع (شهريًا)</div>
    <div class="card-body">
        <canvas id="lineChart13" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('lineChart13'), {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [
                {
                    label: 'فرع الرياض',
                    data: [50000, 52000, 51000, 53000, 54000, 55000],
                    borderColor: '#007bff',
                    fill: false
                },
                {
                    label: 'فرع جدة',
                    data: [30000, 32000, 31000, 33000, 34000, 35000],
                    borderColor: '#28a745',
                    fill: false
                },
                {
                    label: 'فرع الدمام',
                    data: [20000, 21000, 22000, 23000, 24000, 25000],
                    borderColor: '#fd7e14',
                    fill: false
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 