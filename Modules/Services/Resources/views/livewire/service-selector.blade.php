<div class="service-selector">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-cogs me-2"></i>
                اختيار الخدمات
            </h5>
        </div>
        
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">البحث في الخدمات</label>
                        <input type="text" 
                               class="form-control" 
                               wire:model.live.debounce.300ms="searchTerm"
                               placeholder="ابحث عن خدمة...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="category">التصنيف</label>
                        <select class="form-control" wire:model.live="selectedCategory">
                            <option value="">جميع التصنيفات</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-secondary d-block w-100" wire:click="clearSelection">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="row">
                @forelse($services as $service)
                    <div class="col-md-4 mb-3">
                        <div class="card service-card h-100 {{ $selectedService && $selectedService->id == $service->id ? 'border-primary' : '' }}"
                             wire:click="selectService({{ $service->id }})"
                             style="cursor: pointer;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $service->name }}</h6>
                                    <span class="badge bg-info">{{ $service->code }}</span>
                                </div>
                                
                                @if($service->description)
                                    <p class="card-text text-muted small">
                                        {{ Str::limit($service->description, 80) }}
                                    </p>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-success">{{ number_format($service->price, 2) }} ر.س</strong>
                                        <br>
                                        <small class="text-muted">60 دقيقة</small>
                                    </div>
                                    
                                    <div class="text-end">
                                        <span class="badge bg-secondary">
                                            {{ match($service->service_type) {
                                                'general' => 'عام',
                                                'consultation' => 'استشارة',
                                                'maintenance' => 'صيانة',
                                                'repair' => 'إصلاح',
                                                'installation' => 'تركيب',
                                                'training' => 'تدريب',
                                                'other' => 'أخرى',
                                                default => $service->service_type
                                            } }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($service->categories->count() > 0)
                                    <div class="mt-2">
                                        @foreach($service->categories->take(2) as $category)
                                            <span class="badge bg-light text-dark me-1">{{ $category->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد خدمات متطابقة مع البحث</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Selected Service Details -->
    @if($showServiceDetails && $selectedService)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    تفاصيل الخدمة المختارة
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearSelection">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5>{{ $selectedService->name }}</h5>
                        <p class="text-muted">{{ $selectedService->description }}</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>كود الخدمة:</strong> {{ $selectedService->code }}<br>
                                <strong>النوع:</strong> 
                                <span class="badge bg-secondary">
                                    {{ match($selectedService->service_type) {
                                        'general' => 'عام',
                                        'consultation' => 'استشارة',
                                        'maintenance' => 'صيانة',
                                        'repair' => 'إصلاح',
                                        'installation' => 'تركيب',
                                        'training' => 'تدريب',
                                        'other' => 'أخرى',
                                        default => $selectedService->service_type
                                    } }}
                                </span><br>
                                <strong>المدة:</strong> 60 دقيقة
                            </div>
                            <div class="col-md-6">
                                <strong>السعر:</strong> 
                                <span class="text-success h5">{{ number_format($selectedService->price, 2) }} ر.س</span><br>
                                <strong>التكلفة:</strong> {{ number_format($selectedService->cost, 2) }} ر.س<br>
                                <strong>خاضع للضريبة:</strong> 
                                <span class="badge bg-{{ $selectedService->is_taxable ? 'success' : 'warning' }}">
                                    {{ $selectedService->is_taxable ? 'نعم' : 'لا' }}
                                </span>
                            </div>
                        </div>
                        
                        @if($selectedService->requirements && count($selectedService->requirements) > 0)
                            <div class="mt-3">
                                <strong>المتطلبات:</strong>
                                <ul class="list-unstyled">
                                    @foreach($selectedService->requirements as $requirement)
                                        <li><i class="fas fa-check text-success me-1"></i>{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if($selectedService->features && count($selectedService->features) > 0)
                            <div class="mt-3">
                                <strong>المميزات:</strong>
                                <ul class="list-unstyled">
                                    @foreach($selectedService->features as $feature)
                                        <li><i class="fas fa-star text-warning me-1"></i>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-4">
                        @if($selectedService->image)
                            <img src="{{ asset('storage/' . $selectedService->image) }}" 
                                 alt="{{ $selectedService->name }}" 
                                 class="img-fluid rounded mb-3">
                        @endif
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" wire:click="addToPOS">
                                <i class="fas fa-plus me-1"></i>
                                إضافة إلى نقاط البيع
                            </button>
                            
                            <a href="{{ route('services.services.show', $selectedService) }}" 
                               class="btn btn-outline-info" target="_blank">
                                <i class="fas fa-eye me-1"></i>
                                عرض التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.service-card {
    transition: all 0.2s ease-in-out;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.service-card.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
</style>
