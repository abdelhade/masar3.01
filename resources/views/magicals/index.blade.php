@extends('admin.dashboard')

@section('content')
    <div class="container" style="font-family: 'Cairo', sans-serif; direction: rtl;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>{{ __('magicals') }}</h2>
            <a href="{{ route('magicals.create') }}" class="btn btn-success">{{ __('add_new') }}</a>
        </div>
        <table class="table table-bordered table-striped text-end">
            <thead>
                <tr>
                    <th>{{ __('magic_name') }}</th>
                    <th>{{ __('magic_link') }}</th>
                    <th>{{ __('is_journal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($magicals as $magical)
                    <tr>
                        <td>{{ $magical->magic_name }}</td>
                        <td>{{ $magical->magic_link }}</td>
                        <td>{{ $magical->is_journal ? __('yes') : __('no') }}</td>
                        <td>
                            <a href="{{ route('magical-forms.index', ['type' => $magical->magic_link]) }}" class="btn btn-primary">{{ __('show') }}</a>
                            <a href="{{ route('magicals.edit', $magical->id) }}" class="btn btn-primary">{{ __('edit') }}</a>
                            <a href="{{ route('magicals.destroy', $magical->id) }}" class="btn btn-danger">{{ __('delete') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection