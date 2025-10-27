@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection

@section('content')
    <div class="container-fluid p-3">
        <!-- Compact Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="settings-icon-wrapper me-3">
                    <i class="bi bi-sliders2"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">إعدادات النظام</h5>
                    <small class="text-muted">إدارة التطبيق</small>
                </div>
            </div>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="settingSearch" class="form-control border-0 bg-light" placeholder="بحث سريع...">
            </div>
        </div>

        <form action="{{ route('mysettings.update') }}" method="POST">
            @csrf
            @method('POST')

            <!-- Modern Tabs Navigation -->
            <div class="settings-tabs mb-3">
                <div class="tabs-wrapper">
                    @foreach ($cateries as $index => $category)
                        @if ($category->publicSettings->count())
                            <button class="tab-button {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $category->id }}"
                                data-bs-toggle="pill" data-bs-target="#content-{{ $category->id }}" type="button"
                                role="tab">
                                <i class="bi bi-folder2 me-2"></i>
                                <span>{{ $category->name }}</span>
                                <span
                                    class="badge bg-white text-primary ms-2">{{ $category->publicSettings->count() }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content bg-white rounded-3 shadow-sm p-3" id="categoryTabContent">
                @foreach ($cateries as $index => $category)
                    @if ($category->publicSettings->count())
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $category->id }}"
                            role="tabpanel">
                            @php $settings = $category->publicSettings->values(); @endphp

                            <div class="settings-grid">
                                @foreach ($settings as $setting)
                                    <div class="setting-item">
                                        <div class="setting-content">
                                            <div class="setting-label">
                                                <i class="bi bi-dot text-primary"></i>
                                                <span class="fw-semibold">{{ $setting->label }}</span>
                                            </div>
                                            <div class="setting-input">
                                                @if ($setting->input_type === 'boolean')
                                                    <input type="hidden" name="settings[{{ $setting->key }}]"
                                                        value="0">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            name="settings[{{ $setting->key }}]" value="1"
                                                            id="switch-{{ $setting->key }}"
                                                            {{ $setting->value ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    <input
                                                        type="{{ $setting->input_type === 'number' ? 'number' : $setting->input_type }}"
                                                        name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                                        class="form-control form-control-sm">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Compact Save Button -->
            <div class="d-flex justify-content-end mt-3 gap-2">
                <button type="submit" class="btn btn-bg btn-primary btn-lg"
                    style="padding: 15px 40px; font-size: 18px; border-radius: 10px;">
                    <i class="bi bi-check-lg me-1"></i>حفظ التغييرات
                </button>
            </div>
        </form>
    </div>

    <style>
        /* Header Icon */
        .settings-icon-wrapper {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        /* Modern Tabs */
        .settings-tabs {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 12px;
        }

        .tabs-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tab-button {
            flex: 0 0 calc(12.5% - 6px);
            min-width: 120px;
            color: #6c757d;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 500;
            font-size: 0.85rem;
            border: none;
            background: transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .tab-button:hover {
            background: white;
            color: #6366f1;
        }

        .tab-button.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .tab-button .badge {
            font-size: 0.7rem;
            padding: 2px 6px;
        }

        .tab-button.active .badge {
            background: rgba(255, 255, 255, 0.3) !important;
            color: white !important;
        }

        /* Compact Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
            padding: 8px 0;
        }

        .setting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            gap: 12px;
        }

        .setting-item:hover {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
        }

        .setting-label {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            font-size: 0.9rem;
            min-width: 0;
            overflow: hidden;
        }

        .setting-label i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .setting-label .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .setting-input {
            min-width: 120px;
            flex-shrink: 0;
            display: flex;
            justify-content: flex-end;
        }

        .setting-input .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .setting-input .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-switch .form-check-input {
            width: 45px;
            height: 24px;
            cursor: pointer;
            border: 2px solid #dee2e6;
        }

        .form-switch .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            font-weight: 500;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-light {
            border: 1px solid #dee2e6;
            font-weight: 500;
        }

        /* Search Input */
        #settingSearch {
            font-size: 0.9rem;
        }

        #settingSearch:focus {
            box-shadow: none;
            background: white !important;
        }

        /* Tab Content */
        .tab-content {
            border: 1px solid #e9ecef;
            min-height: 400px;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .tab-button {
                flex: 0 0 calc(16.666% - 6px);
            }
        }

        @media (max-width: 992px) {
            .tab-button {
                flex: 0 0 calc(25% - 6px);
            }
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .tab-button {
                flex: 0 0 calc(50% - 6px);
                font-size: 0.8rem;
                padding: 8px 12px;
            }

            .tab-button span:not(.badge) {
                display: none;
            }

            .tab-button i {
                margin: 0 !important;
            }
        }
    </style>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });

                // Show target tab pane
                const targetId = this.getAttribute('data-bs-target');
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });

        // Search Functionality
        document.getElementById("settingSearch").addEventListener("input", function() {
            let value = this.value.toLowerCase();

            document.querySelectorAll(".setting-item").forEach(item => {
                let text = item.innerText.toLowerCase();
                if (text.includes(value)) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        });

        // Reset button functionality
        document.querySelector('.btn-light').addEventListener('click', function() {
            if (confirm('هل تريد إعادة تعيين جميع الإعدادات؟')) {
                location.reload();
            }
        });
    </script>
@endsection
