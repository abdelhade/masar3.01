<div>
    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-3">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="بحث...">
        </div>
        <div class="col-md-2">
            <select wire:model.live="categoryFilter" class="form-control">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select wire:model.live="typeFilter" class="form-control" @if(!$categoryFilter) disabled @endif>
                <option value="">كل الأنواع</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select wire:model.live="statusFilter" class="form-control">
                <option value="">كل الحالات</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button wire:click="resetFilters" class="btn btn-secondary">
                <i class="fas fa-redo"></i> إعادة تعيين
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>التصنيف</th>
                    <th>النوع</th>
                    <th>الحالة</th>
                    <th>الفرع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resources as $resource)
                    <tr>
                        <td>{{ $resource->code }}</td>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->category->name_ar }}</td>
                        <td>{{ $resource->type->name_ar }}</td>
                        <td>
                            <span class="badge bg-{{ $resource->status->color }}">
                                {{ $resource->status->name_ar }}
                            </span>
                        </td>
                        <td>{{ $resource->branch->name ?? '-' }}</td>
                        <td>
                            @can('view MyResources')
                            <a href="{{ route('myresources.show', $resource) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                            @can('edit MyResources')
                            <a href="{{ route('myresources.edit', $resource) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete MyResources')
                            <button wire:click="deleteResource({{ $resource->id }})" 
                                    wire:confirm="هل أنت متأكد من حذف هذا المورد؟"
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد موارد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $resources->links() }}
    </div>
</div>

