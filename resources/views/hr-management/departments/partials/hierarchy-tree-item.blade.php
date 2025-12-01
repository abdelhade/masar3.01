@php
    declare(strict_types=1);
    
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    $margin = $level * 25;
@endphp

<div class="hierarchy-item mb-2" style="margin-right: {{ $margin }}px;">
    <div class="d-flex align-items-center">
        <i class="fas {{ $level === 0 ? 'fa-folder-open' : 'fa-folder' }} text-success me-2"></i>
        <span class="fw-bold">{{ $department->title }}</span>
        @if ($department->description)
            <small class="text-muted ms-2">({{ $department->description }})</small>
        @endif
    </div>
    
    @if ($department->children->count() > 0)
        <div class="mt-2" style="border-right: 2px solid #28a745; margin-right: 15px; padding-right: 15px;">
            @foreach ($department->children as $child)
                @include('hr-management.departments.partials.hierarchy-tree-item', ['department' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>

