@php
    declare(strict_types=1);
    
    use App\Models\Department;
    
    /**
     * Get all ancestors (parents chain)
     */
    function getAncestors(Department $department): array
    {
        $ancestors = [];
        $current = $department->parent;
        
        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }
    
    $ancestors = getAncestors($department);
@endphp

<div class="hierarchy-tree" style="direction: rtl;">
    {{-- Display ancestors (parents) --}}
    @if (count($ancestors) > 0)
        <div class="mb-4">
            <h6 class="fw-bold text-info mb-3">
                <i class="fas fa-arrow-up me-2"></i>{{ __('Parents') }}
            </h6>
            <div class="ps-3" style="border-right: 2px dashed #0dcaf0;">
                @foreach ($ancestors as $ancestor)
                    <div class="mb-2">
                        <i class="fas fa-chevron-left text-info me-2"></i>
                        <span class="fw-bold">{{ $ancestor->title }}</span>
                        @if ($ancestor->description)
                            <small class="text-muted">({{ $ancestor->description }})</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Display current department --}}
    <div class="mb-4">
        <h6 class="fw-bold text-primary mb-3">
            <i class="fas fa-building me-2"></i>{{ __('Current Department') }}
        </h6>
        <div class="card border-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-2">
                    <i class="fas fa-sitemap me-2"></i>{{ $department->title }}
                </h5>
                @if ($department->description)
                    <p class="card-text text-muted mb-0">{{ __('Description') }}: {{ $department->description }}</p>
                    <p class="card-text text-muted mb-0">{{ __('Director') }}: {{ $department->director ? $department->director->name : '-' }}</p>
                    <p class="card-text text-muted mb-0">{{ __('Deputy Director') }}: {{ $department->deputyDirector ? $department->deputyDirector->name : '-' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Display children recursively --}}
    @if ($department->children->count() > 0)
        <div class="mb-4">
            <h6 class="fw-bold text-success mb-3">
                <i class="fas fa-arrow-down me-2"></i>{{ __('Children Departments') }}
            </h6>
            <div class="ps-3">
                @foreach ($department->children as $child)
                    @include('hr-management.departments.partials.hierarchy-tree-item', ['department' => $child, 'level' => 0])
                @endforeach
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            {{ __('No child departments') }}
        </div>
    @endif
</div>
