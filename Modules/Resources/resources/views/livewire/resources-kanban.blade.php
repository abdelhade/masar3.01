<div>
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="بحث...">
        </div>
        <div class="col-md-6">
            <select wire:model.live="categoryFilter" class="form-control">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_ar }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        @foreach($statuses as $status)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-{{ $status->color }} text-white">
                        <h5 class="mb-0">
                            <i class="{{ $status->icon }}"></i>
                            {{ $status->name_ar }}
                            <span class="badge bg-light text-dark">{{ $resourcesByStatus[$status->id]->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body" style="min-height: 400px;">
                        @foreach($resourcesByStatus[$status->id] as $resource)
                            <div class="card mb-2">
                                <div class="card-body p-2">
                                    <h6 class="mb-1">{{ $resource->code }}</h6>
                                    <p class="mb-1">{{ $resource->name }}</p>
                                    <small class="text-muted">{{ $resource->category->name_ar }} - {{ $resource->type->name_ar }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

