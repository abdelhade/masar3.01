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
        @can('view varibalsValues')
            <li class="nav-item">
                <a class="nav-link font-hold fw-bold" href="{{ route('varibalValues.index', $varibalId) }}">
                    <i class="ti-control-record"></i>{{ $name }}
                </a>
            </li>
        @endcan
    @endforeach

</div>
