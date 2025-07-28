<?php

use Livewire\Volt\Component;
use App\Models\Note;
use App\Models\NoteDetails;
use Illuminate\Support\Facades\Validator;

new class extends Component {
    public $notes;
    public $name;
    public $noteId;
    public $noteDetails = [];
    public $noteDetailsName;
    public $noteDetailsId;
    public $showModal = false;
    public $isEdit = false;
    public $isNoteDetailsEdit = false;

    public function rules()
    {
        return [
            'name' => 'required|string|max:60|unique:notes,name,' . $this->noteId,
            'noteDetailsName' => 'required|string|max:60|unique:note_details,name,' . $this->noteDetailsId,
        ];
    }

    public function mount()
    {
        $this->notes = Note::all();
    }

    public function create()
    {
        $this->resetValidation('name');
        $this->reset(['name', 'noteId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit(Note $note)
    {
        $this->resetValidation('name');
        $this->noteId = $note->id;
        $this->name = $note->name;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate(['name' => 'required|string|max:60|unique:notes,name,' . $this->noteId]);
        if ($this->isEdit) {
            Note::find($this->noteId)->update($validated);
            session()->flash('success', 'تم تحديث الملاحظة بنجاح');
        } else {
            Note::create([
                'name' => $this->name,
            ]);
            session()->flash('success', 'تم إضافة الملاحظة بنجاح');
        }

        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->notes = Note::latest()->get();
    }

    public function delete(Note $note)
    {
        try {
            if ($note->noteDetails->count() > 0) {
                session()->flash('error', 'لا يمكن حذف الملاحظة لأنه مرتبط بتفاصيل الملاحظة.');
                return;
            }
            $note->delete();
            session()->flash('success', 'تم حذف الملاحظة بنجاح');
            $this->notes = Note::latest()->get();
        } catch (\Exception $e) {
            session()->flash('error', 'لا يمكن حذف الملاحظة لأنه مرتبط بتفاصيل الملاحظة.');
        }
    }

    public function createNoteDetails(Note $note)
    {
        $this->noteId = $note->id;
        $this->resetValidation('noteDetailsName');
        $this->reset(['noteDetailsName', 'noteDetailsId']);
        $this->isNoteDetailsEdit = false;
        $this->showModal = true;
        $this->noteDetails = NoteDetails::where('note_id', $this->noteId)->get() ?? [];
        $this->dispatch('showNoteDetailsModal');
    }

    public function editNoteDetails(NoteDetails $noteDetails)
    {
        $this->noteDetailsId = $noteDetails->id;
        $this->noteDetailsName = $noteDetails->name;
        $this->isNoteDetailsEdit = true;
        $this->showModal = true;
        $this->noteDetails = NoteDetails::where('note_id', $this->noteId)->get() ?? [];
        $this->dispatch('showNoteDetailsModal');
    }

    public function saveNoteDetails()
    {
        // dd($this->noteDetailsName, $this->noteDetailsId,$this->noteId,$this->isNoteDetailsEdit);
        $validated = $this->validate(['noteDetailsName' => 'required|string|max:60|unique:note_details,name,' . $this->noteDetailsId]);
        if ($this->isNoteDetailsEdit) {
            $noteDetails = NoteDetails::find($this->noteDetailsId);
            $noteDetails->name = $this->noteDetailsName;
            $noteDetails->save();
            session()->flash('success', 'تم تحديث تفاصيل الملاحظة بنجاح');
        } else {
            NoteDetails::create([
                'name' => $this->noteDetailsName,
                'note_id' => $this->noteId,
            ]);
            session()->flash('success', 'تم إضافة تفاصيل الملاحظة بنجاح');
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->noteDetails = NoteDetails::where('note_id', $this->noteId)->get() ?? [];
        $this->reset(['noteId']);
    }

    public function deleteNoteDetails(NoteDetails $noteDetails)
    {
        $noteDetails->delete();
        session()->flash('success', 'تم حذف تفاصيل الملاحظة بنجاح');
        $this->noteDetails = NoteDetails::where('note_id', $this->noteId)->get() ?? [];
    }
}; ?>

<div>
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('error') }}
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    @can('إضافة المجموعات')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            {{ __('Add New') }}
                            <i class="fas fa-plus me-2"></i>
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">

                                <tr>

                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">الاسم</th>
                                    @canany(['حذف المجموعات', 'تعديل المجموعات'])
                                        <th class="font-family-cairo fw-bold">العمليات</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notes as $note)
                                    <tr>
                                        <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo text-center fw-bold">{{ $note->name }}</td>
                                        <td class="text-center">
                                            <a wire:click="edit({{ $note->id }})"><i
                                                    class="las la-pen btn btn-success font-20"></i></a>
                                            <a wire:click="createNoteDetails({{ $note->id }})"><i
                                                    class="las la-eye btn btn-info font-20"></i></a>
                                            <a wire:click="delete({{ $note->id }})"
                                                onclick="confirm('هل أنت متأكد من حذف هذا السعر؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash-alt btn btn-danger font-20"></i>
                                            </a>
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $note->name }}</td>
                                        @canany(['تعديل ' . $note->name, 'حذف ' . $note->name])
                                            <td>
                                                @can('تعديل ' . $note->name)
                                                    <a wire:click="edit({{ $note->id }})">
                                                        <i class="las la-pen text-success font-20"></i>
                                                    </a>
                                                @endcan

                                                @can('حذف ' . $note->name)
                                                    <a wire:click="delete({{ $note->id }})"
                                                        onclick="confirm('هل أنت متأكد من الحذف؟') || event.stopImmediatePropagation()">
                                                        <i class="las la-trash-alt text-danger font-20"></i>
                                                    </a>
                                                @endcan


                                            </td>
                                        @endcanany
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div class="modal fade" wire:ignore.self id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="noteModalLabel">
                        {{ $isEdit ? 'تعديل ملاحظة' : 'إضافة ملاحظة جديدة' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">الاسم</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror font-family-cairo fw-bold"
                                id="name" wire:model="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Note Details Modal -->
    <div class="modal fade" wire:ignore.self id="noteDetailsModal" tabindex="-1"
        aria-labelledby="noteDetailsModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="noteDetailsModalLabel">
                        {{ $isEdit ? 'تعديل تفاصيل الملاحظة' : 'إضافة تفاصيل ملاحظة جديدة' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveNoteDetails" wire:ignore.self>
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">الاسم</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror font-family-cairo fw-bold"
                                id="noteDetailsName" wire:model="noteDetailsName">
                            @error('noteDetailsName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </div>
                    </form>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">الاسم</th>
                                    <th class="font-family-cairo fw-bold">العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($noteDetails as $noteDetail)
                                    <tr>
                                        <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo text-center fw-bold">{{ $noteDetail->name }}</td>
                                        <td class="text-center">
                                            <a wire:click="editNoteDetails({{ $noteDetail->id }})"
                                                class="btn btn-success btn-icon-square-sm "><i
                                                    class="las la-pen"></i></a>
                                            <a wire:click="deleteNoteDetails({{ $noteDetail->id }})"
                                                class="btn btn-danger btn-icon-square-sm"
                                                onclick="confirm('هل أنت متأكد من حذف هذا التفاصيل؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash-alt ></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty

                                    <tr>
                                        <td colspan="3"
                                                    class="text-center font-family-cairo fw-bold">لا يوجد
                                                    تفاصيل
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
            const modalElement = document.getElementById('noteModal');
            const noteDetailsModalElement = document.getElementById('noteDetailsModal');

            Livewire.on('showModal', () => {
                if (modalElement) {
                    // Always get or create the instance for the specific modal
                    modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(
                        modalElement);
                    modalInstance.show();
                }
            });

            Livewire.on('showNoteDetailsModal', () => {
                if (noteDetailsModalElement) {
                    // Always get or create the instance for the specific modal
                    modalInstance = bootstrap.Modal.getInstance(noteDetailsModalElement) || new bootstrap
                        .Modal(noteDetailsModalElement);
                    modalInstance.show();
                }
            });

            Livewire.on('closeModal', () => {
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    // 'this' refers to modalElement here
                    const bsInstance = bootstrap.Modal.getInstance(this);
                    // Only nullify modalInstance if it was tracking this specific modal
                    if (modalInstance === bsInstance) {
                        modalInstance = null;
                    }
                });
            }

            if (noteDetailsModalElement) {
                noteDetailsModalElement.addEventListener('hidden.bs.modal', function() {
                    // 'this' refers to noteDetailsModalElement here
                    const bsInstance = bootstrap.Modal.getInstance(this);
                    // Only nullify modalInstance if it was tracking this specific modal
                    if (modalInstance === bsInstance) {
                        modalInstance = null;
                    }
                });
            }
        });
    </script>
</div>
</div>
