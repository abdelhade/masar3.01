{{-- مثال على كيفية دمج Alpine.js للحسابات على client-side --}}

<div x-data="manufacturingCalculator()" 
     x-init="
        products = @js($selectedProducts);
        rawMaterials = @js($selectedRawMaterials);
        expenses = @js($additionalExpenses);
     "
     class="container">
    
    {{-- قسم المنتجات --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>المنتجات المصنعة</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>التكلفة</th>
                        <th>النسبة %</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(product, index) in products" :key="product.id">
                        <tr>
                            <td x-text="product.name"></td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="product.quantity"
                                    @input="updateProductTotal(index)"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="product.average_cost"
                                    @input="updateProductTotal(index)"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="product.cost_percentage"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <span x-text="parseFloat(product.total_cost).toFixed(2)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>الإجمالي:</strong></td>
                        <td><strong x-text="totalProductsCost.toFixed(2)"></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- قسم المواد الخام --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>المواد الخام</h5>
        </div>
        <div class="card-body">
            {{-- البحث المحسّن --}}
            <div x-data="optimizedSearch()" class="mb-3">
                <input 
                    type="text" 
                    x-model="searchTerm"
                    @keydown.arrow-down="handleKeyDown()"
                    @keydown.arrow-up="handleKeyUp()"
                    @keydown.enter.prevent="handleEnter()"
                    placeholder="ابحث عن مادة خام..."
                    class="form-control"
                >
                
                <div x-show="results.length > 0" class="list-group mt-2">
                    <template x-for="(item, index) in results" :key="item.id">
                        <button 
                            type="button"
                            @click="selectResult(item)"
                            :class="{ 'active': selectedIndex === index }"
                            class="list-group-item list-group-item-action"
                        >
                            <span x-text="item.name"></span>
                        </button>
                    </template>
                </div>
                
                <div x-show="isLoading" class="text-center mt-2">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">جاري البحث...</span>
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>المادة</th>
                        <th>الوحدة</th>
                        <th>الكمية</th>
                        <th>التكلفة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(material, index) in rawMaterials" :key="material.id">
                        <tr>
                            <td x-text="material.name"></td>
                            <td>
                                <select 
                                    x-model="material.unit_id"
                                    @change="updateRawMaterialUnit(index)"
                                    class="form-select"
                                >
                                    <template x-for="unit in material.unitsList" :key="unit.id">
                                        <option :value="unit.id" x-text="unit.name"></option>
                                    </template>
                                </select>
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="material.quantity"
                                    @input="updateRawMaterialTotal(index)"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="material.average_cost"
                                    @input="updateRawMaterialTotal(index)"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <span x-text="parseFloat(material.total_cost).toFixed(2)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>الإجمالي:</strong></td>
                        <td><strong x-text="totalRawMaterialsCost.toFixed(2)"></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- قسم المصروفات --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>المصروفات الإضافية</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>الوصف</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(expense, index) in expenses" :key="index">
                        <tr>
                            <td>
                                <input 
                                    type="text" 
                                    x-model="expense.description"
                                    class="form-control"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    x-model.number="expense.amount"
                                    class="form-control"
                                >
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-end"><strong>الإجمالي:</strong></td>
                        <td><strong x-text="totalExpenses.toFixed(2)"></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- الإجماليات والأزرار --}}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>الإجماليات</h5>
                    <table class="table table-sm">
                        <tr>
                            <td>تكلفة المواد الخام:</td>
                            <td><strong x-text="totalRawMaterialsCost.toFixed(2)"></strong> ج.م</td>
                        </tr>
                        <tr>
                            <td>المصروفات الإضافية:</td>
                            <td><strong x-text="totalExpenses.toFixed(2)"></strong> ج.م</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>إجمالي تكلفة التصنيع:</strong></td>
                            <td><strong x-text="totalManufacturingCost.toFixed(2)"></strong> ج.م</td>
                        </tr>
                        <tr>
                            <td>تكلفة المنتجات:</td>
                            <td><strong x-text="totalProductsCost.toFixed(2)"></strong> ج.م</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>الإجراءات</h5>
                    <div class="d-grid gap-2">
                        <button 
                            type="button"
                            @click="distributeCostsByPercentage()"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-percentage"></i>
                            توزيع التكاليف حسب النسب
                        </button>
                        
                        <div class="input-group">
                            <input 
                                type="number" 
                                x-model.number="multiplier"
                                class="form-control"
                                placeholder="المضاعف"
                                min="0.1"
                                step="0.1"
                            >
                            <button 
                                type="button"
                                @click="applyQuantityMultiplier(multiplier)"
                                class="btn btn-info"
                            >
                                <i class="fas fa-calculator"></i>
                                مضاعفة الكميات
                            </button>
                        </div>
                        
                        <button 
                            type="button"
                            wire:click="saveInvoice"
                            class="btn btn-success"
                        >
                            <i class="fas fa-save"></i>
                            حفظ الفاتورة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // تهيئة Alpine.js data عند تحميل الصفحة
    document.addEventListener('alpine:init', () => {
        // يمكن إضافة تخصيصات إضافية هنا
    });
</script>
@endpush
