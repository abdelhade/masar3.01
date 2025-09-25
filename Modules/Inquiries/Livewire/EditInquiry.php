<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use App\Enums\ClientType;
use Livewire\WithFileUploads;
use App\Models\{City, Town, Client};
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Enums\{KonPriorityEnum, ProjectSizeEnum, ClientPriorityEnum, QuotationStateEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition};

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

    public $totalSubmittalScore = 0;
    public $totalConditionsScore = 0;
    public $projectDifficulty = 1;
    public $documentFile;
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

    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];

    public $engineers = [];
    public $quotationStateOptions = [];
    public $quotationState;

    public $projectDocuments = [
        ['name' => 'Soil report', 'checked' => false],
        ['name' => 'Arch. Drawing', 'checked' => false],
        ['name' => 'Str. Drawing', 'checked' => false],
        ['name' => 'Spacification', 'checked' => false],
        ['name' => 'Pile design', 'checked' => false],
        ['name' => 'shoring design', 'checked' => false],
        ['name' => 'other', 'checked' => false, 'description' => '']
    ];

    public $submittalChecklist = [
        ['name' => 'Pre qualification', 'checked' => false, 'value' => 0],
        ['name' => 'Design', 'checked' => false, 'value' => 1],
        ['name' => 'MOS', 'checked' => false, 'value' => 0],
        ['name' => 'Material Submittal', 'checked' => false, 'value' => 1],
        ['name' => 'Methodology', 'checked' => false, 'value' => 1],
        ['name' => 'Time schedule', 'checked' => false, 'value' => 1],
        ['name' => 'Insurances', 'checked' => false, 'value' => 1],
        ['name' => 'Project team', 'checked' => false, 'value' => 1]
    ];

    public $workingConditions = [
        ['name' => 'Safety level', 'checked' => false, 'options' => ['Normal' => 1, 'Medium' => 2, 'High' => 3], 'selectedOption' => null, 'value' => 0],
        ['name' => 'Vendor list', 'checked' => false, 'value' => 1],
        ['name' => 'Consultant approval', 'checked' => false, 'value' => 1],
        ['name' => 'Machines approval', 'checked' => false, 'value' => 0],
        ['name' => 'Labours approval', 'checked' => false, 'value' => 0],
        ['name' => 'Security approvals', 'checked' => false, 'value' => 0],
        ['name' => 'Working Hours', 'checked' => false, 'options' => ['Normal(10hr/6 days)' => 1, 'Half week(8hr, 4day)' => 2, 'Half day(4hr/6days)' => 2, 'Half week-Half day(4hr/4day)' => 3], 'selectedOption' => null, 'value' => 0],
        ['name' => 'Night shift required', 'checked' => false, 'value' => 1],
        ['name' => 'Tight time schedule', 'checked' => false, 'value' => 1],
        ['name' => 'Remote Location', 'checked' => false, 'value' => 2],
        ['name' => 'Difficult Access Site', 'checked' => false, 'value' => 1],
        ['name' => 'Without advance payment', 'checked' => false, 'value' => 1],
        ['name' => 'Payment conditions', 'checked' => false, 'options' => ['CDC' => 0, 'PDC 30 days' => 1, 'PDC 90 days' => 2], 'selectedOption' => null, 'value' => 0]
    ];

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
    ];

    public function mount($id)
    {
        $this->inquiryId = $id;
        $this->inquiry = Inquiry::with([
            'submittalChecklists',
            'workConditions',
            'projectDocuments',
            'workType',
            'inquirySource'
        ])->findOrFail($id);

        $this->loadInitialData();
        $this->populateFormData();
        $this->buildWorkTypeHierarchy();
        $this->buildInquirySourceHierarchy();
        $this->loadExistingRelations();
        $this->calculateScores();
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

        // Load towns if city is selected
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
        // Load submittal checklists
        $existingSubmittals = $this->inquiry->submittalChecklists->pluck('name')->toArray();
        foreach ($this->submittalChecklist as $index => $item) {
            if (in_array($item['name'], $existingSubmittals)) {
                $this->submittalChecklist[$index]['checked'] = true;
            }
        }

        // Load work conditions
        $existingConditions = $this->inquiry->workConditions->keyBy('name');
        foreach ($this->workingConditions as $index => $condition) {
            if ($existingConditions->has($condition['name'])) {
                $this->workingConditions[$index]['checked'] = true;
                $existingCondition = $existingConditions->get($condition['name']);
                $this->workingConditions[$index]['value'] = $existingCondition->score ?? 0;

                // Handle options
                if (isset($condition['options'])) {
                    $this->workingConditions[$index]['selectedOption'] = $existingCondition->score ?? 0;
                }
            }
        }

        // Load project documents
        $existingDocuments = $this->inquiry->projectDocuments->pluck('name')->toArray();
        foreach ($this->projectDocuments as $index => $document) {
            if (in_array($document['name'], $existingDocuments)) {
                $this->projectDocuments[$index]['checked'] = true;
                // Load description for 'other' type
                if ($document['name'] === 'other') {
                    $otherDoc = $this->inquiry->projectDocuments->where('name', 'other')->first();
                    if ($otherDoc && $otherDoc->pivot->description) {
                        $this->projectDocuments[$index]['description'] = $otherDoc->pivot->description;
                    }
                }
            }
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
                    $this->workingConditions[$index]['value'] = $this->workingConditions[$index]['value'] ?? 0;
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
                $this->totalSubmittalScore += (int) $item['value'];
            }
        }

        $this->totalConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked']) {
                $this->totalConditionsScore += (int) ($condition['value'] ?? 0);
            }
        }

        $score = $this->totalConditionsScore;
        if ($score < 6) {
            $this->projectDifficulty = 1;
        } elseif ($score <= 10) {
            $this->projectDifficulty = 2;
        } elseif ($score <= 15) {
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

    public function removeExistingDocument($documentId)
    {
        $media = $this->inquiry->getMedia('inquiry-documents')->where('id', $documentId)->first();
        if ($media) {
            $media->delete();
            $this->existingDocuments = array_filter($this->existingDocuments, function ($doc) use ($documentId) {
                return $doc['id'] != $documentId;
            });
            session()->flash('message', 'تم حذف الملف بنجاح!');
        }
    }

    public function save()
    {
        // Validation can be added here

        $inquiry = $this->inquiry;

        $inquiry->update([
            'inquiry_name' => $this->inquiryName ?: ('Inquiry-' . now()->format('YmdHis')),
            'project_id' => $this->projectId,
            'work_type_id' => $this->workTypeSteps[array_key_last($this->workTypeSteps)] ?? null,
            'final_work_type' => $this->finalWorkType,
            'inquiry_source_id' => $this->inquirySourceSteps[array_key_last($this->inquirySourceSteps)] ?? null,
            'final_inquiry_source' => $this->finalInquirySource,
            'client_id' => $this->clientId,
            'main_contractor_id' => $this->mainContractorId,
            'consultant_id' => $this->consultantId,
            'owner_id' => $this->ownerId,
            'assigned_engineer_id' => $this->assignedEngineer,
            'city_id' => $this->cityId,
            'town_id' => $this->townId,
            'inquiry_date' => $this->inquiryDate,
            'req_submittal_date' => $this->reqSubmittalDate,
            'project_start_date' => $this->projectStartDate,
            'status' => $this->status,
            'status_for_kon' => $this->statusForKon,
            'kon_title' => $this->konTitle,
            'client_priority' => $this->clientPriority,
            'kon_priority' => $this->konPriority,
            'project_size' => $this->projectSize,
            'quotation_state' => $this->quotationState,
            'total_submittal_check_list_score' => $this->totalSubmittalScore,
            'total_work_conditions_score' => $this->totalConditionsScore,
            'project_difficulty' => $this->projectDifficulty,
            'tender_number' => $this->tenderNo,
            'tender_id' => $this->tenderId,
            'estimation_start_date' => $this->estimationStartDate,
            'estimation_finished_date' => $this->estimationFinishedDate,
            'submitting_date' => $this->submittingDate,
            'total_project_value' => $this->totalProjectValue,
            'rejection_reason' => in_array($this->quotationState, [
                QuotationStateEnum::REJECTED->value,
                QuotationStateEnum::RE_ESTIMATION->value,
            ]) ? $this->quotationStateReason : null,
        ]);

        // Handle new document upload
        if ($this->documentFile) {
            $inquiry
                ->addMedia($this->documentFile->getRealPath())
                ->usingFileName($this->documentFile->getClientOriginalName())
                ->toMediaCollection('inquiry-documents');
        }

        // Sync submittal checklists
        $inquiry->submittalChecklists()->detach();
        foreach ($this->submittalChecklist as $item) {
            if ($item['checked']) {
                $submittal = SubmittalChecklist::firstOrCreate(
                    ['name' => $item['name']],
                    ['score' => $item['value']]
                );
                $inquiry->submittalChecklists()->attach($submittal->id);
            }
        }

        // Sync work conditions
        $inquiry->workConditions()->detach();
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked']) {
                $workCondition = WorkCondition::firstOrCreate(
                    ['name' => $condition['name']],
                    ['score' => $condition['value'] ?? 0]
                );
                $inquiry->workConditions()->attach($workCondition->id);
            }
        }

        // Sync project documents
        $inquiry->projectDocuments()->detach();
        foreach ($this->projectDocuments as $document) {
            if ($document['checked']) {
                $projectDocument = ProjectDocument::firstOrCreate(
                    ['name' => $document['name']],
                    ['description' => $document['description'] ?? null]
                );
                $inquiry->projectDocuments()->attach($projectDocument->id, [
                    'description' => $document['description'] ?? null
                ]);
            }
        }

        session()->flash('message', 'تم تحديث الاستفسار بنجاح!');
        return redirect()->route('inquiries.index');
    }

    public function render()
    {
        return view('inquiries::livewire.edit-inquiry');
    }
}
