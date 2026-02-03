@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => $client->cname,
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Clients'), 'url' => route('clients.index')],
            ['label' => $client->cname],
        ],
    ])

    <div class="row">
        <!-- Sidebar: Contact Info & Quick Actions -->
        <div class="col-lg-3 mb-4">
             <div class="card h-100 shadow-sm">
                 <div class="card-body text-center">
                     <div class="mb-3">
                        <div class="avatar-circle mx-auto bg-light text-primary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; border-radius: 50%;">
                             {{ substr($client->cname, 0, 1) }}
                        </div>
                     </div>
                     <h5 class="card-title fw-bold">{{ $client->cname }}</h5>
                     <p class="text-muted small">{{ $client->clientType->title ?? __('Uncategorized') }}</p>
                     
                     <div class="d-grid gap-2 mb-4">
                         @if($client->phone)
                            <a href="https://wa.me/{{ $client->phone }}" target="_blank" class="btn btn-success">
                                <i class="lab la-whatsapp"></i> {{ __('WhatsApp') }}
                            </a>
                            <a href="tel:{{ $client->phone }}" class="btn btn-outline-primary">
                                <i class="las la-phone"></i> {{ __('Call') }}
                            </a>
                         @endif
                         @if($client->email)
                            <a href="mailto:{{ $client->email }}" class="btn btn-outline-info">
                                <i class="las la-envelope"></i> {{ __('Email') }}
                            </a>
                         @endif
                         <a href="{{ route('activities.create', ['client_id' => $client->id]) }}" class="btn btn-primary">
                             <i class="las la-plus-circle"></i> {{ __('Log Activity') }}
                         </a>
                     </div>

                     <div class="text-start">
                         <h6 class="text-muted text-uppercase small fw-bold mb-3">{{ __('Contact Details') }}</h6>
                         <ul class="list-unstyled mb-0">
                             @if($client->phone)
                             <li class="mb-2"><i class="las la-phone me-2 text-muted"></i> {{ $client->phone }}</li>
                             @endif
                             @if($client->email)
                             <li class="mb-2"><i class="las la-envelope me-2 text-muted"></i> {{ $client->email }}</li>
                             @endif
                             @if($client->address)
                             <li class="mb-2"><i class="las la-map-marker me-2 text-muted"></i> {{ $client->address }}</li>
                             @endif
                             @if($client->national_id)
                             <li class="mb-2"><i class="las la-id-card me-2 text-muted"></i> {{ $client->national_id }}</li>
                             @endif
                         </ul>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Main Content: Timeline & Tabs -->
        <div class="col-lg-9">
             <div class="card shadow-sm">
                 <div class="card-header bg-white border-bottom-0 pt-3">
                     <ul class="nav nav-tabs card-header-tabs" id="clientTabs" role="tablist">
                         <li class="nav-item">
                             <a class="nav-link active fw-bold" id="timeline-tab" data-bs-toggle="tab" href="#timeline" role="tab">
                                 <i class="las la-stream me-1"></i> {{ __('Timeline') }}
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link fw-bold" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab">
                                 <i class="las la-user me-1"></i> {{ __('Full Profile') }}
                             </a>
                         </li>
                         <!-- Add more tabs for Documents etc later -->
                     </ul>
                 </div>
                 <div class="card-body">
                     <div class="tab-content" id="clientTabsContent">
                         
                         <!-- TIMELINE TAB -->
                         <div class="tab-pane fade show active" id="timeline" role="tabpanel">
                              <!-- Quick Add Box (Optional for future) -->
                              <!-- <div class="mb-4">...Input to add note...</div> -->

                              <div class="timeline-container px-2">
                                  @if($timeline && $timeline->count() > 0)
                                    @php $lastDate = null; @endphp
                                    @foreach($timeline as $item)
                                        @php 
                                            $currentDate = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                                            $isNewDay = $currentDate != $lastDate;
                                            $lastDate = $currentDate;
                                        @endphp
                                        
                                        @if($isNewDay)
                                            <div class="timeline-date-label mb-3 mt-4">
                                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                    {{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d F Y') }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="timeline-item d-flex gap-3 mb-4">
                                            <div class="timeline-icon">
                                                <div class="icon-circle bg-soft-{{ $item->color ?? 'secondary' }} text-{{ $item->color ?? 'secondary' }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                                    <i class="{{ $item->icon ?? 'las la-circle' }} fs-5"></i>
                                                </div>
                                            </div>
                                            <div class="timeline-content flex-grow-1 card p-3 border-0 bg-light">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $item->title }}</h6>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('H:i A') }}</small>
                                                </div>
                                                <p class="mb-1 text-muted small">{{ $item->description }}</p>
                                                @if(isset($item->link))
                                                    <div class="mt-2">
                                                        <a href="{{ $item->link }}" class="btn btn-sm btn-outline-{{ $item->color ?? 'secondary' }} rounded-pill px-3">
                                                            {{ __('View Details') }} <i class="las la-arrow-right ms-1"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                  @else
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="las la-history text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                            </div>
                                            <h5 class="text-muted">{{ __('No activity found') }}</h5>
                                            <p class="text-muted">{{ __('This client has no recorded history yet.') }}</p>
                                        </div>
                                  @endif
                              </div>
                         </div>
                         
                         <!-- PROFILE TAB -->
                         <div class="tab-pane fade" id="profile" role="tabpanel">
                              <div class="table-responsive">
                                  <table class="table table-bordered table-striped">
                                      <tbody>
                                          <tr>
                                              <th width="30%">{{ __('Client Name') }}</th>
                                              <td>{{ $client->cname }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Phone') }}</th>
                                              <td>{{ $client->phone ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Email') }}</th>
                                              <td>{{ $client->email ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Address') }}</th>
                                              <td>{{ $client->address ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Client Type') }}</th>
                                              <td>{{ $client->clientType->title ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Category') }}</th>
                                              <td>{{ $client->category->name ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('National ID') }}</th>
                                              <td>{{ $client->national_id ?? '-' }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Created At') }}</th>
                                              <td>{{ $client->created_at->format('Y-m-d') }}</td>
                                          </tr>
                                          <tr>
                                              <th>{{ __('Notes') }}</th>
                                              <td>{{ $client->info ?? '-' }}</td>
                                          </tr>
                                      </tbody>
                                  </table>
                              </div>
                              <div class="mt-3 text-end">
                                  <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary">
                                      <i class="las la-edit"></i> {{ __('Edit Profile') }}
                                  </a>
                              </div>
                         </div>
                     </div>
                 </div>
             </div>
        </div>
    </div>
    
    <style>
        .timeline-container { position: relative; }
        .timeline-item { position: relative; z-index: 1; }
        /* Vertical line connecting items */
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 28px; /* Center with the 40px icon + padding */
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
            z-index: 0;
        }
        .timeline-date-label { position: relative; z-index: 1; text-align: center; }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
    </style>
@endsection
