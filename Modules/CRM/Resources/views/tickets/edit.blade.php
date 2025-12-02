@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Ticket'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Tickets'), 'url' => route('tickets.index')],
            ['label' => $ticket->subject, 'url' => route('tickets.show', $ticket->id)],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Ticket') }}: {{ $ticket->subject }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.update', $ticket->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Client -->
                            <div class="col-md-6 mb-3">
                                <x-dynamic-search name="client_id" :label="__('Client')" column="cname"
                                    model="App\Models\Client" :placeholder="__('Search for client...')" :required="true" :class="'form-select'" 
                                    :selected-id="$ticket->client_id" :selected-name="$ticket->client->cname ?? ''" />
                            </div>

                            <!-- Assigned To -->
                            <div class="mb-3 col-lg-6">
                                <label for="assigned_to" class="form-label">{{ __('Assigned To') }}</label>
                                <select name="assigned_to" id="assigned_to" class="form-control">
                                    <option value="">{{ __('-- Select User --') }}</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('assigned_to', $ticket->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="mb-3 col-lg-12">
                                <label for="subject" class="form-label">{{ __('Subject') }}</label>
                                <input type="text" name="subject" id="subject" class="form-control"
                                    value="{{ old('subject', $ticket->subject) }}" required>
                                @error('subject')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3 col-lg-9">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description', $ticket->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <x-branches::branch-select :branches="$branches" :selected="$ticket->branch_id" />
                            </div>

                            <!-- Priority -->
                            <div class="mb-3 col-lg-6">
                                <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                <select name="priority" id="priority" class="form-control" required>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>
                                        {{ __('Low') }}</option>
                                    <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>
                                        {{ __('Medium') }}</option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>
                                        {{ __('High') }}</option>
                                </select>
                                @error('priority')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3 col-lg-6">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>
                                        {{ __('Open') }}</option>
                                    <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>
                                        {{ __('In Progress') }}</option>
                                    <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>
                                        {{ __('Resolved') }}</option>
                                    <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>
                                        {{ __('Closed') }}</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Save Buttons -->
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-main me-2">
                                <i class="las la-save"></i> {{ __('Update') }}
                            </button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
