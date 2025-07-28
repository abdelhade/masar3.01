<?php

use Livewire\Volt\Component;
use App\Models\NoteDetails;
use App\Models\Note;

new class extends Component {
    public $noteId;
    public $noteDetails;
    public $noteDetailsName;
    public $noteDetailsId;
    public $isNoteDetailsEdit = false;
    public $showModal = false;
    public $parentNoteName = '';

    protected function rules()
    {
        return [
            'noteDetailsName' => 'required|string|max:60|unique:note_details,name,' . $this?->noteDetailsId ?? '',
        ];
    }

    public function messages()
    {
        return [
            'noteDetailsName.required' => 'الاسم مطلوب',
            'noteDetailsName.string' => 'الاسم يجب أن يكون نص',
        ];
    }

    public function mount($noteId)
    {
        $this->noteId = $noteId;

        $this->noteDetails = NoteDetails::where('note_id', $noteId)->get();
        $parentNote = Note::find($this->noteId);
        if ($parentNote) {
            $this->parentNoteName = $parentNote->name;
        }
    }
    public function createNoteDetails()
    {
        $this->resetValidation('noteDetailsName');
        $this->reset(['noteDetailsName', 'noteDetailsId']);
        $this->isNoteDetailsEdit = false;
        $this->showModal = true;
        $this->dispatch('showNoteDetailsModal');
    }

    public function editNoteDetails(NoteDetails $noteDetails)
    {
        $this->noteDetailsId = $noteDetails->id;
        $this->noteDetailsName = $noteDetails->name;
        $this->isNoteDetailsEdit = true;
        $this->showModal = true;
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

            @can('إضافة ' . $parentNoteName)
                <button wire:click="createNoteDetails" type="button" class="btn btn-primary font-family-cairo fw-bold m-2">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </button>
            @endcan

            <div class="card">
                <div class="card-header">



                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped text-center mb-0" style="min-width: 1200px;">
                            <thead class="table-light align-middle">

                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">الاسم</th>
                                    @canany(['تعديل ' . $parentNoteName, 'حذف ' . $parentNoteName])
                                        <th class="font-family-cairo fw-bold">العمليات</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($noteDetails as $noteDetail)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $noteDetail->name }}</td>

                                        @canany(['تعديل ' . $parentNoteName, 'حذف ' . $parentNoteName])
                                            <td>
                                                @can('تعديل ' . $parentNoteName)
                                                    <a wire:click="editNoteDetails({{ $noteDetail->id }})">
                                                        <i class="las la-pen text-success font-20"></i>
                                                    </a>
                                                @endcan

                                                @can('حذف ' . $parentNoteName)
                                                    <a wire:click="deleteNoteDetails({{ $noteDetail->id }})"
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


    <!-- Note Details Modal -->
    <div class="modal fade" wire:ignore.self id="noteDetailsModal" tabindex="-1"
        aria-labelledby="noteDetailsModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="noteDetailsModalLabel">
                        {{ $isNoteDetailsEdit ? 'تعديل ' . ($noteDetailsName ?? '') : 'إضافة جديد الى ' . ($parentNoteName ?? '') }}
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
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
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
