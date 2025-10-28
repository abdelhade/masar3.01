<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Price;
use App\Models\Note;
use App\Models\NoteDetails;
use App\Support\ItemDataTransformer;
use App\Models\AccHead;
use App\Models\OperationItems;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use App\Services\ItemsQueryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

?>

<div>
    <?php
        include_once app_path('Helpers/FormatHelper.php');
    ?>
    
    <style>
        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
                <div class="alert alert-success font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }"
                    x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php if(session()->has('error')): ?>
                <div class="alert alert-danger font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }"
                    x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <div class="card">
                
                <div class="text-center bg-dark text-white py-3">
                    <h5 class="card-title font-family-cairo fw-bold font-20 text-white">
                        <?php echo e(__('قائمه الأصناف مع الأرصده')); ?>

                    </h5>
                </div>
                

                
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('إضافة الأصناف')): ?>
                            <a href="<?php echo e(route('items.create')); ?>"
                                class="btn btn-outline-primary btn-lg font-family-cairo fw-bold mt-4 d-flex justify-content-center align-items-center text-center"
                                style="min-height: 50px;">
                                <i class="fas fa-plus me-2"></i>
                                <span class="w-100 text-center"><?php echo e(__('إضافه صنف')); ?></span>
                            </a>
                        <?php endif; ?>

                        
                        <div class = "mt-4">
                        <a href="<?php echo e(route('items.print', [
                            'search' => $search,
                            'warehouse' => $selectedWarehouse,
                            'group' => $selectedGroup,
                            'category' => $selectedCategory,
                            'priceType' => $selectedPriceType
                        ])); ?>" target="_blank" class="print-btn font-family-cairo fw-bold" style="text-decoration: none;">
                                <i class="fas fa-print"></i>
                                طباعة القائمة
                            </a>
                        </div>

                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-outline-info btn-lg font-family-cairo fw-bold" 
                                    data-bs-toggle="modal" data-bs-target="#columnVisibilityModal"
                                    style="min-height: 50px;">
                                <i class="fas fa-columns me-2"></i>
                                خيارات العرض
                            </button>
                        </div>

                        
                        <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-end gap-2"
                            style="min-width: 300px;" 
                            x-data="filtersComponent()"
                            x-init="
                                searchValue = <?php echo \Illuminate\Support\Js::from($this->search)->toHtml() ?>;
                                warehouseValue = <?php echo \Illuminate\Support\Js::from($this->selectedWarehouse)->toHtml() ?>;
                                groupValue = <?php echo \Illuminate\Support\Js::from($this->selectedGroup)->toHtml() ?>;
                                categoryValue = <?php echo \Illuminate\Support\Js::from($this->selectedCategory)->toHtml() ?>;
                            ">
                            
                            <div class="d-flex align-items-end mt-4">
                                <button type="button" @click="clearFilters()" style="min-height: 50px;"
                                    class="btn btn-outline-secondary btn-lg font-family-cairo fw-bold"
                                    wire:loading.attr="disabled" wire:target="clearFilters">
                                    <span wire:loading.remove wire:target="clearFilters">
                                    <i class="fas fa-times me-1"></i>
                                    مسح الفلاتر
                                    </span>
                                    <span wire:loading wire:target="clearFilters">
                                        <div class="spinner-border spinner-border-sm me-1" role="status">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                        جاري التحميل...
                                    </span>
                                </button>
                            </div>
                            
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">البحث:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search" wire:loading.remove wire:target="search"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="search">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                    <input type="text" x-model="searchValue" @input="updateSearch()"
                                        class="form-control font-family-cairo"
                                        placeholder="بحث بالاسم, الكود, الباركود..."
                                        wire:loading.attr="disabled" wire:target="search">
                                </div>
                            </div>

                            
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المخزن:</label>
                                <div class="input-group">
                                    <select x-model="warehouseValue" @change="updateWarehouse()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedWarehouse">
                                    <option value="">كل المخازن</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->aname); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-warehouse" wire:loading.remove wire:target="selectedWarehouse"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedWarehouse">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المجموعة:</label>
                                <div class="input-group">
                                    <select x-model="groupValue" @change="updateGroup()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedGroup">
                                    <option value="">كل المجموعات</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-layer-group" wire:loading.remove wire:target="selectedGroup"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedGroup">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">التصنيف:</label>
                                <div class="input-group">
                                    <select x-model="categoryValue" @change="updateCategory()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedCategory">
                                    <option value="">كل التصنيفات</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-tags" wire:loading.remove wire:target="selectedCategory"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedCategory">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label font-family-cairo fw-bold mb-0">عرض:</label>
                            <div class="input-group" style="width: auto;">
                                <select wire:model.live="perPage" class="form-select form-select-sm font-family-cairo fw-bold"
                                    wire:loading.attr="disabled" wire:target="perPage">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                                <span class="input-group-text">
                                    <i class="fas fa-list" wire:loading.remove wire:target="perPage"></i>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="perPage">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                </span>
                            </div>
                            <span class="font-family-cairo fw-bold">سجل</span>
                        </div>
                        <div class="font-family-cairo fw-bold text-muted">
                            <i class="fas fa-list me-1"></i>
                            إجمالي النتائج: <span class="text-primary"><?php echo e($this->items->total()); ?></span>
                        </div>
                    </div>
                    
                    
                    <!--[if BLOCK]><![endif]--><?php if($search || $selectedWarehouse || $selectedGroup || $selectedCategory): ?>
                        <div class="alert alert-info mb-3" 
                             x-data="{ show: true }" 
                             x-show="show"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="font-family-cairo fw-bold">
                                    <i class="fas fa-filter me-2"></i>
                                    الفلاتر النشطة:
                                    <!--[if BLOCK]><![endif]--><?php if($search): ?>
                                        <span class="badge bg-primary me-1">البحث: <?php echo e($search); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
                                        <?php $warehouse = $warehouses->firstWhere('id', $selectedWarehouse); ?>
                                        <span class="badge bg-success me-1">المخزن:
                                            <?php echo e($warehouse ? $warehouse->aname : 'غير محدد'); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($selectedGroup): ?>
                                        <span class="badge bg-warning me-1">المجموعة:
                                            <?php echo e($groups[$selectedGroup] ?? 'غير محدد'); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($selectedCategory): ?>
                                        <span class="badge bg-info me-1">التصنيف:
                                            <?php echo e($categories[$selectedCategory] ?? 'غير محدد'); ?></span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <button type="button" class="btn-close" @click="show = false"></button>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="table-responsive" style="overflow-x: auto; max-height: 70vh; overflow-y: auto;">
                        
                        <table class="table table-striped mb-0 table-hover"
                            style="direction: rtl; font-family: 'Cairo', sans-serif;">
                            <style>
                                /* تخصيص لون الهوفر للصفوف */
                                .table-hover tbody tr:hover {
                                    background-color: #ffc107 !important;
                                    /* لون warning */
                                }
                                
                                /* Fixed header styles */
                                .table-responsive {
                                    position: relative;
                                }
                                
                                .table thead th {
                                    position: sticky;
                                    top: 0;
                                    background-color: #f8f9fa !important;
                                    z-index: 10;
                                    border-bottom: 2px solid #dee2e6;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                }
                                
                                /* Ensure proper stacking context */
                                .table-responsive {
                                    z-index: 1;
                                }
                            </style>
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo text-center fw-bold">#</th>
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['code']): ?>
                                        <th class="font-family-cairo text-center fw-bold">الكود</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['name']): ?>
                                        <th class="font-family-cairo text-center fw-bold">الاسم</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['units']): ?>
                                        <th class="font-family-cairo text-center fw-bold" style="min-width: 130px;">الوحدات</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity']): ?>
                                        <th class="font-family-cairo text-center fw-bold" style="min-width: 100px;">الكميه</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['average_cost']): ?>
                                        <th class="font-family-cairo text-center fw-bold">متوسط التكلفه</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity_average_cost']): ?>
                                        <th class="font-family-cairo text-center fw-bold">تكلفه المتوسطه للكميه</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['last_cost']): ?>
                                        <th class="font-family-cairo text-center fw-bold">التكلفه الاخيره</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity_cost']): ?>
                                        <th class="font-family-cairo text-center fw-bold">تكلفه الكميه</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->priceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceId => $priceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <!--[if BLOCK]><![endif]--><?php if(isset($visiblePrices[$priceId]) && $visiblePrices[$priceId]): ?>
                                            <th class="font-family-cairo text-center fw-bold"><?php echo e($priceName); ?></th>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if($visibleColumns['barcode']): ?>
                                        <th class="font-family-cairo text-center fw-bold">الباركود</th>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->noteTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noteId => $noteName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <!--[if BLOCK]><![endif]--><?php if(isset($visibleNotes[$noteId]) && $visibleNotes[$noteId]): ?>
                                            <th class="font-family-cairo text-center fw-bold"><?php echo e($noteName); ?></th>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['تعديل الأصناف', 'حذف الأصناف'])): ?>
                                        <!--[if BLOCK]><![endif]--><?php if($visibleColumns['actions']): ?>
                                            <th class="font-family-cairo fw-bold">العمليات</th>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        // Data already prepared in getItemsProperty()
                                        $itemData = $this->displayItemData[$item->id] ?? [];
                                        $selectedUnitId = $this->selectedUnit[$item->id] ?? null;
                                    ?>
                                    <!--[if BLOCK]><![endif]--><?php if(!empty($itemData)): ?>
                                         <tr wire:key="item-<?php echo e($item->id); ?>-<?php echo e($selectedUnitId ?? 'no-unit'); ?>" 
                                             x-data="itemRow(<?php echo e(json_encode($itemData)); ?>, <?php echo e($selectedUnitId); ?>)"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 transform scale-100"
                                             x-transition:leave-end="opacity-0 transform scale-95">
                                            <td class="font-family-cairo text-center fw-bold"><?php echo e($loop->iteration); ?></td>
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['code']): ?>
                                                <td class="font-family-cairo text-center fw-bold" x-text="itemData.code"></td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['name']): ?>
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="itemData.name"></span>
                                                    <a href="<?php echo e(route('item-movement', ['itemId' => $item->id])); ?>">
                                                        <i class="las la-eye fa-lg text-primary" title="عرض حركات الصنف"></i>
                                                    </a>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['units']): ?>
                                                <td class="font-family-cairo text-center fw-bold">
                                                     <template x-if="Object.keys(itemData.units).length > 0">
                                                         <div>
                                                            <select class="form-select font-family-cairo fw-bold font-14"
                                                                 x-model="selectedUnitId"
                                                                style="min-width: 105px;">
                                                                 <template x-for="[unitId, unit] in Object.entries(itemData.units)" :key="unitId">
                                                                     <option :value="unitId" x-text="unit.name + ' [' + formatNumber(unit.u_val) + ']'"></option>
                                                                 </template>
                                                            </select>
                                                        </div>
                                                     </template>
                                                    <template x-if="Object.keys(itemData.units).length === 0">
                                                        <span class="font-family-cairo fw-bold font-14">لا يوجد وحدات</span>
                                                    </template>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity']): ?>
                                                <td class="text-center fw-bold">
                                                    <span x-text="formattedQuantity.integer"></span>
                                                    <template x-if="formattedQuantity.remainder > 0 && formattedQuantity.unitName !== formattedQuantity.smallerUnitName">
                                                        <span x-text="'[' + formattedQuantity.remainder + ' ' + formattedQuantity.smallerUnitName + ']'"></span>
                                                    </template>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['average_cost']): ?>
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="formatCurrency(unitAverageCost)"></span>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity_average_cost']): ?>
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="formatCurrency(quantityAverageCost)"></span>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['last_cost']): ?>
                                                <td class="text-center fw-bold">
                                                    <span x-text="formatCurrency(unitCostPrice)"></span>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['quantity_cost']): ?>
                                                <td class="text-center fw-bold">
                                                    <span x-text="formatCurrency(quantityCost)"></span>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                            
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->priceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceTypeId => $priceTypeName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <!--[if BLOCK]><![endif]--><?php if(isset($visiblePrices[$priceTypeId]) && $visiblePrices[$priceTypeId]): ?>
                                                    <td class="font-family-cairo text-center fw-bold">
                                                        <span x-text="getPriceForType(<?php echo e($priceTypeId); ?>)"></span>
                                                    </td>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                                            <!--[if BLOCK]><![endif]--><?php if($visibleColumns['barcode']): ?>
                                                <td class="font-family-cairo fw-bold text-center">
                                                    <template x-if="currentBarcodes.length > 0">
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            style="min-width: 100px;">
                                                            <template x-for="barcode in currentBarcodes" :key="barcode.id">
                                                                <option :value="barcode.barcode" x-text="barcode.barcode"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="currentBarcodes.length === 0">
                                                        <span class="font-family-cairo fw-bold font-14">لا يوجد</span>
                                                    </template>
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                            
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->noteTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noteTypeId => $noteTypeName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <!--[if BLOCK]><![endif]--><?php if(isset($visibleNotes[$noteTypeId]) && $visibleNotes[$noteTypeId]): ?>
                                                    <td class="font-family-cairo fw-bold text-center">
                                                        <span x-text="itemData.notes[<?php echo e($noteTypeId); ?>] || ''"></span>
                                                    </td>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['تعديل الأصناف', 'حذف الأصناف'])): ?>
                                                <!--[if BLOCK]><![endif]--><?php if($visibleColumns['actions']): ?>
                                                    <td class="d-flex justify-content-center align-items-center gap-2 mt-2">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('تعديل الأصناف')): ?>
                                                            <button type="button" title="تعديل الصنف" class="btn btn-success btn-sm"
                                                                wire:click="edit(<?php echo e($item->id); ?>)">
                                                                <i class="las la-edit fa-lg"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('حذف الأصناف')): ?>
                                                            <button type="button" title="حذف الصنف" class="btn btn-danger btn-sm"
                                                                wire:click="delete(<?php echo e($item->id); ?>)"
                                                                onclick="confirm('هل أنت متأكد من حذف هذا الصنف؟') || event.stopImmediatePropagation()">
                                                                <i class="las la-trash fa-lg"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php endif; ?>
                                        </tr>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <?php
                                        $colspan = $this->visibleColumnsCount;
                                    ?>
                                    <tr>
                                        <td colspan="<?php echo e($colspan); ?>"
                                            class="text-center font-family-cairo fw-bold">لا يوجد سجلات
                                        </td>
                                    </tr>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                        
                    </div>

                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="font-family-cairo fw-bold mb-0 text-white">
                                        <i class="fas fa-calculator me-2"></i>
                                        تقيم المخزون
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label font-family-cairo fw-bold">اختر نوع السعر:</label>
                                            <select wire:model.live="selectedPriceType"
                                                class="form-select font-family-cairo fw-bold font-14">
                                                <option value="">اختر نوع السعر</option>
                                                <option value="cost">التكلفة</option>
                                                <option value="average_cost">متوسط التكلفة</option>
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->priceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceId => $priceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($priceId); ?>"><?php echo e($priceName); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label font-family-cairo fw-bold">المخزن المحدد:</label>
                                            <div class="form-control-plaintext font-family-cairo fw-bold">
                                                <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
                                                    <?php
                                                        $warehouse = $warehouses->firstWhere('id', $selectedWarehouse);
                                                    ?>
                                                    <?php echo e($warehouse ? $warehouse->aname : 'غير محدد'); ?>

                                                <?php else: ?>
                                                    جميع المخازن
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php if($selectedPriceType): ?>
                                            <div class="col-md-3">
                                                <h6 class="font-family-cairo fw-bold text-primary mb-1"
                                                    style="font-size: 0.95rem;">إجمالي الكمية</h6>
                                                <h4 class="font-family-cairo fw-bold text-success mb-0"
                                                    style="font-size: 1.2rem;"><?php echo e($this->totalQuantity); ?></h4>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="font-family-cairo fw-bold text-primary">إجمالي القيمة</h6>
                                                <h4 class="font-family-cairo fw-bold text-success">
                                                    <?php echo e(formatCurrency($this->totalAmount)); ?></h4>
                                            </div>
                                            <div class="col-md-2">
                                                <h6 class="font-family-cairo fw-bold text-primary">عدد الأصناف</h6>
                                                <h4 class="font-family-cairo fw-bold text-success">
                                                    <?php echo e($this->totalItems); ?></h4>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    

                    
                    
                    <div class="mt-4 d-flex justify-content-center">
                        <div class="font-family-cairo">
                        <?php echo e($this->items->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-labelledby="columnVisibilityModalLabel" aria-hidden="true" 
         x-data="columnVisibilityModal()" 
         x-init="
            columns = <?php echo \Illuminate\Support\Js::from($this->visibleColumns)->toHtml() ?>;
            prices = <?php echo \Illuminate\Support\Js::from($this->visiblePrices)->toHtml() ?>;
            notes = <?php echo \Illuminate\Support\Js::from($this->visibleNotes)->toHtml() ?>;
         "
         @close-modal.window="$el.querySelector('.btn-close').click()">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-family-cairo fw-bold" id="columnVisibilityModalLabel">
                        <i class="fas fa-columns me-2"></i>
                        خيارات عرض الأعمدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" @click="showAllColumns()" class="btn btn-success btn-sm font-family-cairo fw-bold">
                                    <i class="fas fa-eye me-1"></i>
                                    إظهار الكل
                                </button>
                                <button type="button" @click="hideAllColumns()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                    <i class="fas fa-eye-slash me-1"></i>
                                    إخفاء الكل
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-family-cairo fw-bold text-primary mb-3">
                                <i class="fas fa-list me-2"></i>
                                الأعمدة الأساسية:
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.code">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الكود
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.name">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الاسم
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.units">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الوحدات
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الكمية
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.barcode">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الباركود
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="font-family-cairo fw-bold text-primary mb-3">
                                <i class="fas fa-dollar-sign me-2"></i>
                                أعمدة التكلفة والأسعار:
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.average_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    متوسط التكلفة
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity_average_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    تكلفة المتوسطة للكمية
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.last_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    التكلفة الأخيرة
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    تكلفة الكمية
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!--[if BLOCK]><![endif]--><?php if(count($this->priceTypes) > 0): ?>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="font-family-cairo fw-bold text-info mb-3">
                                    <i class="fas fa-tags me-2"></i>
                                    أسعار البيع:
                                </h6>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" @click="showAllPrices()" class="btn btn-info btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye me-1"></i>
                                        إظهار جميع الأسعار
                                    </button>
                                    <button type="button" @click="hideAllPrices()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye-slash me-1"></i>
                                        إخفاء جميع الأسعار
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->priceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceId => $priceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" x-model="prices['<?php echo e($priceId); ?>']">
                                        <label class="form-check-label font-family-cairo fw-bold">
                                            <?php echo e($priceName); ?>

                                        </label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['تعديل الأصناف', 'حذف الأصناف'])): ?>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-family-cairo fw-bold text-warning mb-3">
                                    <i class="fas fa-cogs me-2"></i>
                                    العمليات:
                                </h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" x-model="columns.actions">
                                    <label class="form-check-label font-family-cairo fw-bold">
                                        العمليات (تعديل/حذف)
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    
                    <!--[if BLOCK]><![endif]--><?php if(count($this->noteTypes) > 0): ?>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="font-family-cairo fw-bold text-success mb-3">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    الملاحظات:
                                </h6>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" @click="showAllNotes()" class="btn btn-success btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye me-1"></i>
                                        إظهار جميع الملاحظات
                                    </button>
                                    <button type="button" @click="hideAllNotes()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye-slash me-1"></i>
                                        إخفاء جميع الملاحظات
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->noteTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noteId => $noteName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" x-model="notes['<?php echo e($noteId); ?>']">
                                        <label class="form-check-label font-family-cairo fw-bold">
                                            <?php echo e($noteName); ?>

                                        </label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold" @click="applyChanges()"
                            wire:loading.attr="disabled" wire:target="updateVisibility">
                        <span wire:loading.remove wire:target="updateVisibility">
                            <i class="fas fa-check me-2"></i>
                            تطبيق التغييرات
                        </span>
                        <span wire:loading wire:target="updateVisibility">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">جاري التطبيق...</span>
                            </div>
                            جاري التطبيق...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold" data-bs-dismiss="modal">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Alpine.js component for item row calculations
