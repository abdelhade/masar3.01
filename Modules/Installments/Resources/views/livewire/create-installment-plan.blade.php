<form wire:submit.prevent="save">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Installment Setup') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Client Selection -->
                <div class="col-md-4 mb-3">
                    <label for="clientId" class="form-label">{{ __('Client (Account)') }}</label>
                    <select wire:model="clientId" id="clientId"
                        class="form-select @error('clientId') is-invalid @enderror">
                        <option value="">{{ __('Select Client') }}</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->cname }}</option>
                        @endforeach
                    </select>
                    @error('clientId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <!-- Total Amount -->
                <div class="col-md-3 mb-3">
                    <label for="totalAmount" class="form-label">{{ __('Total Amount Before Interest') }}</label>
                    <input type="number" step="0.01" wire:model.live.debounce.500ms="totalAmount" id="totalAmount"
                        class="form-control">
                </div>
                <!-- Down Payment -->
                <div class="col-md-3 mb-3">
                    <label for="downPayment" class="form-label">{{ __('Down Payment Amount') }}</label>
                    <input type="number" step="0.01" wire:model.live.debounce.500ms="downPayment" id="downPayment"
                        class="form-control">
                </div>
                <!-- Amount to be Installed -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Remaining Amount for Installment') }}</label>
                    <input type="text" value="{{ number_format($amountToBeInstalled, 2) }}" class="form-control"
                        readonly>
                </div>
            </div>
            <hr>
            <div class="row">
                <!-- Number of Installments -->
                <div class="col-md-3 mb-3">
                    <label for="numberOfInstallments" class="form-label">{{ __('Number of Installments') }}</label>
                    <input type="number" wire:model.live.debounce.500ms="numberOfInstallments"
                        id="numberOfInstallments" class="form-control">
                </div>
                <!-- Installment Amount -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Single Installment Amount') }}</label>
                    <input type="text" value="{{ number_format($installmentAmount, 2) }}" class="form-control"
                        readonly>
                </div>
                <!-- Start Date -->
                <div class="col-md-3 mb-3">
                    <label for="startDate" class="form-label">{{ __('First Installment Date') }}</label>
                    <input type="date" wire:model="startDate" id="startDate" class="form-control">
                </div>
                <!-- Interval -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Interval Between Installments') }}</label>
                    <select wire:model="intervalType" class="form-select">
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="daily">{{ __('Daily') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">{{ __('Save and Generate Installments') }}</button>
        </div>
    </div>
</form>
