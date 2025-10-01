<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use App\Enums\ClientType;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\{City, Town, Client};
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus};
use Modules\Inquiries\Enums\{KonPriorityEnum, ProjectSizeEnum, ClientPriorityEnum, QuotationStateEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition, InquiryComment};

class EditInquiry extends Component
{
    use WithFileUploads;

    public $inquiryId;
    public $inquiry;

    public $workTypeSteps = [1 => null];
    public $inquirySourceSteps = [1 => null];
    public $selectedWorkPath = [];
    public $selectedInquiryPath = [];
    public $finalWorkType = '';
    public $finalInquirySource = '';

    public $projectId;
    public $inquiryDate;
    public $reqSubmittalDate;
    public $projectStartDate;
    public $cityId;
    public $townId;
    public $townDistance;

    public $status;
    public $statusForKon;
    public $konTitle;
    public $clientId;
    public $assignedEngineer;
    public $mainContractorId;
    public $consultantId;
    public $ownerId;
    public $isPriority = false;
    public $projectSize;
    public $tenderNo;
    public $tenderId;
    public $estimationStartDate;
    public $estimationFinishedDate;
    public $submittingDate;
    public $totalProjectValue;
    public $quotationStateReason;
    public $quotationState;

    public $totalSubmittalScore = 0;
    public $totalConditionsScore = 0;
    public $totalScore = 0;
    public $projectDifficulty = 1;

    public $documentFiles = [];
    public $existingDocuments = [];

    public $workTypes = [];
    public $inquirySources = [];
    public $projects = [];
    public $cities = [];
    public $towns = [];
    public $clients = [];
    public $mainContractors = [];
    public $consultants = [];
    public $owners = [];
    public $statusOptions = [];
    public $statusForKonOptions = [];
    public $konTitleOptions = [];
    public $projectSizeOptions = [];
    public $inquiryName;

