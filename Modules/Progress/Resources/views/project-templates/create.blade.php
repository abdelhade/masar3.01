@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('قوالب المشاريع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('قوالب المشاريع'), 'url' => route('project.template.index')],
            ['label' => __('انشاء')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>اضافة قالب مشروع جديد</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('project.template.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- اسم القالب --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="name">اسم القالب</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="ادخل اسم القالب" value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الوصف --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="description">الوصف</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="ادخل وصف القالب">{{ old('description') }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="card mb-4 border-0 shadow-sm rounded-3">
                            <div
                                class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-3">
                                <h6 class="mb-0 fw-bold">{{ __('general.template_items') }}</h6>
                                <button type="button" id="add-row" class="btn btn-sm btn-primary rounded-3">
                                    <i class="fa-solid fa-plus me-1"></i> {{ __('general.add_item') }}
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-hover table-striped mb-0 align-middle text-center">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>{{ __('general.item') }}</th>
                                            <th>{{ __('general.default_quantity') }}</th>
                                            <th class="text-center">{{ __('general.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-table">
                                        @if (old('items'))
                                            @foreach (old('items') as $i => $item)
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $i }}][work_item_id]"
                                                            class="form-select form-select-sm rounded-3" required>
                                                            <option value="">{{ __('general.choose_item') }}</option>
                                                            @foreach ($workItems as $workItem)
                                                                <option value="{{ $workItem->id }}"
                                                                    {{ $item['work_item_id'] == $workItem->id ? 'selected' : '' }}>
                                                                    {{ $workItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $i }}][default_quantity]"
                                                            class="form-control form-control-sm rounded-3"
                                                            value="{{ $item['default_quantity'] ?? 1 }}" min="1">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger rounded-3 remove-row">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> حفظ
                            </button>

                            <a href="{{ route('project.template.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = {{ old('items') ? count(old('items')) : 0 }};

            document.getElementById('add-row').addEventListener('click', function() {
                let table = document.getElementById('items-table');
                let newRow = document.createElement('tr');
                newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][work_item_id]" class="form-select form-select-sm rounded-3" required>
                    <option value="">{{ __('general.choose_item') }}</option>
                    @foreach ($workItems as $workItem)
                        <option value="{{ $workItem->id }}">{{ $workItem->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][default_quantity]"
                    class="form-control form-control-sm rounded-3" value="1" min="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger rounded-3 remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;
                table.appendChild(newRow);
                rowIndex++;
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-row')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>
@endpush
