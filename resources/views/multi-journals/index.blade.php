@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.journals')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Journals'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Journals')]],
    ])
    <div class="card-header">
        @can('create multi-journals')
            <a href="{{ route('multi-journals.create') }}" type="button" class="btn btn-main">
                <i class="fas fa-plus me-2"></i>
                {{ __('Add New') }}
            </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-striped mb-0" style="min-width: 1200px;">
                <thead class="table-light text-center align-middle">

                    <tr>
                        <th class="font-hold fw-bold font-14 text-center">#</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Date') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Operation Number') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Operation Type') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Statement') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Amount') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('From Account') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('To Account') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Employee') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Employee 2') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('User') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Created At') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Notes') }}</th>
                        <th class="font-hold fw-bold font-14 text-center">{{ __('Reviewed') }}</th>
                        <th class="font-hold fw-bold font-14 text-center" class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($multis as $multi)
                        <tr>
                            <td class="font-hold fw-bold font-14 text-center">{{ $loop->iteration }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_date }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_id }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->type->ptext ?? '—' }}
                            </td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->details }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->pro_value }}</td>
                            <td class="font-hold fw-bold font-14 text-center">
                                {{ $multi->account1->aname ?? __('Multiple') }}</td>
                            <td class="font-hold fw-bold font-14 text-center">
                                {{ $multi->account2->aname ?? __('Multiple') }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->emp1->aname ?? '' }}
                            </td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->emp2->aname ?? '' }}
                            </td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->user }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->created_at }}</td>
                            <td class="font-hold fw-bold font-14 text-center">{{ $multi->info }}</td>
                            <td class="font-hold fw-bold font-14 text-center">
                                {{ $multi->confirmed ? __('Yes') : __('No') }}</td>
                            <td class="font-hold fw-bold font-14 text-center" x-show="columns[16]">
                                <button>
                                    <a href="{{ route('multi-journals.edit', $multi) }}" class="btn btn-primary"><i
                                            class="las la-edit"></i></a>
                                </button>
                                <form action="{{ route('multi-journals.destroy', $multi->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-icon-square-sm"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this operation and its associated journal entry?') }}')">
                                        <i class="las la-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center">
                                <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                    <i class="las la-info-circle me-2"></i>
                                    {{ __('No data available') }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
    </div>
@endsection