function itemRow(itemData, initialUnitId) {
    return {
        itemData: itemData,
        selectedUnitId: initialUnitId,
        
        get selectedUnit() {
            return this.itemData.units[this.selectedUnitId] || null;
        },
        
        get selectedUVal() {
            return this.selectedUnit?.u_val || 1;
        },
        
        get currentUnitQuantity() {
            return this.selectedUVal > 0 ? this.itemData.base_quantity / this.selectedUVal : 0;
        },
        
        get formattedQuantity() {
            const integer = this.selectedUVal > 0 ? Math.floor(this.itemData.base_quantity / this.selectedUVal) : 0;
            const remainder = this.selectedUVal > 0 ? this.itemData.base_quantity % this.selectedUVal : 0;
            
            // Find smaller unit
            const units = Object.values(this.itemData.units);
            const smallerUnit = units.length > 0 ? units.reduce((min, unit) => 
                unit.u_val < min.u_val ? unit : min
            ) : null;
            
            return {
                integer: integer,
                remainder: remainder,
                unitName: this.selectedUnit?.name || '',
                smallerUnitName: smallerUnit?.name || ''
            };
        },
        
        get unitCostPrice() {
            return this.selectedUnit?.cost || 0;
        },
        
        get quantityCost() {
            return this.currentUnitQuantity * this.unitCostPrice;
        },
        
        get unitAverageCost() {
            return this.itemData.average_cost * this.selectedUVal;
        },
        
        get quantityAverageCost() {
            return this.currentUnitQuantity * this.unitAverageCost;
        },
        
        get currentBarcodes() {
            return this.itemData.barcodes[this.selectedUnitId] || [];
        },
        
        getPriceForType(priceTypeId) {
            const unitPrices = this.itemData.prices[this.selectedUnitId] || {};
            const price = unitPrices[priceTypeId];
            return price ? this.formatCurrency(price.price) : 'N/A';
        },
        
        formatCurrency(value) {
            if (value === null || value === undefined) return '0.00';
            return new Intl.NumberFormat('ar-SA', {
                style: 'currency',
                currency: 'SAR',
                minimumFractionDigits: 2
            }).format(value);
        },
        
        formatNumber(value) {
            if (value === null || value === undefined) return '0';
            // Remove trailing zeros and decimal point if not needed
            return parseFloat(value).toString();
        }
    }
}

// Alpine.js component for filters with debouncing
function filtersComponent() {
    return {
        searchValue: '',
        warehouseValue: '',
        groupValue: '',
        categoryValue: '',
        searchTimeout: null,
        
        updateSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.$wire.set('search', this.searchValue);
            }, 500);
        },
        
        updateWarehouse() {
            this.$wire.set('selectedWarehouse', this.warehouseValue);
        },
        
        updateGroup() {
            this.$wire.set('selectedGroup', this.groupValue);
        },
        
        updateCategory() {
            this.$wire.set('selectedCategory', this.categoryValue);
        },
        
        clearFilters() {
            this.searchValue = '';
            this.warehouseValue = '';
            this.groupValue = '';
            this.categoryValue = '';
            this.$wire.call('clearFilters');
        }
    }
}

// Alpine.js component for column visibility modal
function columnVisibilityModal() {
    return {
        columns: {},
        prices: {},
        notes: {},
        
        showAllColumns() {
            console.log('showAllColumns called', { columns: this.columns, prices: this.prices, notes: this.notes });
            Object.keys(this.columns).forEach(key => this.columns[key] = true);
            Object.keys(this.prices).forEach(key => this.prices[key] = true);
            Object.keys(this.notes).forEach(key => this.notes[key] = true);
        },
        
        hideAllColumns() {
            console.log('hideAllColumns called', { columns: this.columns, prices: this.prices, notes: this.notes });
            Object.keys(this.columns).forEach(key => this.columns[key] = false);
            Object.keys(this.prices).forEach(key => this.prices[key] = false);
            Object.keys(this.notes).forEach(key => this.notes[key] = false);
        },
        
        showAllPrices() {
            console.log('showAllPrices called', { prices: this.prices });
            Object.keys(this.prices).forEach(key => this.prices[key] = true);
        },
        
        hideAllPrices() {
            console.log('hideAllPrices called', { prices: this.prices });
            Object.keys(this.prices).forEach(key => this.prices[key] = false);
        },
        
        showAllNotes() {
            console.log('showAllNotes called', { notes: this.notes });
            Object.keys(this.notes).forEach(key => this.notes[key] = true);
        },
        
        hideAllNotes() {
            console.log('hideAllNotes called', { notes: this.notes });
            Object.keys(this.notes).forEach(key => this.notes[key] = false);
        },
        
        applyChanges() {
            console.log('applyChanges called', { columns: this.columns, prices: this.prices, notes: this.notes });
            this.$wire.call('updateVisibility', this.columns, this.prices, this.notes);
        }
    }
}

</script><?php /**PATH D:\laragon\www\massar1.02\resources\views\livewire/item-management/items/index.blade.php ENDPATH**/ ?>