    // Temporary Comments
    public $tempComments = [];
    public $newTempComment = '';
    public $existingComments = [];

    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];

    public $engineers = [];
    public $quotationStateOptions = [];

    public $projectDocuments = [
        ['name' => 'Soil report', 'checked' => false],
        ['name' => 'Arch. Drawing', 'checked' => false],
        ['name' => 'Str. Drawing', 'checked' => false],
        ['name' => 'Spacification', 'checked' => false],
        ['name' => 'Pile design', 'checked' => false],
        ['name' => 'shoring design', 'checked' => false],
        ['name' => 'other', 'checked' => false, 'description' => '']
    ];

    public $types = [
        ['name' => 'With material', 'checked' => false],
        ['name' => 'Without materials', 'checked' => false],
        ['name' => 'Rental only', 'checked' => false],
    ];

    public $units = [
        ['name' => 'Liner Meter', 'checked' => false],
        ['name' => 'Per pile', 'checked' => false],
        ['name' => 'LumpSum', 'checked' => false],
        ['name' => 'Per Month', 'checked' => false],
        ['name' => 'Per week', 'checked' => false],
        ['name' => 'Per hour', 'checked' => false],
    ];

    public $type_note = null;

    public $submittalChecklist = [];
    public $workingConditions = [];

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
    ];

    public function mount($id)
    {
        $this->inquiryId = $id;
        $this->inquiry = Inquiry::with([
            'submittalChecklists',
            'workConditions',
            'projectDocuments',
            'workType',
            'inquirySource',
            'comments.user'
        ])->findOrFail($id);

        $this->loadInitialData();
        $this->populateFormData();
        $this->buildWorkTypeHierarchy();
        $this->buildInquirySourceHierarchy();
        $this->loadExistingRelations();
        $this->loadExistingComments();
        $this->calculateScores();
    }

    public function handleItemSelected($data)
    {
        $wireModel = $data['wireModel'];
        $value = $data['value'];
        $this->{$wireModel} = $value;
    }

    private function loadInitialData()
    {
        $this->engineers = Client::where('type', ClientType::ENGINEER->value)->get()->toArray();
        $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->projectSizeOptions = ProjectSizeEnum::values();
        $this->workTypes = WorkType::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->inquirySources = InquirySource::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->projects = ProjectProgress::all()->toArray();
        $this->cities = City::all()->toArray();
        $this->clients = Client::whereIn('type', [ClientType::Person->value, ClientType::Company->value])->get()->toArray();
        $this->mainContractors = Client::where('type', ClientType::MainContractor->value)->get()->toArray();
        $this->consultants = Client::where('type', ClientType::Consultant->value)->get()->toArray();
        $this->owners = Client::where('type', ClientType::Owner->value)->get()->toArray();
        $this->statusOptions = Inquiry::getStatusOptions();
        $this->statusForKonOptions = Inquiry::getStatusForKonOptions();
        $this->konTitleOptions = Inquiry::getKonTitleOptions();
        $this->clientPriorityOptions = ClientPriorityEnum::values();
        $this->konPriorityOptions = KonPriorityEnum::values();

        $submittalsFromDB = SubmittalChecklist::all();
        $this->submittalChecklist = $submittalsFromDB->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'checked' => false,
                'value' => $item->score
            ];
        })->toArray();

        $conditionsFromDB = WorkCondition::all();
        $this->workingConditions = $conditionsFromDB->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'checked' => false,
                'options' => $item->options,
                'selectedOption' => null,
                'value' => $item->options ? 0 : $item->score,
                'default_score' => $item->score
            ];
        })->toArray();
    }

    private function populateFormData()
    {
        $inquiry = $this->inquiry;

        $this->inquiryName = $inquiry->inquiry_name;
        $this->projectId = $inquiry->project_id;
        $this->finalWorkType = $inquiry->final_work_type;
        $this->finalInquirySource = $inquiry->final_inquiry_source;
        $this->inquiryDate = $inquiry->inquiry_date?->format('Y-m-d');
        $this->reqSubmittalDate = $inquiry->req_submittal_date?->format('Y-m-d');
        $this->projectStartDate = $inquiry->project_start_date?->format('Y-m-d');
        $this->cityId = $inquiry->city_id;
        $this->townId = $inquiry->town_id;
        $this->townDistance = $inquiry->town_distance;
        $this->status = $inquiry->status?->value;
        $this->statusForKon = $inquiry->status_for_kon?->value;
        $this->konTitle = $inquiry->kon_title?->value;
        $this->clientId = $inquiry->client_id;
        $this->mainContractorId = $inquiry->main_contractor_id;
        $this->consultantId = $inquiry->consultant_id;
        $this->ownerId = $inquiry->owner_id;
        $this->assignedEngineer = $inquiry->assigned_engineer_id;
        $this->clientPriority = $inquiry->client_priority;
        $this->konPriority = $inquiry->kon_priority;
        $this->projectSize = $inquiry->project_size;
        $this->quotationState = $inquiry->quotation_state;
        $this->tenderNo = $inquiry->tender_number;
        $this->tenderId = $inquiry->tender_id;
        $this->estimationStartDate = $inquiry->estimation_start_date?->format('Y-m-d');
        $this->estimationFinishedDate = $inquiry->estimation_finished_date?->format('Y-m-d');
        $this->submittingDate = $inquiry->submitting_date?->format('Y-m-d');
        $this->totalProjectValue = $inquiry->total_project_value;
        $this->quotationStateReason = $inquiry->rejection_reason;
        $this->type_note = $inquiry->type_note;

        // Load types
        $savedTypes = json_decode($inquiry->types, true) ?? [];
        foreach ($this->types as $index => $type) {
            if (in_array($type['name'], $savedTypes)) {
                $this->types[$index]['checked'] = true;
            }
        }

        // Load units
        $savedUnits = json_decode($inquiry->unit, true) ?? [];
        foreach ($this->units as $index => $unit) {
            if (in_array($unit['name'], $savedUnits)) {
                $this->units[$index]['checked'] = true;
            }
        }

        // Load existing documents
        $this->existingDocuments = $inquiry->getMedia('inquiry-documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl()
            ];
        })->toArray();

        if ($this->cityId) {
            $this->towns = Town::where('city_id', $this->cityId)->get()->toArray();
        }
    }

    private function buildWorkTypeHierarchy()
    {
        if (!$this->inquiry->work_type_id) {
            return;
        }

        $workType = $this->inquiry->workType;
        $hierarchy = [];
        $current = $workType;

        while ($current) {
            array_unshift($hierarchy, $current);
            $current = $current->parent_id ? WorkType::find($current->parent_id) : null;
        }

        foreach ($hierarchy as $index => $type) {
            $stepNum = $index + 1;
            $this->workTypeSteps[$stepNum] = $type->id;
            $this->selectedWorkPath[$index] = $type->name;
        }
    }

    private function buildInquirySourceHierarchy()
    {
        if (!$this->inquiry->inquiry_source_id) {
            return;
        }

        $inquirySource = $this->inquiry->inquirySource;
        $hierarchy = [];
        $current = $inquirySource;

        while ($current) {
            array_unshift($hierarchy, $current);
            $current = $current->parent_id ? InquirySource::find($current->parent_id) : null;
        }

        foreach ($hierarchy as $index => $source) {
            $stepNum = $index + 1;
            $this->inquirySourceSteps[$stepNum] = $source->id;
            $this->selectedInquiryPath[$index] = $source->name;
        }
    }

    private function loadExistingRelations()
    {
        $existingSubmittals = $this->inquiry->submittalChecklists->pluck('id')->toArray();
        foreach ($this->submittalChecklist as $index => $item) {
            if (in_array($item['id'], $existingSubmittals)) {
                $this->submittalChecklist[$index]['checked'] = true;
            }
        }

        $existingConditions = $this->inquiry->workConditions->keyBy('id');
        foreach ($this->workingConditions as $index => $condition) {
            if ($existingConditions->has($condition['id'])) {
                $this->workingConditions[$index]['checked'] = true;
                $existingCondition = $existingConditions->get($condition['id']);

                if (isset($condition['options'])) {
                    $this->workingConditions[$index]['selectedOption'] = $existingCondition->pivot->value ?? $existingCondition->score;
                    $this->workingConditions[$index]['value'] = $existingCondition->pivot->value ?? $existingCondition->score;
                } else {
                    $this->workingConditions[$index]['value'] = $existingCondition->score;
                }
            }
        }

        $existingDocuments = $this->inquiry->projectDocuments;
        foreach ($this->projectDocuments as $index => $document) {
            $found = $existingDocuments->where('name', $document['name'])->first();
            if ($found) {
                $this->projectDocuments[$index]['checked'] = true;
                if ($document['name'] === 'other' && $found->pivot->description) {
                    $this->projectDocuments[$index]['description'] = $found->pivot->description;
                }
            }
        }
    }

    private function loadExistingComments()
    {
        $this->existingComments = $this->inquiry->comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_name' => $comment->user->name ?? 'Unknown',
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function addTempComment()
    {
        $this->validate([
            'newTempComment' => 'required|string|min:3|max:1000',
        ]);

        $this->tempComments[] = [
            'comment' => $this->newTempComment,
            'user_name' => Auth::user()->name,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $this->newTempComment = '';
    }

    public function removeTempComment($index)
    {
        unset($this->tempComments[$index]);
        $this->tempComments = array_values($this->tempComments);
    }

    public function removeExistingComment($commentId)
    {
        InquiryComment::where('id', $commentId)->delete();
        $this->existingComments = array_filter($this->existingComments, function ($comment) use ($commentId) {
            return $comment['id'] != $commentId;
        });
        session()->flash('message', 'تم حذف التعليق بنجاح!');
        session()->flash('alert-type', 'success');
    }

    public function removeDocumentFile($index)
    {
        unset($this->documentFiles[$index]);
        $this->documentFiles = array_values($this->documentFiles);
    }

    public function removeExistingDocument($documentId)
    {
        $media = $this->inquiry->getMedia('inquiry-documents')->where('id', $documentId)->first();
        if ($media) {
            $media->delete();
            $this->existingDocuments = array_filter($this->existingDocuments, function ($doc) use ($documentId) {
                return $doc['id'] != $documentId;
            });
            session()->flash('message', 'تم حذف الملف بنجاح!');
            session()->flash('alert-type', 'success');
        }
    }

    public function updatedWorkTypeSteps($value, $key)
    {
        $stepNum = (int) str_replace('step_', '', $key);
        $this->workTypeSteps = array_slice($this->workTypeSteps, 0, $stepNum + 1, true);

        if ($value) {
            $selectedWorkType = WorkType::where('is_active', true)->find($value);
            if ($selectedWorkType) {
                $this->selectedWorkPath = array_slice($this->selectedWorkPath, 0, $stepNum, true);
                $this->selectedWorkPath[$stepNum] = $selectedWorkType->name;
            }
        } else {
            $this->selectedWorkPath = array_slice($this->selectedWorkPath, 0, $stepNum, true);
        }
    }

    public function updatedInquirySourceSteps($value, $key)
    {
        $stepNum = (int) str_replace('inquiry_source_step_', '', $key);
        $this->inquirySourceSteps = array_slice($this->inquirySourceSteps, 0, $stepNum + 1, true);

        if ($value) {
            $selectedInquirySource = InquirySource::where('is_active', true)->find($value);
            if ($selectedInquirySource) {
                $this->selectedInquiryPath = array_slice($this->selectedInquiryPath, 0, $stepNum, true);
                $this->selectedInquiryPath[$stepNum] = $selectedInquirySource->name;
            }
        } else {
            $this->selectedInquiryPath = array_slice($this->selectedInquiryPath, 0, $stepNum, true);
        }
    }

    public function updatedCityId($value)
    {
        $this->townId = null;
        $this->towns = $value ? Town::where('city_id', $value)->get()->toArray() : [];
    }

    public function updatedProjectDocuments($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = $parts[0];
            $property = $parts[1];
            if (isset($this->projectDocuments[$index])) {
                $this->projectDocuments[$index][$property] = $value;
                if ($this->projectDocuments[$index]['name'] === 'other' && $property === 'checked' && !$value) {
                    $this->projectDocuments[$index]['description'] = '';
                }
            }
        }
    }

    public function updatedWorkingConditions($value, $key)
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $property = $parts[1] ?? 'checked';

        if ($property === 'checked') {
            if (!$this->workingConditions[$index]['checked']) {
                $this->workingConditions[$index]['selectedOption'] = null;
                $this->workingConditions[$index]['value'] = 0;
            } else {
                if (isset($this->workingConditions[$index]['options'])) {
                    if (!$this->workingConditions[$index]['selectedOption']) {
                        $firstOption = array_values($this->workingConditions[$index]['options'])[0];
                        $this->workingConditions[$index]['selectedOption'] = $firstOption;
                        $this->workingConditions[$index]['value'] = $firstOption;
                    }
                } else {
                    $this->workingConditions[$index]['value'] = $this->workingConditions[$index]['default_score'] ?? 0;
                }
            }
        } elseif ($property === 'selectedOption') {
            $this->workingConditions[$index]['value'] = $value;
        }

        $this->calculateScores();
    }

    public function calculateScores()
    {
        $this->totalSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            if ($item['checked']) {
                $this->totalSubmittalScore += (int) ($item['value'] ?? 0);
            }
        }

        $this->totalConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked']) {
                $this->totalConditionsScore += (int) ($condition['value'] ?? 0);
            }
        }

        $this->totalScore = $this->totalSubmittalScore + $this->totalConditionsScore;

        if ($this->totalScore < 6) {
            $this->projectDifficulty = 1;
        } elseif ($this->totalScore <= 10) {
            $this->projectDifficulty = 2;
        } elseif ($this->totalScore <= 15) {
            $this->projectDifficulty = 3;
        } else {
            $this->projectDifficulty = 4;
        }
    }

    public function updatedSubmittalChecklist($value, $key)
    {
        $this->calculateScores();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'townId' && $this->townId) {
            $town = Town::find($this->townId);
            $this->townDistance = $town?->distance;
        }

        if (
            strpos($propertyName, 'submittalChecklist') !== false ||
            strpos($propertyName, 'workingConditions') !== false
        ) {
            $this->calculateScores();
        }
    }

    public function emitWorkTypeChildren($stepNum, $parentId)
    {
        $children = $parentId ? WorkType::where('parent_id', $parentId)->where('is_active', true)->get()->toArray() : [];
        $this->dispatch('workTypeChildrenLoaded', stepNum: $stepNum, children: $children);
    }

    public function emitInquirySourceChildren($stepNum, $parentId)
    {
        $children = $parentId ? InquirySource::where('parent_id', $parentId)->where('is_active', true)->get()->toArray() : [];
        $this->dispatch('inquirySourceChildrenLoaded', stepNum: $stepNum, children: $children);
    }

    public function save()
    {
        $this->validate([
            'projectId' => 'required|exists:projects,id',
            'inquiryDate' => 'required|date',
            'reqSubmittalDate' => 'nullable|date',
            'projectStartDate' => 'nullable|date',
            'cityId' => 'nullable|exists:cities,id',
            'townId' => 'nullable|exists:towns,id',
            'status' => 'required',
            'statusForKon' => 'nullable',
            'konTitle' => 'required',
            'clientId' => 'nullable|exists:clients,id',
            'mainContractorId' => 'nullable|exists:clients,id',
            'consultantId' => 'nullable|exists:clients,id',
            'ownerId' => 'nullable|exists:clients,id',
            'assignedEngineer' => 'nullable|exists:clients,id',
            'projectSize' => 'nullable',
            'quotationState' => 'nullable',
            'clientPriority' => 'nullable',
            'konPriority' => 'nullable',
            'documentFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $selectedTypes = collect($this->types)->where('checked', true)->pluck('name')->toArray();
        $selectedUnits = collect($this->units)->where('checked', true)->pluck('name')->toArray();

        try {
            DB::beginTransaction();

            $this->inquiry->update([
                'inquiry_name' => $this->inquiryName ?: ('Inquiry-' . now()->format('YmdHis')),
                'project_id' => $this->projectId,
                'work_type_id' => !empty($this->workTypeSteps) ? end($this->workTypeSteps) : null,
                'final_work_type' => $this->finalWorkType,
                'inquiry_source_id' => !empty($this->inquirySourceSteps) ? end($this->inquirySourceSteps) : null,
                'final_inquiry_source' => $this->finalInquirySource,
                'inquiry_date' => $this->inquiryDate,
                'req_submittal_date' => $this->reqSubmittalDate,
                'project_start_date' => $this->projectStartDate,
                'city_id' => $this->cityId,
                'town_id' => $this->townId,
                'town_distance' => $this->townDistance,
                'status' => $this->status,
                'status_for_kon' => $this->statusForKon,
                'kon_title' => $this->konTitle,
                'client_id' => $this->clientId,
                'main_contractor_id' => $this->mainContractorId,
                'consultant_id' => $this->consultantId,
                'owner_id' => $this->ownerId,
                'assigned_engineer_id' => $this->assignedEngineer,
                'total_check_list_score' => $this->totalScore,
                'project_difficulty' => $this->projectDifficulty,
                'tender_number' => $this->tenderNo,
                'tender_id' => $this->tenderId,
                'estimation_start_date' => $this->estimationStartDate,
                'estimation_finished_date' => $this->estimationFinishedDate,
                'submitting_date' => $this->submittingDate,
                'total_project_value' => $this->totalProjectValue,
                'quotation_state' => $this->quotationState,
                'rejection_reason' => $this->quotationStateReason,
                'project_size' => $this->projectSize,
                'client_priority' => $this->clientPriority,
                'kon_priority' => $this->konPriority,
                'types' => json_encode($selectedTypes),
                'unit' => json_encode($selectedUnits),
                'type_note' => $this->type_note,
            ]);

            // Handle new document files
            if (!empty($this->documentFiles)) {
                foreach ($this->documentFiles as $file) {
                    $this->inquiry
                        ->addMedia($file->getRealPath())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inquiry-documents');
                }
            }

            // Sync submittal checklists
            $submittalIds = [];
            foreach ($this->submittalChecklist as $item) {
                if (!empty($item['checked']) && isset($item['id'])) {
                    $submittalIds[] = $item['id'];
                }
            }
            $this->inquiry->submittalChecklists()->sync($submittalIds);

            // Sync work conditions
            $conditionIds = [];
            foreach ($this->workingConditions as $condition) {
                if (!empty($condition['checked']) && isset($condition['id'])) {
                    $conditionIds[] = $condition['id'];
                }
            }
            $this->inquiry->workConditions()->sync($conditionIds);

            // Sync project documents
            $this->inquiry->projectDocuments()->detach();
            foreach ($this->projectDocuments as $document) {
                if (!empty($document['checked'])) {
                    $projectDocument = ProjectDocument::firstOrCreate(
                        ['name' => $document['name']]
                    );

                    $this->inquiry->projectDocuments()->attach($projectDocument->id, [
                        'description' => $document['description'] ?? null
                    ]);
                }
            }

            // Save new temporary comments
            foreach ($this->tempComments as $tempComment) {
                InquiryComment::create([
                    'inquiry_id' => $this->inquiry->id,
                    'user_id' => Auth::id(),
                    'comment' => $tempComment['comment'],
                ]);
            }

            DB::commit();

            session()->flash('message', 'تم تحديث الاستفسار بنجاح!');
            session()->flash('alert-type', 'success');

            return redirect()->route('inquiries.index');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('خطأ في تحديث الاستفسار: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('message', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
            session()->flash('alert-type', 'error');

            return back();
        }
    }

    public function render()
    {
        return view('inquiries::livewire.edit-inquiry');
    }
}
