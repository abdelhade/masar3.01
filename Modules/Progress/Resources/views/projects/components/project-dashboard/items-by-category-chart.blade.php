<div class="card stat-card border-0 shadow-sm mb-4"
     x-data="itemsByCategoryChart()" 
     x-init="initChart()">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fas fa-folder me-2"></i>{{ __('general.items_by_category') }}
        </h5>
        <!-- Dropdown Filter -->
        <div style="min-width: 200px;">
            <select class="form-select form-select-sm" 
                    id="categorySelect"
                    @change="updateChart($el.value)">
            </select>
        </div>
    </div>
    <div class="card-body">
        
        <!-- Legend Explanation -->
        <div class="mb-3 small text-muted">
            <span class="me-3"><strong style="color: #ffab40;">{{ __('general.progress') }}</strong> ({{ __('general.actual') }})</span>
            <span><strong style="color: #6398f5;">{{ __('general.planned_progress') }}</strong> ({{ __('general.planned') }})</span>
        </div>

        <!-- Chart -->
        <div style="height: 400px; width: 100%;">
            <canvas id="itemsByCategoryChart"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemsByCategoryChart', () => ({
            chart: null,
            allData: @json($itemsByCategory),
            currentCategory: null,

            initChart() {
                const select = document.getElementById('categorySelect');
                
                // Populate Dropdown
                const categories = Object.keys(this.allData);
                if (categories.length === 0) {
                     select.innerHTML = '<option disabled>No Data</option>';
                     return;
                }

                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat;
                    option.text = cat;
                    select.appendChild(option);
                });

                // Set Default
                this.currentCategory = categories[0];
                select.value = this.currentCategory;

                // Initialize Chart
                const initialData = this.getDataForCategory(this.currentCategory);
                this.renderChart(initialData);
            },

            renderChart(data) {
                const ctx = document.getElementById('itemsByCategoryChart').getContext('2d');
                
                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: '{{ __("general.progress") }}',
                                data: data.progress,
                                backgroundColor: '#ffab40', // Orange
                                borderColor: '#ffab40',
                                borderWidth: 1,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            },
                            {
                                label: '{{ __("general.planned_progress") }}',
                                data: data.planned,
                                backgroundColor: '#6398f5', // Blue
                                borderColor: '#6398f5',
                                borderWidth: 1,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
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
                        },
                         plugins: {
                            legend: {
                                display: false // Custom legend above
                            }
                        }
                    }
                });
            },

            getDataForCategory(catName) {
                const items = this.allData[catName]?.items || [];
                return {
                    labels: items.map(i => i.label),
                    progress: items.map(i => i.progress),
                    planned: items.map(i => i.planned)
                };
            },

            updateChart(catName) {
                const newData = this.getDataForCategory(catName);
                this.renderChart(newData);
            }
        }));
    });
</script>
