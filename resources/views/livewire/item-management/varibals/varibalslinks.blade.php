<?php

use Livewire\Volt\Component;
use App\Models\Varibal;

new class extends Component {
    public $varibals;

    public function mount()
    {
        $this->varibals = Varibal::all()->pluck('name', 'id');
    }
}; ?>

<div>
    <!--  -->
    @foreach ($varibals as $varibalId => $name)

            <li class="nav-item">
                <a class="nav-link font-family-cairo fw-bold" href="{{ route('varibalValues.index', $varibalId) }}">
                    <i class="ti-control-record"></i>{{ $name }}
                </a>
            </li>
    @endforeach

</div>
