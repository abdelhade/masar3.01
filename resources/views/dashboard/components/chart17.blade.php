<div class="card">
    <div class="card-header">توزيع الموردين حسب المناطق</div>
    <div class="card-body">
        <canvas id="bubbleChart17" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('bubbleChart17'), {
        type: 'bubble',
        data: {
            datasets: [
                {
                    label: 'الرياض',
                    data: [{x:1, y:20, r:10}],
                    backgroundColor: '#007bff'
                },
                {
                    label: 'جدة',
                    data: [{x:2, y:15, r:8}],
                    backgroundColor: '#28a745'
                },
                {
                    label: 'الدمام',
                    data: [{x:3, y:10, r:6}],
                    backgroundColor: '#fd7e14'
                },
                {
                    label: 'مكة',
                    data: [{x:4, y:8, r:5}],
                    backgroundColor: '#dc3545'
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 