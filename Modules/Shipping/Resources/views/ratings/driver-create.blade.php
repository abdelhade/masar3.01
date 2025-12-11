@extends('admin.dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">{{ __('Rate Driver') }}</h4>
                
                <div class="alert alert-info">
                    <p><strong>{{ __('Order') }}:</strong> {{ $order->order_number }}</p>
                    <p><strong>{{ __('Driver') }}:</strong> {{ $order->driver->name }}</p>
                    <p><strong>{{ __('Customer') }}:</strong> {{ $order->customer_name }}</p>
                </div>

                <form action="{{ route('orders.rate-driver.store', $order->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label">{{ __('Rating') }}</label>
                        <div class="rating-stars">
                            @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" required>
                            <label for="star{{ $i }}">
                                <i class="fas fa-star"></i>
                            </label>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Comment') }}</label>
                        <textarea name="comment" class="form-control" rows="4" 
                                  placeholder="{{ __('Share your experience...') }}"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('Submit Rating') }}</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    font-size: 2rem;
}
.rating-stars input {
    display: none;
}
.rating-stars label {
    color: #ddd;
    cursor: pointer;
    padding: 0 5px;
}
.rating-stars input:checked ~ label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #ffc107;
}
</style>
@endsection
