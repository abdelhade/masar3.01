<div class="container-fluid">
    <div class="row">
        <!-- رسائل النجاح -->
        @if (session()->has('message'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-check-circle"></i>
                    {{ session('message') }}
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- رسائل الخطأ -->
        @if (session()->has('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- رسائل Livewire -->
        <div class="col-12" x-data="{ showMessage: false, message: '', messageType: 'success' }" x-show="showMessage">
            <div class="alert alert-dismissible fade show" 
                 :class="messageType === 'success' ? 'alert-success' : 'alert-danger'"
                 x-show="showMessage" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <i class="fas" :class="messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="message"></span>
                <button type="button" class="btn-close" @click="showMessage = false" aria-label="Close"></button>
            </div>
        </div>

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('show-message', (data) => {
                    const alertDiv = document.querySelector('[x-data*="showMessage"]');
                    if (alertDiv) {
                        const alpine = Alpine.$data(alertDiv);
                        alpine.message = data.message;
                        alpine.messageType = data.type;
                        alpine.showMessage = false;
                        setTimeout(() => {
                            alpine.showMessage = true;
                            setTimeout(() => {
                                alpine.showMessage = false;
                            }, 5000);
                        }, 100);
                    }
                });
            });
        </script>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-hold fw-bold">{{ __('hr.hr_settings') }}</h3>
                        @can('edit HR Settings')
                            <a href="{{ route('hr.settings.edit') }}" class="btn btn-main font-hold fw-bold">
                                <i class="fas fa-edit"></i>
                                {{ __('hr.edit_hr_setting') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- عرض الإعدادات -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white font-hold fw-bold font-14">{{ __('hr.company_max_leave_percentage') }} (%)</th>
                                    <th class="text-white font-hold fw-bold font-14">{{ __('hr.created_at') }}</th>
                                    <th class="text-white font-hold fw-bold font-14">{{ __('hr.updated_at') }}</th>
                                    @can('edit HR Settings')
                                        <th class="text-white font-hold fw-bold font-14">{{ __('hr.actions') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-hold fw-bold font-14">
                                        <span class="badge bg-success fs-6">{{ number_format($setting->company_max_leave_percentage ?? 0, 2) }}%</span>
                                    </td>
                                    <td class="font-hold fw-bold font-14">{{ $setting->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="font-hold fw-bold font-14">{{ $setting->updated_at->format('Y-m-d H:i') }}</td>
                                    @can('edit HR Settings')
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('hr.settings.edit') }}"
                                                    class="btn btn-sm btn-warning font-hold fw-bold font-14"
                                                    title="{{ __('hr.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                    {{ __('hr.edit') }}
                                                </a>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
