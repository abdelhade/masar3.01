<div class="card">
    <div class="card-header">حركة المشتريات والمبيعات (شهور)</div>
    <div class="card-body">
        <canvas id="bubbleChart7" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('bubbleChart7'), {
        type: 'bubble',
        data: {
            datasets: [
                {
                    label: 'المبيعات',
                    data: [
                        {x:1, y:120000, r:10},
                        {x:2, y:135000, r:12},
                        {x:3, y:128000, r:11}
                    ],
                    backgroundColor: '#17a2b8'
                },
                {
                    label: 'المشتريات',
                    data: [
                        {x:1, y:80000, r:8},
                        {x:2, y:90000, r:9},
                        {x:3, y:85000, r:7}
                    ],
                    backgroundColor: '#fd7e14'
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 