
<div class="row g-4 mb-5">
    
    <div class="col-lg-8">
        <div class="stat-card">
            <h4 class="gradient-text fw-bold mb-4">
                <i class="fas fa-chart-bar me-2"></i>{{ __('general.project_progress_overview') }}
            </h4>
            <div class="chart-container">
                <canvas id="progressChart"></canvas>
            </div>
        </div>
    </div>

    
    <div class="col-lg-4">
        <div class="stat-card">
            <h4 class="gradient-text fw-bold mb-4">
                <i class="fas fa-chart-pie me-2"></i>{{ __('general.progress_distribution') }}
            </h4>
            <div class="chart-container">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // بيانات الرسوم البيانية
        const workItems = @json($chartData['work_items']);
        const completionPercentages = @json($chartData['completion_percentages']);
        const weeklyProgress = @json($chartData['weekly_progress']);

        // مخطط التقدم (Bar Chart)
        const progressCtx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: workItems,
                datasets: [{
                    label: '{{ __('general.progress_percentage') }}',
                    data: completionPercentages,
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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
                    }
                }
            }
        });

        // مخطط الدونات (Donut Chart)
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['{{ __('general.completed') }}', '{{ __('general.remaining') }}'],
                datasets: [{
                    data: [{{ $overallProgress }}, {{ 100 - $overallProgress }}],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush

