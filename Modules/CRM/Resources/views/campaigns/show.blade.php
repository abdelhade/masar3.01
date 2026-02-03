@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => $campaign->title,
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Marketing Campaigns'), 'url' => route('campaigns.index')],
            ['label' => $campaign->title]
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
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

            <!-- أزرار الإجراءات -->
            <div class="mb-3">
                @if ($campaign->isDraft())
                    <form action="{{ route('campaigns.send', $campaign) }}" method="POST" style="display:inline-block;"
                          onsubmit="return confirm('{{ __('Are you sure you want to send this campaign?') }}');">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="las la-paper-plane me-2"></i>
                            {{ __('Send Campaign Now') }}
                        </button>
                    </form>

                    <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-primary">
                        <i class="las la-edit me-2"></i>
                        {{ __('Edit') }}
                    </a>
                @endif

                <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">
                    <i class="las la-arrow-left me-2"></i>
                    {{ __('Back to List') }}
                </a>
            </div>

            <!-- إحصائيات الحملة -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ $campaign->total_recipients }}</h3>
                            <p class="text-muted mb-0">{{ __('Total Recipients') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ $campaign->total_sent }}</h3>
                            <p class="text-muted mb-0">{{ __('Successfully Sent') }}</p>
                            @if ($campaign->total_recipients > 0)
                                <small class="text-success">{{ $campaign->success_rate }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info">{{ $campaign->total_opened }}</h3>
                            <p class="text-muted mb-0">{{ __('Opened') }}</p>
                            @if ($campaign->total_sent > 0)
                                <small class="text-info">{{ $campaign->open_rate }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $campaign->total_clicked }}</h3>
                            <p class="text-muted mb-0">{{ __('Clicked') }}</p>
                            @if ($campaign->total_sent > 0)
                                <small class="text-warning">{{ $campaign->click_rate }}%</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- تفاصيل الحملة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Campaign Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Title') }}:</strong> {{ $campaign->title }}</p>
                            <p><strong>{{ __('Subject') }}:</strong> {{ $campaign->subject }}</p>
                            <p><strong>{{ __('Status') }}:</strong> 
                                @if ($campaign->status === 'draft')
                                    <span class="badge bg-secondary">{{ __('Draft') }}</span>
                                @elseif ($campaign->status === 'sent')
                                    <span class="badge bg-success">{{ __('Sent') }}</span>
                                @else
                                    <span class="badge bg-info">{{ __('Scheduled') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Created By') }}:</strong> {{ $campaign->creator->name }}</p>
                            <p><strong>{{ __('Created At') }}:</strong> {{ $campaign->created_at->format('Y-m-d H:i') }}</p>
                            @if ($campaign->sent_at)
                                <p><strong>{{ __('Sent At') }}:</strong> {{ $campaign->sent_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>{{ __('Message') }}:</strong>
                        <div class="border p-3 mt-2 bg-light">
                            {!! nl2br(e($campaign->message)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- أفضل العملاء تفاعلاً -->
            @if ($topClients->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Top Engaged Clients') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Client Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Opened At') }}</th>
                                        <th>{{ __('Clicked At') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topClients as $log)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $log->client->cname ?? __('Unknown') }}</td>
                                            <td>{{ $log->email }}</td>
                                            <td>
                                                @if ($log->status === 'clicked')
                                                    <span class="badge bg-success">
                                                        <i class="las la-mouse-pointer"></i> {{ __('Clicked') }}
                                                    </span>
                                                @elseif ($log->status === 'opened')
                                                    <span class="badge bg-info">
                                                        <i class="las la-envelope-open"></i> {{ __('Opened') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $log->opened_at ? $log->opened_at->format('Y-m-d H:i') : '-' }}</td>
                                            <td>{{ $log->clicked_at ? $log->clicked_at->format('Y-m-d H:i') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
