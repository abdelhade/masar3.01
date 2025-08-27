@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('المباني والوحدات السكنية'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('المباني والوحدات السكنية')],
        ],
    ])
    <div class="container-fluid px-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المباني</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $buildings->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي الوحدات</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $units->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">وحدات مؤجرة</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $units->where('status', \Modules\Rentals\Enums\UnitStatus::RENTED)->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-key fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">وحدات متاحة</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $units->where('status', \Modules\Rentals\Enums\UnitStatus::AVAILABLE)->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-door-open fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buildings Section -->
        <div id="buildingsSection" class="section-container">
            <div class="row mb-3">

                <div class="col-10">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-building text-primary me-2"></i>
                            المباني السكنية
                        </h2>
                    </div>
                </div>

                <div class="col-2 text-end">
                    <a href="{{ route('rentals.buildings.create') }}" class="btn btn-lg btn-primary">
                        <i class="fas fa-plus me-1"></i> إضافة مبنى جديد
                    </a>
                </div>
            </div>

            <div class="row">
                @foreach ($buildings as $building)
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card building-card shadow-lg border-0 h-100">
                            <div class="card-header bg-gradient-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-building me-2"></i>
                                        {{ $building->name }}
                                    </h5>
                                    <div class="dropdown">
                                        <a href="{{ route('rentals-units.create', $building->id) }}"
                                            class="btn btn-sm btn-info" type="button">
                                            <i class="fas fa-plus"></i> أضافة وحدات
                                        </a>

                                        <a href="{{ route('rentals.buildings.edit', $building->id) }}"
                                            class="btn btn-sm btn-success" type="button">
                                            <i class="fas fa-edit"></i> تعديل
                                        </a>

                                        <form action="{{ route('rentals.buildings.destroy', $building->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا المبنى؟')">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="building-info">
                                    <div class="info-item mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <span class="text-muted">{{ $building->address ?: 'لم يتم تحديد العنوان' }}</span>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="info-box text-center">
                                                <i class="fas fa-layer-group text-info"></i>
                                                <div class="info-number">{{ $building->floors ?: 0 }}</div>
                                                <div class="info-label">طابق</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box text-center">
                                                <i class="fas fa-home text-success"></i>
                                                <div class="info-number">{{ $building->units->count() }}</div>
                                                <div class="info-label">وحدة</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($building->area)
                                        <div class="info-item mb-2">
                                            <i class="fas fa-ruler-combined text-warning me-2"></i>
                                            <span class="text-muted">{{ $building->area }} م²</span>
                                        </div>
                                    @endif

                                    <!-- Units Status -->
                                    <div class="units-status mt-3">
                                        <h6 class="mb-2">حالة الوحدات:</h6>
                                        <div class="row text-center">
                                            @foreach (\Modules\Rentals\Enums\UnitStatus::cases() as $status)
                                                <div class="col-4">
                                                    <div class="status-item {{ strtolower($status->name) }}">
                                                        <div class="status-number">
                                                            {{ $building->units->where('status', $status)->count() }}
                                                        </div>
                                                        <div class="status-label">{{ $status->label() }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer bg-light">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('rentals.buildings.show', $building->id) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>عرض الوحدات
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection

@section('styles')
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Stats Cards */
        .stats-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        /* Section Headers */
        .section-header {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .section-title {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Building Cards */
        .building-card {
            border-radius: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
            border: none;
        }

        .building-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
        }

        .info-box {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .info-box:hover {
            background: #e3e6f0;
            transform: scale(1.05);
        }

        .info-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a5c69;
            margin: 5px 0;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }

        .status-item {
            padding: 10px;
            border-radius: 10px;
            margin: 2px;
        }

        .status-item.available {
            background: rgba(28, 200, 138, 0.1);
            border: 1px solid rgba(28, 200, 138, 0.3);
        }

        .status-item.rented {
            background: rgba(246, 194, 62, 0.1);
            border: 1px solid rgba(246, 194, 62, 0.3);
        }

        .status-item.maintenance {
            background: rgba(231, 74, 59, 0.1);
            border: 1px solid rgba(231, 74, 59, 0.3);
        }

        .status-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #5a5c69;
        }

        .status-label {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Unit Cards */
        .unit-card {
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .unit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .unit-header {
            border: none;
            padding: 15px;
        }

        .unit-header.status-available {
            background: linear-gradient(45deg, #1cc88a, #17a2b8);
        }

        .unit-header.status-rented {
            background: linear-gradient(45deg, #f6c23e, #fd7e14);
        }

        .unit-header.status-maintenance {
            background: linear-gradient(45deg, #e74a3b, #c0392b);
        }

        .badge-status {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .unit-body {
            padding: 20px;
        }

        .building-ref {
            padding: 8px 12px;
            background: #f8f9fc;
            border-radius: 8px;
            border-left: 3px solid #4e73df;
        }

        .detail-item {
            margin-bottom: 8px;
            color: #5a5c69;
            font-size: 0.9rem;
        }

        .lease-info {
            border-left: 3px solid #1cc88a;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #224abe, #1e3a8a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }

        .btn-success {
            background: linear-gradient(45deg, #1cc88a, #17a2b8);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #17a2b8, #138496);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
        }

        .btn-info {
            background: linear-gradient(45deg, #36b9cc, #17a2b8);
            border: none;
        }

        .btn-outline-primary:hover,
        .btn-outline-success:hover,
        .btn-outline-info:hover {
            transform: translateY(-2px);
        }

        /* Filter Buttons */
        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .btn-group .btn:last-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .building-card,
            .unit-card {
                margin-bottom: 20px;
            }

            .section-header {
                padding: 15px;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .info-box {
                margin-bottom: 10px;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .building-card,
        .unit-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #224abe, #1e3a8a);
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            const buildingsBtn = document.getElementById('buildingsView');
            const unitsBtn = document.getElementById('unitsView');
            const allBtn = document.getElementById('allView');

            const buildingsSection = document.getElementById('buildingsSection');

            function resetButtons() {
                [buildingsBtn, unitsBtn, allBtn].forEach(btn => {
                    btn.classList.remove('active', 'btn-primary', 'btn-success', 'btn-info');
                    btn.classList.add('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
                });
            }

            function showSection(section, button, btnClass) {
                resetButtons();
                buildingsSection.style.display = 'none';

                if (section) {
                    section.style.display = 'block';
                } else {
                    buildingsSection.style.display = 'block';
                }

                button.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
                button.classList.add('active', btnClass);
            }

            buildingsBtn?.addEventListener('click', () => {
                showSection(buildingsSection, buildingsBtn, 'btn-primary');
            });

            allBtn?.addEventListener('click', () => {
                showSection(null, allBtn, 'btn-info');
            });

            // Add animation delays for staggered loading effect
            const cards = document.querySelectorAll('.building-card, .unit-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Tooltip initialization if using Bootstrap tooltips
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
@endsection
