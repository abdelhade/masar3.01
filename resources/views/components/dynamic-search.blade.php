@php
    if (isset($filters) && !empty($filters)) {
        $query = $model::query();

        foreach ($filters as $key => $value) {
            $query->whereIn($key, array_map('intval', (array) $value));
        }

        $options = $query->get();
    } else {
        $options = $options ?? $model::all();
    }
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <select class="select2-dynamic {{ $class }}" id="{{ $name }}" name="{{ $name }}"
        @if ($required) required @endif>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option value="{{ $option->id }}" data-balance="{{ $option->balance }}"
                {{ $selected == $option->id ? 'selected' : '' }}>
                {{ $option->{$column} }}
            </option>
        @endforeach
    </select>
    @error($name)
        <small class="text-danger">{{ $message }}</small>
    @enderror

    {{-- <p>الرصيد: <span id="balance-display">-</span></p> --}}
</div>

@push('styles')
    <style>
        .select2-container--default .select2-selection--single {
            height: 50px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 6px 12px;
            background-color: #fff;
            font-size: 0.875rem;
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #5c9ded;
            box-shadow: 0 0 0 0.25rem rgba(92, 157, 237, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // تهيئة select2
            $('.select2-dynamic').select2({
                placeholder: '{{ $placeholder }}',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "لا توجد نتائج";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                }
            });

            // تحديث الرصيد عند تغيير الاختيار
            $('#{{ $name }}').on('change', function() {
                let balance = $(this).find(':selected').data('balance') || '-';
                $('#balance-display').text(balance);
            });

            // عرض الرصيد الافتراضي لو فيه قيمة مختارة
            $('#{{ $name }}').trigger('change');
        });
    </script>
@endpush
