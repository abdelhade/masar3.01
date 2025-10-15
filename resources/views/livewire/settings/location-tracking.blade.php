<?php

use Livewire\Volt\Component;
use App\Models\UserLocationTracking;

new class extends Component {
    public $trackingEnabled = false;
    public $lastLocation = null;
    public $trackingStatus = 'inactive';
    public $sessionId = null;

    public function mount()
    {
        $this->loadTrackingStatus();
    }

    public function loadTrackingStatus()
    {
        // تحقق من حالة التتبع من LocalStorage (سيتم تحديثها عبر JavaScript)
        $this->trackingEnabled = false; // سيتم تحديثها عبر JavaScript
        $this->trackingStatus = 'inactive';
        
        // جلب آخر موقع
        $lastTracking = UserLocationTracking::where('user_id', auth()->id())
            ->latest('tracked_at')
            ->first();
            
        if ($lastTracking) {
            $this->lastLocation = [
                'latitude' => $lastTracking->latitude,
                'longitude' => $lastTracking->longitude,
                'tracked_at' => $lastTracking->tracked_at->format('Y-m-d H:i:s'),
                'accuracy' => $lastTracking->accuracy
            ];
        }
    }

    public function toggleTracking()
    {
        $this->trackingEnabled = !$this->trackingEnabled;
        
        if ($this->trackingEnabled) {
            $this->trackingStatus = 'active';
            $this->dispatch('start-location-tracking');
        } else {
            $this->trackingStatus = 'inactive';
            $this->dispatch('stop-location-tracking');
        }
    }

    public function getLocationHistory()
    {
        return UserLocationTracking::where('user_id', auth()->id())
            ->latest('tracked_at')
            ->limit(10)
            ->get();
    }
}; ?>

<div dir="rtl" style="font-family: 'Cairo', sans-serif;">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 font-family-cairo fw-bold">{{ __('إعدادات تتبع الموقع') }}</h5>
                </div>
                <div class="card-body">
                    <!-- حالة التتبع -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h6 class="font-family-cairo fw-bold">{{ __('حالة التتبع') }}</h6>
                                    <div class="mt-2">
                                        @if($trackingStatus === 'active')
                                            <span class="badge bg-success fs-6">
                                                <i class="las la-map-marker-alt"></i> {{ __('نشط') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary fs-6">
                                                <i class="las la-map-marker-alt"></i> {{ __('غير نشط') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h6 class="font-family-cairo fw-bold">{{ __('مدة التتبع') }}</h6>
                                    <p class="mb-0 text-muted">{{ __('10 ساعات') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- آخر موقع -->
                    @if($lastLocation)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="font-family-cairo fw-bold">{{ __('آخر موقع مسجل') }}</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('الإحداثيات:') }}</small>
                                                <p class="mb-1">{{ $lastLocation['latitude'] }}, {{ $lastLocation['longitude'] }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">{{ __('التاريخ:') }}</small>
                                                <p class="mb-1">{{ $lastLocation['tracked_at'] }}</p>
                                            </div>
                                        </div>
                                        @if($lastLocation['accuracy'])
                                            <small class="text-muted">{{ __('الدقة:') }} {{ $lastLocation['accuracy'] }} متر</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- أزرار التحكم -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <button class="btn btn-primary font-family-cairo fw-bold me-2" 
                                    wire:click="toggleTracking">
                                @if($trackingStatus === 'active')
                                    <i class="las la-stop"></i> {{ __('إيقاف التتبع') }}
                                @else
                                    <i class="las la-play"></i> {{ __('بدء التتبع') }}
                                @endif
                            </button>
                            
                            <button class="btn btn-outline-info font-family-cairo fw-bold" 
                                    onclick="refreshLocation()">
                                <i class="las la-sync"></i> {{ __('تحديث الموقع') }}
                            </button>
                        </div>
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="font-family-cairo fw-bold">{{ __('معلومات مهمة:') }}</h6>
                                <ul class="mb-0 font-family-cairo">
                                    <li>{{ __('التتبع يعمل كل 30 دقيقة لمدة 10 ساعات') }}</li>
                                    <li>{{ __('يعمل حتى لو كان المتصفح في الخلفية') }}</li>
                                    <li>{{ __('يتم حفظ الموقع تلقائياً عند تسجيل البصمة') }}</li>
                                    <li>{{ __('يتم حذف السجلات القديمة تلقائياً بعد 30 يوم') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshLocation() {
    if (window.locationTracker) {
        window.locationTracker.captureLocationForAttendance('manual')
            .then(location => {
                console.log('Location refreshed:', location);
                // تحديث الصفحة لعرض الموقع الجديد
                @this.call('loadTrackingStatus');
            })
            .catch(error => {
                console.error('Error refreshing location:', error);
            });
    }
}

// الاستماع لأحداث التتبع
document.addEventListener('livewire:init', () => {
    Livewire.on('start-location-tracking', () => {
        if (window.locationTracker) {
            window.locationTracker.startTracking();
        }
    });
    
    Livewire.on('stop-location-tracking', () => {
        if (window.locationTracker) {
            window.locationTracker.stopTracking();
        }
    });
});
</script>
@endpush
