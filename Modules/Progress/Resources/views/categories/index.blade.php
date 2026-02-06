@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('general.categories') }}</li>
@endsection

@section('title', __('general.categories'))

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">{{ __('general.categories') }}</h2>

            </div>
            @can('categories-create')
                <a href="{{ route('progress.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> {{ __('general.add_new') }}
                </a>
            @endcan

        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                @if ($categories->isEmpty())
                    <div class="text-center py-4 text-muted">
                        لا توجد فئات حتى الآن.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('general.category_name') }}</th>
                                    @canany(['categories-edit', 'categories-delete'])
                                        <th style="width:160px">{{ __('general.actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        @canany(['categories-edit', 'categories-delete'])
                                            <td>
                                                @can('categories-edit')
                                                    <a href="{{ route('progress.categories.edit', $category->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('categories-delete')
                                                    <form action="{{ route('progress.categories.destroy', $category->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('هل أنت متأكدة من الحذف؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
