<div>
    <div class="card border-primary mb-4 shadow">
        <div class="card-header">
            <small>حدد كلا الموقعين من الخريطة</small>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- ✅ الموقع الأول (من) --}}
                <div class="col-lg-5">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success bg-opacity-10 border-success">
                            <h6 class="mb-0">
                                <i class="bi bi-geo-fill text-success"></i>
                                الموقع الأول (نقطة البداية)
                            </h6>
                        </div>
                        <div class="card-body">
                            @if ($fromLocation)
                                <div class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-pin-map-fill text-success fs-4 me-2"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold">{{ $fromLocation }}</p>
                                            <small class="text-muted">
                                                <i class="bi bi-compass"></i>
                                                {{ number_format($fromLocationLat, 6) }},
                                                {{ number_format($fromLocationLng, 6) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-map fs-1 mb-3 d-block"></i>
                                    <p>لم يتم تحديد الموقع الأول بعد</p>
                                </div>
                            @endif

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success btn-lg" wire:click="openFromMapModal">
                                    <i class="bi bi-map me-2"></i>
                                    {{ $fromLocation ? 'تغيير الموقع' : 'اختر من الخريطة' }}
                                </button>

                                @if ($fromLocation && $fromLocation !== 'Abu Dhabi, UAE')
                                    <button type="button" class="btn btn-outline-secondary"
                                        wire:click="resetFromLocation">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        إعادة لأبوظبي
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- السهم والمسافة --}}
                <div class="col-lg-2 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="bi bi-arrow-left-right text-primary mb-3" style="font-size: 3rem;"></i>

                        @if ($calculatedDistance)
                            <div class="mb-2">
                                <div class="badge bg-success fs-5 px-3 py-2">
                                    <i class="bi bi-rulers me-1"></i>
                                    {{ $calculatedDistance }} كم
                                </div>
                            </div>

                            @if ($calculatedDuration)
                                <div class="badge bg-info fs-6 px-3 py-2">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $calculatedDuration }}
                                </div>
                            @endif
                        @else
                            <small class="text-muted d-block">
                                حدد الموقعين<br>لحساب المسافة
                            </small>
                        @endif
                    </div>
                </div>

                {{-- ✅ الموقع الثاني (إلى) --}}
                <div class="col-lg-5">
                    <div class="card h-100 border-danger">
                        <div class="card-header bg-danger bg-opacity-10 border-danger">
                            <h6 class="mb-0">
                                <i class="bi bi-geo text-danger"></i>
                                الموقع الثاني (نقطة النهاية)
                            </h6>
                        </div>
                        <div class="card-body">
                            @if ($toLocation)
                                <div class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-pin-map text-danger fs-4 me-2"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold">{{ $toLocation }}</p>
                                            <small class="text-muted">
                                                <i class="bi bi-compass"></i>
                                                {{ number_format($toLocationLat, 6) }},
                                                {{ number_format($toLocationLng, 6) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-map fs-1 mb-3 d-block"></i>
                                    <p>لم يتم تحديد الموقع الثاني بعد</p>
                                </div>
                            @endif

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger btn-lg" wire:click="openToMapModal">
                                    <i class="bi bi-map me-2"></i>
                                    {{ $toLocation ? 'تغيير الموقع' : 'اختر من الخريطة' }}
                                </button>

                                @if ($toLocation)
                                    <button type="button" class="btn btn-outline-secondary"
                                        wire:click="resetToLocation">
                                        <i class="bi bi-x-lg me-1"></i>
                                        إعادة تعيين
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- زر حساب المسافة --}}
            @if ($fromLocationLat && $toLocationLat)
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-primary btn-lg px-5" wire:click="calculateDistance"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="calculateDistance">
                                <i class="bi bi-calculator me-2"></i>
                                احسب المسافة
                            </span>
                            <span wire:loading wire:target="calculateDistance">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                جاري الحساب...
                            </span>
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-lg ms-2" wire:click="resetAll">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            إعادة تعيين الكل
                        </button>
                    </div>
                </div>
            @endif

            {{-- نتائج الحساب التفصيلية --}}
            @if ($calculatedDistance)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-success border-2 border-success shadow-sm" role="alert">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                </div>
                                <div class="col-md-11">
                                    <h5 class="alert-heading mb-3">
                                        <i class="bi bi-graph-up-arrow me-2"></i>
                                        نتائج حساب المسافة
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-white rounded">
                                                <small class="text-muted d-block mb-1">من</small>
                                                <strong class="text-success">
                                                    <i class="bi bi-geo-fill me-1"></i>
                                                    {{ Str::limit($fromLocation, 30) }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-white rounded">
                                                <small class="text-muted d-block mb-1">إلى</small>
                                                <strong class="text-danger">
                                                    <i class="bi bi-geo me-1"></i>
                                                    {{ Str::limit($toLocation, 30) }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-3 bg-white rounded text-center">
                                                <small class="text-muted d-block mb-1">المسافة</small>
                                                <strong class="text-primary fs-5">
                                                    {{ $calculatedDistance }} كم
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-3 bg-white rounded text-center">
                                                <small class="text-muted d-block mb-1">الوقت</small>
                                                <strong class="text-info">
                                                    {{ $calculatedDuration ?? 'N/A' }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ✅ Map Picker Modal (موحد للموقعين) --}}
    @if ($showMapModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7);">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header {{ $mapModalType === 'from' ? 'bg-success' : 'bg-danger' }} text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-map me-2"></i>
                            <span id="mapModalTitle">اختر الموقع من الخريطة</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeMapModal"></button>
                    </div>
                    <div class="modal-body p-0">
                        {{-- تعليمات الاستخدام --}}
                        <div class="alert alert-info mb-0 rounded-0 border-0">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <strong>كيفية الاستخدام:</strong>
                                    انقر على أي نقطة في الخريطة أو اسحب العلامة لتحديد الموقع المطلوب
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-sm btn-primary" id="useMyLocationBtn">
                                        <i class="bi bi-crosshair me-1"></i>
                                        استخدم موقعي الحالي
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- الخريطة --}}
                        <div id="mapPicker" style="height: 600px; width: 100%;"></div>

                        {{-- معلومات الموقع المختار --}}
                        <div class="p-4 bg-light border-top">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-2">
                                        <i class="bi bi-pin-map-fill me-1"></i>
                                        الموقع المختار:
                                    </h6>
                                    <p class="mb-1 fw-bold" id="selectedAddress">انقر على الخريطة لاختيار موقع</p>
                                    <small class="text-muted" id="selectedCoords"></small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-success btn-lg px-5"
                                        id="confirmLocationBtn" disabled>
                                        <i class="bi bi-check-circle me-2"></i>
                                        تأكيد الموقع
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Google Maps Script --}}
@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&language=ar">
    </script>
    <script>
        let map;
        let marker;
        let geocoder;
        let selectedLocation = null;
        let currentMapType = '';

        // ✅ Livewire Events
        document.addEventListener('livewire:init', () => {
            Livewire.on('initMapPicker', (data) => {
                setTimeout(() => {
                    const params = data[0];
                    currentMapType = params.type;
                    document.getElementById('mapModalTitle').textContent = params.title;
                    initMapPicker(params.lat, params.lng, params.type);
                }, 300);
            });
        });

        // ✅ تهيئة الخريطة
        function initMapPicker(initialLat, initialLng, type) {
            const mapElement = document.getElementById('mapPicker');
            if (!mapElement) {
                console.error('❌ Map element not found');
                return;
            }

            geocoder = new google.maps.Geocoder();
            selectedLocation = null;

            // إنشاء الخريطة
            map = new google.maps.Map(mapElement, {
                center: {
                    lat: initialLat,
                    lng: initialLng
                },
                zoom: 12,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                    position: google.maps.ControlPosition.TOP_RIGHT,
                },
                zoomControl: true,
                zoomControlOptions: {
                    position: google.maps.ControlPosition.LEFT_CENTER
                },
                streetViewControl: true,
                fullscreenControl: true,
                gestureHandling: 'greedy'
            });

            // إضافة Marker
            const markerColor = type === 'from' ? '#28a745' : '#dc3545'; // أخضر للأول، أحمر للثاني

            marker = new google.maps.Marker({
                position: {
                    lat: initialLat,
                    lng: initialLng
                },
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'اسحب لتغيير الموقع',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: markerColor,
                    fillOpacity: 0.8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                }
            });

            // ✅ عند النقر على الخريطة
            map.addListener('click', function(event) {
                placeMarker(event.latLng);
            });

            // ✅ عند سحب الـ Marker
            marker.addListener('dragend', function(event) {
                placeMarker(event.latLng);
            });

            // ✅ زر استخدام الموقع الحالي
            document.getElementById('useMyLocationBtn').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            map.setCenter(pos);
                            placeMarker(new google.maps.LatLng(pos.lat, pos.lng));
                        },
                        () => {
                            alert('⚠️ فشل الحصول على موقعك الحالي');
                        }
                    );
                } else {
                    alert('❌ المتصفح لا يدعم Geolocation');
                }
            });

            // ✅ زر التأكيد
            document.getElementById('confirmLocationBtn').addEventListener('click', function() {
                if (selectedLocation) {
                    console.log('Dispatching locationPicked with:', selectedLocation); // تصحيح
                    Livewire.dispatch('locationPicked', {
                        type: selectedLocation.type,
                        address: selectedLocation.address,
                        lat: selectedLocation.lat,
                        lng: selectedLocation.lng
                    });
                } else {
                    console.error('No location selected!'); // تصحيح
                    alert('يرجى اختيار موقع أولاً');
                }
            });

            // Set initial location
            placeMarker(new google.maps.LatLng(initialLat, initialLng));

            console.log('✅ Map Picker initialized for:', type);
        }

        // ✅ وضع Marker في موقع جديد
        function placeMarker(location) {
            marker.setPosition(location);
            map.panTo(location);

            const lat = location.lat();
            const lng = location.lng();

            // عرض الإحداثيات فوراً
            document.getElementById('selectedCoords').textContent =
                `📍 الإحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

            // Reverse Geocoding للحصول على العنوان
            geocoder.geocode({
                location: location
            }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const address = results[0].formatted_address;

                    selectedLocation = {
                        type: currentMapType,
                        address: address,
                        lat: lat,
                        lng: lng
                    };

                    document.getElementById('selectedAddress').textContent = address;
                    document.getElementById('confirmLocationBtn').disabled = false;

                    console.log('✅ Location selected:', selectedLocation);
                } else {
                    // في حالة فشل Geocoding، استخدم الإحداثيات فقط
                    selectedLocation = {
                        type: currentMapType,
                        address: `Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`,
                        lat: lat,
                        lng: lng
                    };

                    document.getElementById('selectedAddress').textContent =
                        `الموقع المحدد (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
                    document.getElementById('confirmLocationBtn').disabled = false;

                    console.warn('⚠️ Geocoder failed:', status);
                }
            });
        }

        // ✅ تنظيف عند إغلاق الـ Modal
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                if (!component.showMapModal && map) {
                    map = null;
                    marker = null;
                    selectedLocation = null;
                }
            });
        });
    </script>
@endpush

{{-- Styles --}}
@push('styles')
    <style>
        /* Modal Styling */
        .modal.show {
            display: block !important;
        }

        /* Map Styling */
        #mapPicker {
            border-radius: 0;
        }

        /* Custom Marker Colors */
        .marker-from {
            color: #28a745;
        }

        .marker-to {
            color: #dc3545;
        }

        /* Cards Styling */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Gradient Header */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }

        /* Success/Danger Opacity Backgrounds */
        .bg-success.bg-opacity-10 {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Badge Animations */
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .badge {
            animation: scaleIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Alert Animations */
        @keyframes slideDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert {
            animation: slideDown 0.5s ease-out;
        }

        /* Loading Animation */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        [wire\:loading] .spinner-border {
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Button Hover Effects */
        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Custom Scrollbar for Modal */
        .modal-body {
            scrollbar-width: thin;
            scrollbar-color: #0d6efd #f8f9fa;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #0d6efd;
            border-radius: 4px;
        }

        /* Arrow Animation */
        @keyframes arrowBounce {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(-10px);
            }
        }

        .bi-arrow-left-right {
            animation: arrowBounce 2s ease-in-out infinite;
        }

        /* Card Border Glow Effect */
        .border-success {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
        }

        .border-danger {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1);
        }

        /* Info Alert in Modal */
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-color: #bee5eb;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .bi-arrow-left-right {
                transform: rotate(90deg);
                font-size: 2rem !important;
            }

            .modal-xl {
                margin: 0.5rem;
            }

            #mapPicker {
                height: 400px !important;
            }
        }
    </style>
@endpush
