<div>
    <div class="form-group">
        @if ($label)
            <label class="form-label fw-bold">{{ $label }}</label>
        @endif

        <div class="searchable-select-container position-relative">
            <!-- حقل البحث -->
            <div class="input-group">
                <input type="text" class="form-control" wire:model.live="search" placeholder="{{ $placeholder }}"
                    autocomplete="off" wire:focus="$set('showDropdown', true)"
                    @blur="setTimeout(() => { $wire.showDropdown = false }, 200)">

                @if ($selectedId)
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" wire:click="clearSelection"
                            title="مسح">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- قائمة نتائج البحث -->
            @if ($showDropdown && $search && !$selectedId)
                <div class="dropdown-menu show w-100" style="max-height: 300px; overflow-y: auto;">
                    @if (count($filteredItems) > 0)
                        <!-- العناصر الموجودة -->
                        @foreach ($filteredItems as $item)
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                wire:click="selectItem({{ $item['id'] }}, '{{ addslashes($item['text']) }}')">
                                <i class="fas fa-check-circle text-muted me-2"></i>
                                <span>{{ $item['text'] }}</span>
                            </button>
                        @endforeach
                    @endif

                    <!-- زر إنشاء عنصر جديد -->
                    @if ($search && !collect($filteredItems)->contains('text', $search))
                        <button type="button" class="dropdown-item d-flex align-items-center border-top text-success"
                            wire:click="createNew">
                            <i class="fas fa-plus-circle me-2"></i>
                            <span>إنشاء جديد: "<strong>{{ $search }}</strong>"</span>
                        </button>
                    @endif

                    <!-- رسالة عدم وجود نتائج -->
                    @if (count($filteredItems) === 0 && !collect($filteredItems)->contains('text', $search))
                        <div class="dropdown-item text-muted">
                            <i class="fas fa-search me-2"></i>
                            <span>لا توجد نتائج</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- مؤشر العنصر المحدد -->
            @if ($selectedId && $selectedText)
                <small class="text-success mt-1 d-block">
                    <i class="fas fa-check-circle"></i>
                    تم اختيار: {{ $selectedText }}
                </small>
            @endif
        </div>
    </div>

    <style>
        .searchable-select-container .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .searchable-select-container .dropdown-item {
            cursor: pointer;
            padding: 0.75rem 1rem;
            transition: background-color 0.2s;
        }

        .searchable-select-container .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</div>
