<?php

use Livewire\Volt\Component;
use App\Models\Note;

new class extends Component {
    public $notes;

    public function mount()
    {
        $this->notes = Note::all()->pluck('name', 'id');
    }
}; ?>

<div>
    <!--  -->
    @foreach ($notes as $noteId => $name)
        @php
            $permission = 'عرض ' . $name;
        @endphp

        @can($permission)
            <li class="nav-item">
                <a class="nav-link font-family-cairo fw-bold" href="{{ route('notes.noteDetails', $noteId) }}">
                    <i class="ti-control-record"></i>{{ $name }}
                </a>
            </li>
        @endcan
    @endforeach

</div>
