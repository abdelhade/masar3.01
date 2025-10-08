{{-- resources/views/components/excel-importer.blade.php --}}
<div class="excel-importer-wrapper" x-data="excelImporter({
    model: '{{ $model }}',
    columnMapping: {{ json_encode($columnMapping) }},
    validationRules: {{ json_encode($validationRules) }},
    apiBaseUrl: '{{ $apiBaseUrl }}'
})">

    {{-- الزر الصغير --}}
    <button @click="openModal" class="btn btn-primary {{ $buttonSize === 'small' ? 'btn-sm' : '' }}" type="button">
        <i class="fas fa-upload me-2"></i>
        <span>{{ $buttonText }}</span>
    </button>

    {{-- الـ Modal --}}
    <div x-show="showModal" x-cloak class="modal fade" @click.self="closeModal">
        <div class="modal-dialog modal-lg" @click.stop>
            <div class="modal-content">
                {{-- Header --}}
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fas fa-file-excel me-2"></i>
                        استيراد من Excel
                    </h5>
                    <button @click="closeModal" type="button" class="btn-close"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">
                    {{-- Upload Step --}}
                    <div x-show="step === 'upload'" class="text-center p-4">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">الأعمدة المطلوبة في ملف Excel:</h6>
                            <div class="row">
                                <template x-for="(header, key) in columnMapping" :key="key">
                                    <div class="col-3 mb-2">
                                        <div class="list-group-item text-center fw-semibold border rounded py-2"
                                            x-text="key"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <input type="file" id="excel-file-input" accept=".xlsx,.xls,.csv" @change="handleFileChange"
                            class="d-none">
                        <label for="excel-file-input"
                            class="border border-2 border-dashed p-5 rounded w-100 text-center"
                            style="cursor: pointer;">
                            <i class="fas fa-upload fa-3x mb-3 text-muted"></i>
                            <p class="mb-2 fw-bold">اضغط لاختيار ملف Excel</p>
                            <p class="text-muted mb-0">يدعم: .xlsx, .xls, .csv</p>
                        </label>
                    </div>

                    {{-- Preview Step --}}
                    <div x-show="step === 'preview'" class="mt-3">
                        <h5 class="mb-3">معاينة البيانات (أول 5 صفوف)</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <template x-if="preview && preview[0]">
                                            <template x-for="(header, index) in preview[0]" :key="index">
                                                <th x-text="header"></th>
                                            </template>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="preview">
                                        <template x-for="(row, rowIndex) in preview.slice(1, 6)" :key="rowIndex">
                                            <tr>
                                                <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                                    <td x-text="cell"></td>
                                                </template>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex gap-3 mt-3">
                            <button @click="handleImport" :disabled="importing" class="btn btn-primary">
                                <span
                                    x-text="importing ? 'جاري الاستيراد...' : `استيراد ${preview ? preview.length - 1 : 0} صف`"></span>
                            </button>
                            <button @click="resetImporter" class="btn btn-secondary">إلغاء</button>
                        </div>
                    </div>

                    {{-- Importing Step --}}
                    <div x-show="step === 'importing'" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 fw-bold">جاري استيراد البيانات...</p>
                        <p class="text-muted">الرجاء الانتظار</p>
                    </div>

                    {{-- Results Step --}}
                    <div x-show="step === 'results'" class="mt-3">
                        <h5 class="mb-3">نتائج الاستيراد</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <div>
                                            <h6 class="card-title mb-1">نجح</h6>
                                            <p class="card-text display-4 mb-0" x-text="results?.success || 0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body d-flex align-items-center">
                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                        <div>
                                            <h6 class="card-title mb-1">فشل</h6>
                                            <p class="card-text display-4 mb-0" x-text="results?.failed || 0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Errors --}}
                        <template x-if="results?.errors && results.errors.length > 0">
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">الأخطاء:</h6>
                                <div style="max-height: 240px; overflow-y: auto;">
                                    <template x-for="(error, index) in results.errors" :key="index">
                                        <div class="border rounded p-2 mb-2 bg-white">
                                            <p class="mb-1 fw-bold text-danger"
                                                x-text="error.row ? `الصف ${error.row}:` : 'خطأ:'"></p>
                                            <p class="mb-0 text-muted"
                                                x-text="error.message || (Array.isArray(error.errors) ? error.errors.join(', ') : JSON.stringify(error.errors))">
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <button @click="resetImporter" class="btn btn-primary w-100 mt-3">استيراد ملف جديد</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function excelImporter(config) {
        return {
            showModal: false,
            step: 'upload',
            file: null,
            preview: null,
            importing: false,
            results: null,
            model: config.model,
            columnMapping: config.columnMapping,
            validationRules: config.validationRules,
            apiBaseUrl: config.apiBaseUrl,

            openModal() {
                this.showModal = true;
                this.resetImporter();
                // إضافة كلاس show للمودال عشان يظهر مع Bootstrap
                setTimeout(() => {
                    document.querySelector('.modal').classList.add('show');
                    document.querySelector('.modal').style.display = 'block';
                    document.body.classList.add('modal-open');
                    // إضافة backdrop يدويًا
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }, 0);
            },

            closeModal() {
                this.showModal = false;
                // إزالة كلاس show وإغلاق المودال
                document.querySelector('.modal').classList.remove('show');
                document.querySelector('.modal').style.display = 'none';
                document.body.classList.remove('modal-open');
                // إزالة الـ backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            },

            resetImporter() {
                this.step = 'upload';
                this.file = null;
                this.preview = null;
                this.results = null;
                this.importing = false;
            },

            async handleFileChange(event) {
                const selectedFile = event.target.files[0];
                if (!selectedFile) return;

                this.file = selectedFile;
                this.step = 'preview';

                const formData = new FormData();
                formData.append('file', selectedFile);

                try {
                    const response = await fetch(`${this.apiBaseUrl}/${this.model}/preview`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.preview = data.data;
                    } else {
                        alert(data.error || 'حدث خطأ أثناء معاينة الملف');
                        this.resetImporter();
                    }
                } catch (error) {
                    console.error('خطأ في المعاينة:', error);
                    alert('حدث خطأ أثناء معاينة الملف');
                    this.resetImporter();
                }
            },

            async handleImport() {
                this.importing = true;
                this.step = 'importing';

                const formData = new FormData();
                formData.append('file', this.file);
                formData.append('model', this.model);
                formData.append('mapping', JSON.stringify(this.columnMapping));
                formData.append('validation_rules', JSON.stringify(this.validationRules));

                try {
                    const response = await fetch(`${this.apiBaseUrl}/${this.model}/import`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.results = data.results;
                        this.step = 'results';

                        if (this.results.success > 0) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    } else {
                        this.results = {
                            success: 0,
                            failed: 0,
                            errors: [{
                                message: data.error || 'حدث خطأ أثناء الاستيراد'
                            }]
                        };
                        this.step = 'results';
                    }
                } catch (error) {
                    console.error('خطأ في الاستيراد:', error);
                    this.results = {
                        success: 0,
                        failed: 0,
                        errors: [{
                            message: error.message
                        }]
                    };
                    this.step = 'results';
                } finally {
                    this.importing = false;
                }
            },

            downloadTemplate() {
                const headers = Object.keys(this.columnMapping);
                const params = new URLSearchParams({
                    headers: JSON.stringify(headers),
                    filename: `${this.model}_template.xlsx`
                });

                window.open(`${this.apiBaseUrl}/template?${params}`, '_blank');
            }
        }
    }
</script>
