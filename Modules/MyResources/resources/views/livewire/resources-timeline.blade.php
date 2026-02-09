<div>
    <div class="row mb-3">
        <div class="col-md-3">
            <input type="date" wire:model.live="startDate" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" wire:model.live="endDate" class="form-control">
        </div>
        <div class="col-md-3">
            <select wire:model.live="categoryFilter" class="form-control">
                <option value="">{{ __("All Categories") }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_ar }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="alert alert-info">
        {{ __("Timeline View for Resources - Can be developed later using JavaScript libraries") }}
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ __("Resource") }}</th>
                    <th>{{ __("Project") }}</th>
                    <th>{{ __("Start Date") }}</th>
                    <th>{{ __("End Date") }}</th>
                    <th>{{ __("Status") }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->resource->name }}</td>
                        <td>{{ $assignment->project->name }}</td>
                        <td>{{ $assignment->start_date->format('Y-m-d') }}</td>
                        <td>{{ $assignment->end_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $assignment->status->color() }}">
                                {{ $assignment->status->label() }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
