<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-4">
                    <i class="fas fa-chart-bar me-2"></i>{{ __('general.project_progress_overview') }}
                </h5>
                <div class="chart-container" style="position: relative; height:300px; width:100%">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-4">
                    <i class="fas fa-chart-pie me-2"></i>{{ __('general.progress_distribution') }}
                </h5>
                <div class="chart-container" style="position: relative; height:250px; width:100%">
                    <canvas id="donutChart"></canvas>
                </div>
                <div class="mt-4 text-center">
                    <small class="text-muted d-block">{{ __('general.based_on_completed_items') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bar Chart
        const ctxProgress = document.getElementById('progressChart').getContext('2d');
        new Chart(ctxProgress, {
            type: 'bar',
            data: {
                labels: @json($chartData['work_items']),
                datasets: [{
                    label: '{{ __('general.completion_percentage') }}',
                    data: @json($chartData['completion_percentages']),
                    backgroundColor: '#3498db',
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Donut Chart
        const ctxDonut = document.getElementById('donutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['{{ __('general.completed') }}', '{{ __('general.remaining') }}'],
                datasets: [{
                    data: [{{ $overallProgress }}, {{ 100 - $overallProgress }}],
                    backgroundColor: ['#2ecc71', '#ecf0f1'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
