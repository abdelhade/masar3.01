@extends('admin.dashboard')
@section('content')
    <div class="card-body">
        <form action="{{ route('mysettings.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="mb-3">
                <input type="text" id="settingSearch" class="form-control form-control-sm w-25"
                    placeholder="ابحث عن إعداد...">
            </div>

            <div class="accordion" id="settingsAccordion">
                @foreach ($cateries as $category)
                    @if ($category->publicSettings->count())
                        <div class="accordion-item mb-3 border">
                            <h2 class="accordion-header" id="heading-{{ $category->id }}">
                                <button class="accordion-button collapsed fw-bold bg-light text-primary" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $category->id }}"
                                    aria-expanded="false" aria-controls="collapse-{{ $category->id }}">
                                    <i class="bi bi-folder text-secondary me-2"></i>{{ $category->name }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $category->id }}" class="accordion-collapse collapse"
                                aria-labelledby="heading-{{ $category->id }}" data-bs-parent="#settingsAccordion">
                                <div class="accordion-body p-3">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-bordered table-sm align-middle text-center settings-table">
                                            <thead class="table-light">
                                                <tr>
                                                    @for ($i = 0; $i < 3; $i++)
                                                        <th style="min-width: 120px">الاسم</th>
                                                        <th style="min-width: 160px">القيمة</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $settings = $category->publicSettings->values(); @endphp
                                                @for ($i = 0; $i < $settings->count(); $i += 3)
                                                    <tr>
                                                        @for ($col = 0; $col < 3; $col++)
                                                            @php $index = $i + $col; @endphp
                                                            @if (isset($settings[$index]))
                                                                <td class="fw-semibold">{{ $settings[$index]->label }}</td>
                                                                <td>
                                                                    @if ($settings[$index]->input_type === 'boolean')
                                                                        <div class="d-flex justify-content-center">
                                                                            <input type="hidden"
                                                                                name="settings[{{ $settings[$index]->key }}]"
                                                                                value="0">
                                                                            <div class="form-check form-switch">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox" role="switch"
                                                                                    name="settings[{{ $settings[$index]->key }}]"
                                                                                    value="1"
                                                                                    id="switch-{{ $settings[$index]->key }}"
                                                                                    {{ $settings[$index]->value ? 'checked' : '' }}
                                                                                    style="transform: scale(1.2); cursor: pointer;">
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <input
                                                                            type="{{ $settings[$index]->input_type === 'number' ? 'number' : $settings[$index]->input_type }}"
                                                                            name="settings[{{ $settings[$index]->key }}]"
                                                                            value="{{ $settings[$index]->value }}"
                                                                            class="form-control form-control-sm text-center mx-auto"
                                                                            style="max-width: 160px;">
                                                                    @endif
                                                                </td>
                                                            @else
                                                                <td></td>
                                                                <td></td>
                                                            @endif
                                                        @endfor
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- @if ($cateries->sum(fn($category) => $category->publicSettings->count()) > 0) --}}
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary px-4">حفظ الإعدادات</button>
            </div>
            {{-- @endif --}}
        </form>
    </div>





    <script>
        document.getElementById("settingSearch").addEventListener("input", function() {
            let value = this.value.toLowerCase();
            document.querySelectorAll(".settings-table tbody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    </script>

@endsection
