<div class="card">
    <div class="card-header">أعلى 5 موردين شراءً</div>
    <div class="card-body">
        <canvas id="mixedChart9" style="width:100%;height:250px;"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('mixedChart9'), {
        data: {
            labels: ['مورد الشرق', 'مورد الغرب', 'مورد الجنوب', 'مورد الشمال', 'مورد الوسط'],
            datasets: [
                {
                    type: 'bar',
                    label: 'إجمالي المشتريات (ريال)',
                    data: [67000, 62000, 59000, 57000, 55000],
                    backgroundColor: '#007bff'
                },
                {
                    type: 'line',
                    label: 'عدد الفواتير',
                    data: [22, 19, 17, 15, 13],
                    borderColor: '#dc3545',
                    fill: false
                }
            ]
        },
        options: {responsive: true, maintainAspectRatio: false}
    });
</script> 