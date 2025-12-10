@can('view Installment Plans')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('installments.plans.index') }}">
            {{ __('View Installment Plans') }}
        </a>
    </li>
@endcan

@can('create Installment Plans')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('installments.plans.create') }}">
            {{ __('Add New Plan') }}
        </a>
    </li>
@endcan

@can('view Overdue Installments')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('installments.payments.overdue') }}">
            {{ __('Overdue Installments') }}
        </a>
    </li>
@endcan
