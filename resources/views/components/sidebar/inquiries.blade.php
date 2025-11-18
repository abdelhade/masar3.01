{{-- <li class="nav-item">
    <a class="nav-link" href="{{ route('clients.index') }}">
        <i class="ti-control-record"></i>{{ __('Central Data') }}
    </a>
</li> --}}

@can('view Inquiries')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inquiries.index') }}">
            <i class="ti-control-record"></i>{{ __('Inquiries') }}
        </a>
    </li>
@endcan

@can('view My Drafts')
    <li>
        <a class="dropdown-item" href="{{ route('inquiries.drafts') }}">
            <i class="fas fa-file-alt me-2"></i>
            {{ __('My Drafts') }}
            @php
                $draftCount = \Modules\Inquiries\Models\Inquiry::myDrafts()->count();
            @endphp
            @if ($draftCount > 0)
                <span class="badge bg-warning text-dark ms-2">{{ $draftCount }}</span>
            @endif
        </a>
    </li>
@endcan

@can('view Inquiries Source')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inquiry.sources.index') }}">
            <i class="ti-control-record"></i>{{ __('Inquiries Source') }}
        </a>
    </li>
@endcan

@can('view Work Types')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('work.types.index') }}">
            <i class="ti-control-record"></i>{{ __('Work Types') }}
        </a>
    </li>
@endcan

@can('view Difficulty Matrix')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('difficulty-matrix.create') }}">
            <i class="ti-control-record"></i>{{ __('Difficulty Matrix') }}
        </a>
    </li>
@endcan

@can('view Quotation Info')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quotation-info.create') }}">
            <i class="ti-control-record"></i>{{ __('Quotation Info') }}
        </a>
    </li>
@endcan

@can('view Documents')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inquiry.documents.index') }}">
            <i class="ti-control-record"></i>{{ __('Documents') }}
        </a>
    </li>
@endcan

@can('view Project Size')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('project-size.index') }}">
            <i class="ti-control-record"></i>{{ __('Project Size') }}
        </a>
    </li>
@endcan

@can('view Inquiries Roles')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inquiries-roles.index') }}">
            <i class="ti-control-record"></i>{{ __('Inquiries Roles') }}
        </a>
    </li>
@endcan

@can('view Inquiries Statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inquiries.dashboard.statistics') }}">
            <i class="ti-bar-chart"></i>{{ __('Inquiries Statistics') }}
        </a>
    </li>
@endcan
