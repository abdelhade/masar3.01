<div>
    <div class="row mt-4">
        {{-- Form لإضافة/تعديل Type --}}
        @if (!$unit_id)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Manage Offer Types') }}</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="{{ $type_id ? 'updateType' : 'storeType' }}">
                            <div class="row">
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" placeholder="{{ __('Type Name') }}"
                                        wire:model.defer="type_name">
                                    @error('type_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-lg-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="las la-save"></i> {{ __('Save') }}
                                    </button>
                                    @if ($type_id)
                                        <button type="button" class="btn btn-danger" wire:click="cancel">
                                            <i class="las la-times"></i> {{ __('Cancel') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form لإضافة/تعديل Unit (مع اختيار Type) --}}
        @if (!$type_id)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Manage Units') }}</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="{{ $unit_id ? 'updateUnit' : 'storeUnit' }}">
                            <div class="row">
                                <div class="col-lg-5">
                                    <select class="form-control" wire:model="selected_type_id_for_unit">
                                        <option value="">{{ __('Select Type') }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selected_type_id_for_unit')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-lg-5">
                                    <input type="text" class="form-control" placeholder="{{ __('Unit Name') }}"
                                        wire:model.defer="unit_name">
                                    @error('unit_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-lg-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="las la-save"></i> {{ __('Save') }}
                                    </button>
                                    @if ($unit_id)
                                        <button type="button" class="btn btn-danger" wire:click="cancel">
                                            <i class="las la-times"></i> {{ __('Cancel') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- قائمة الأنواع مع الوحدات التابعة (Nested) --}}
    <div class="row mt-4">
        <div class="col-12">
            @forelse ($types as $type)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $type->name }} <small class="text-muted">({{ __('Units:') }}
                                {{ $type->units->count() }})</small></h6>
                        <div>
                            <button wire:click="editType({{ $type->id }})" class="btn btn-success btn-sm">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button wire:click="destroyType({{ $type->id }})" class="btn btn-danger btn-sm"
                                onclick="return confirm('{{ __('Are you sure? This will also delete related units.') }}')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($type->units->count() > 0)
                            <table class="table table-bordered table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Unit Name') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($type->units as $unit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $unit->name }}</td>
                                            <td>
                                                <button wire:click="editUnit({{ $unit->id }})"
                                                    class="btn btn-success btn-xs"><i class="fa fa-edit"></i></button>
                                                <button wire:click="destroyUnit({{ $unit->id }})"
                                                    class="btn btn-danger btn-xs"
                                                    onclick="return confirm('{{ __('Are you sure?') }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted text-center">{{ __('No units for this type') }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-info">{{ __('No types added') }}</div>
            @endforelse
        </div>
    </div>
</div>
