@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Marketing Campaigns'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Marketing Campaigns')]
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Campaigns')
                <a href="{{ route('campaigns.create') }}" class="btn btn-main font-hold fw-bold">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('New Campaign') }}
                </a>
            @endcan
            <br><br>

            @if (session('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Campaign Title') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Total Recipients') }}</th>
                                    <th>{{ __('Sent') }}</th>
                                    <th>{{ __('Opened') }}</th>
                                    <th>{{ __('Open Rate') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($campaigns as $campaign)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $campaign->title }}</td>
                                        <td>
                                            @if ($campaign->status === 'draft')
                                                <span class="badge bg-secondary">{{ __('Draft') }}</span>
                                            @elseif ($campaign->status === 'sent')
                                                <span class="badge bg-success">{{ __('Sent') }}</span>
                                            @else
                                                <span class="badge bg-info">{{ __('Scheduled') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $campaign->total_recipients }}</td>
                                        <td>{{ $campaign->total_sent }}</td>
                                        <td>{{ $campaign->total_opened }}</td>
                                        <td>
                                            @if ($campaign->total_sent > 0)
                                                <span class="badge bg-primary">{{ $campaign->open_rate }}%</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $campaign->creator->name }}</td>
                                        <td>{{ $campaign->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <a class="btn btn-info btn-icon-square-sm" 
                                               href="{{ route('campaigns.show', $campaign) }}">
                                                <i class="las la-eye"></i>
                                            </a>
                                            
                                            @if ($campaign->isDraft())
                                                @can('edit Campaigns')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                       href="{{ route('campaigns.edit', $campaign) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('delete Campaigns')
                                                    <form action="{{ route('campaigns.destroy', $campaign) }}" 
                                                          method="POST" style="display:inline-block;"
                                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this campaign?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No campaigns added yet') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $campaigns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
