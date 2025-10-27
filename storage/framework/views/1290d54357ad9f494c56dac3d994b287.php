<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tableId',
    'filename' => 'export',
    'excelLabel' => 'Export Excel',
    'pdfLabel' => 'Export PDF',
    'printLabel' => 'Print',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'tableId',
    'filename' => 'export',
    'excelLabel' => 'Export Excel',
    'pdfLabel' => 'Export PDF',
    'printLabel' => 'Print',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="d-flex align-items-center justify-content-end mb-3">
    <div class="btn-group" role="group" aria-label="Export actions" data-export-actions data-table-id="<?php echo e($tableId); ?>" data-filename="<?php echo e($filename); ?>" data-skip-last="true">
        <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center" data-action="export-excel">
            <i class="las la-file-excel me-1"></i>
            <span><?php echo e($excelLabel); ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center" data-action="export-pdf">
            <i class="las la-file-pdf me-1"></i>
            <span><?php echo e($pdfLabel); ?></span>
        </button>
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center" data-action="print">
            <i class="las la-print me-1"></i>
            <span><?php echo e($printLabel); ?></span>
        </button>
    </div>
</div>


<?php /**PATH D:\laragon\www\massar1.02\resources\views/components/table-export-actions.blade.php ENDPATH**/ ?>