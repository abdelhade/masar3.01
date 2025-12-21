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
            $permission = match($noteId) {
                1 => 'view groups',
                2 => 'view Categories',
                default => 'view items',
            };
        @endphp

        @can($permission)
            <li class="nav-item">
                <a class="nav-link font-hold fw-bold" href="{{ route('notes.noteDetails', $noteId) }}">
                    <i class="ti-control-record"></i>{{ $name }}
                </a>
            </li>
        @endcan
    @endforeach

</div>
