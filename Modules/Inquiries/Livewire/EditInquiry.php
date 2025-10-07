<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use App\Enums\ClientType;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{City, Town, Client};
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\ClientCategory;
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Models\InquiryDocument;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus};
use Modules\Inquiries\Enums\{KonPriorityEnum, ProjectSizeEnum, ClientPriorityEnum, QuotationStateEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition, InquiryComment, QuotationType};

class EditInquiry extends Component
{
    use WithFileUploads;

    public $inquiryId;
    public $inquiry;

    // Multi-worktype selection (match CreateInquiry)
    public $selectedWorkTypes = [];
    public $currentWorkTypeSteps = [1 => null];
    public $currentWorkPath = [];

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

    public $type_note = null;

    public $submittalChecklist = [];
    public $workingConditions = [];

    // New properties for quotation types and units
    public $quotationTypes = [];
    public $selectedQuotationUnits = [];

    // New properties for client modal
    public $modalClientType = null;
    public $modalClientTypeLabel = '';
    public $clientCategories = [];
    public $newClient = [
        'cname' => '',
        'email' => '',
        'phone' => '',
        'phone2' => '',
        'company' => '',
        'address' => '',
        'address2' => '',
        'date_of_birth' => '',
        'national_id' => '',
        'contact_person' => '',
        'contact_phone' => '',
        'contact_relation' => '',
        'info' => '',
        'job' => '',
        'gender' => '',
        'is_active' => true,
        'type' => null,
        'client_category_id' => null,
    ];

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
        'openClientModal' => 'openClientModal',
    ];

    public function mount($id)
    {
        $this->inquiryId = $id;
        $this->inquiry = Inquiry::with([
            'submittalChecklists',
            'workConditions',
            'projectDocuments',
            'workType.ancestors',
            'inquirySource.ancestors',
            'comments.user',
            'quotationUnits',
            'media',
        ])->findOrFail($id);

        $this->loadInitialData();
        $this->populateFormData();
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
        $this->clientCategories = ClientCategory::all()->toArray();

        $this->quotationTypes = QuotationType::with('units')->orderBy('name')->get();

        $submittalsFromDB = SubmittalChecklist::all();
        $this->submittalChecklist = $submittalsFromDB->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'checked' => false,
            'value' => $item->score
        ])->toArray();

        $documentsFromDB = InquiryDocument::orderBy('name')->get();
        $this->projectDocuments = $documentsFromDB->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'checked' => false,
            'description' => ''
        ])->toArray();

        $conditionsFromDB = WorkCondition::all();
        $this->workingConditions = $conditionsFromDB->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'checked' => false,
            'options' => $item->options,
            'selectedOption' => null,
            'value' => $item->options ? 0 : $item->score,
            'default_score' => $item->score
        ])->toArray();
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

        if ($this->cityId) {
            $this->towns = Town::where('city_id', $this->cityId)->get()->toArray();
        }

        $this->loadExistingRelations();
        $this->loadMedia();
    }

    private function loadMedia()
    {
        $this->existingProjectImage = $this->inquiry->getFirstMedia('project-image');
        $this->existingDocuments = $this->inquiry->getMedia('inquiry-documents')->map(fn($media) => [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'size' => $media->size,
            'url' => $media->getUrl()
        ])->toArray();
    }

    private function loadExistingRelations()
    {
        // Work Types
        if ($this->inquiry->workType) {
            $workTypeHierarchy = $this->inquiry->workType->ancestors->pluck('id')->push($this->inquiry->work_type_id)->toArray();
            foreach ($workTypeHierarchy as $index => $typeId) {
                $this->currentWorkTypeSteps['step_' . ($index + 1)] = $typeId;
            }
            $this->dispatch('prepopulateWorkTypes', steps: $this->currentWorkTypeSteps, path: $this->currentWorkPath);
        }

        // Inquiry Sources
        if ($this->inquiry->inquirySource) {
            $inquirySourceHierarchy = $this->inquiry->inquirySource->ancestors->pluck('id')->push($this->inquiry->inquiry_source_id)->toArray();
            foreach ($inquirySourceHierarchy as $index => $sourceId) {
                $this->inquirySourceSteps['inquiry_source_step_' . ($index + 1)] = $sourceId;
            }
            $this->dispatch('prepopulateInquirySources', steps: $this->inquirySourceSteps, path: $this->selectedInquiryPath);
        }

        // Checklists
        $this->checkItems($this->submittalChecklist, $this->inquiry->submittalChecklists->pluck('id'));
        $this->checkItems($this->workingConditions, $this->inquiry->workConditions->pluck('id'));
        $this->checkItems($this->projectDocuments, $this->inquiry->projectDocuments->pluck('id'));

        // Quotation Units
        foreach ($this->inquiry->quotationUnits as $unit) {
            if (isset($unit->pivot->quotation_type_id)) {
                $this->selectedQuotationUnits[$unit->pivot->quotation_type_id][$unit->id] = true;
            }
        }

        // Comments
        $this->existingComments = $this->inquiry->comments->map(fn($comment) => [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'user_name' => $comment->user->name ?? 'Unknown',
            'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
        ])->toArray();
    }

    private function checkItems(&$list, $existingIds)
    {
        $existingIds = $existingIds->toArray();
        foreach ($list as $index => $item) {
            if (in_array($item['id'], $existingIds)) {
                $list[$index]['checked'] = true;
            }
        }
    }

    public function generateTenderId()
    {
        $workTypeName = $this->currentWorkPath ? end($this->currentWorkPath) : '';
        $cityName = $this->cityId ? City::find($this->cityId)?->title : '';
        $townName = $this->townId ? Town::find($this->townId)?->title : '';
        $this->tenderId = trim("{$this->tenderNo} - {$workTypeName} - {$cityName} - {$townName}", ' -');
    }

    public function handleItemSelected($data)
    {
        $wireModel = $data['wireModel'];
        $value = $data['value'];
        $this->{$wireModel} = $value;
    }

    public function removeDocumentFile($index)
    {
        unset($this->documentFiles[$index]);
        $this->documentFiles = array_values($this->documentFiles);
    }

    public function removeExistingDocument($mediaId)
    {
        $media = $this->inquiry->getMedia('inquiry-documents')->find($mediaId);
        if ($media) {
            $media->delete();
        }
        $this->loadMedia(); // Refresh the list
    }

    public function removeProjectImage()
    {
        if ($this->existingProjectImage) {
            $this->existingProjectImage->delete();
            $this->existingProjectImage = null;
        }
        if ($this->projectImage) {
            $this->projectImage = null;
        }
    }

    public function addWorkType()
    {
        if (!empty($this->currentWorkTypeSteps) && end($this->currentWorkTypeSteps)) {
            $this->selectedWorkTypes[] = [
                'steps' => $this->currentWorkTypeSteps,
                'path' => $this->currentWorkPath,
                'final_description' => ''
            ];
            $this->currentWorkTypeSteps = [1 => null];
            $this->currentWorkPath = [];
        }
        $this->dispatch('workTypeAdded');
    }

    public function removeWorkType($index)
    {
        unset($this->selectedWorkTypes[$index]);
        $this->selectedWorkTypes = array_values($this->selectedWorkTypes);
    }

    public function updatedCurrentWorkTypeSteps($value, $key)
    {
        $stepNum = (int) str_replace('step_', '', $key);
        $this->currentWorkTypeSteps = array_slice($this->currentWorkTypeSteps, 0, $stepNum, true);
        $this->currentWorkTypeSteps['step_' . $stepNum] = $value;

        if ($value) {
            $selectedWorkType = WorkType::where('is_active', true)->find($value);
            if ($selectedWorkType) {
                $this->currentWorkPath = array_slice($this->currentWorkPath, 0, $stepNum - 1, true);
                $this->currentWorkPath[$stepNum - 1] = $selectedWorkType->name;
            }
        } else {
            $this->currentWorkPath = array_slice($this->currentWorkPath, 0, $stepNum - 1, true);
        }
        $this->generateTenderId();
    }

    public function updatedInquirySourceSteps($value, $key)
    {
        $stepNum = (int) str_replace('inquiry_source_step_', '', $key);
        $this->inquirySourceSteps = array_slice($this->inquirySourceSteps, 0, $stepNum, true);
        $this->inquirySourceSteps['inquiry_source_step_' . $stepNum] = $value;

        if ($value) {
            $selectedInquirySource = InquirySource::where('is_active', true)->find($value);
            if ($selectedInquirySource) {
                $this->selectedInquiryPath = array_slice($this->selectedInquiryPath, 0, $stepNum - 1, true);
                $this->selectedInquiryPath[$stepNum - 1] = $selectedInquirySource->name;
            }
        } else {
            $this->selectedInquiryPath = array_slice($this->selectedInquiryPath, 0, $stepNum - 1, true);
        }
    }

    public function updatedCityId($value)
    {
        $this->townId = null;
        $this->towns = $value ? Town::where('city_id', $value)->get()->toArray() : [];
        $this->generateTenderId();
    }

    public function updatedProjectDocuments($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = $parts[0];
            $property = $parts[1];
            if (isset($this->projectDocuments[$index])) {
                $this->projectDocuments[$index][$property] = $value;
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
        $this->totalSubmittalScore = collect($this->submittalChecklist)->where('checked', true)->sum('value');
        $this->totalConditionsScore = collect($this->workingConditions)->where('checked', true)->sum('value');
        $this->totalScore = $this->totalSubmittalScore + $this->totalConditionsScore;

        if ($this->totalScore < 6) $this->projectDifficulty = 1;
        elseif ($this->totalScore <= 10) $this->projectDifficulty = 2;
        elseif ($this->totalScore <= 15) $this->projectDifficulty = 3;
        else $this->projectDifficulty = 4;
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
        if (str_starts_with($propertyName, 'submittalChecklist') || str_starts_with($propertyName, 'workingConditions')) {
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

    public function addTempComment()
    {
        $this->validate(['newTempComment' => 'required|string|min:3|max:1000']);
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
        $this->existingComments = array_filter($this->existingComments, fn($c) => $c['id'] != $commentId);
    }

    public function openClientModal($type)
    {
        $this->modalClientType = $type;
        $clientTypeEnum = ClientType::tryFrom($type);
        $this->modalClientTypeLabel = $clientTypeEnum ? $clientTypeEnum->label() : 'عميل';
        $this->resetClientForm();
        $this->newClient['type'] = $type;
        $this->resetValidation();
    }

    public function saveNewClient()
    {
        $this->validate([
            'newClient.cname' => 'required|string|max:255',
            'newClient.phone' => 'required|string|max:20',
            'newClient.email' => 'nullable|email|unique:clients,email',
            'newClient.gender' => 'required|in:male,female',
        ]);

        try {
            DB::beginTransaction();
            $client = Client::create(array_merge($this->newClient, [
                'created_by' => Auth::id(),
                'tenant' => Auth::user()->tenant ?? 0,
                'branch' => Auth::user()->branch ?? 0,
                'branch_id' => Auth::user()->branch_id ?? 1,
            ]));

            switch ($this->modalClientType) {
                case ClientType::Person->value:
                case ClientType::Company->value:
                    $this->clientId = $client->id;
                    break;
                case ClientType::MainContractor->value:
                    $this->mainContractorId = $client->id;
                    break;
                case ClientType::Consultant->value:
                    $this->consultantId = $client->id;
                    break;
                case ClientType::Owner->value:
                    $this->ownerId = $client->id;
                    break;
                case ClientType::ENGINEER->value:
                    $this->assignedEngineer = $client->id;
                    break;
            }

            DB::commit();
            $this->dispatch('closeClientModal');
            $this->refreshClientLists();
            session()->flash('message', 'تم إضافة ' . $this->modalClientTypeLabel . ' بنجاح');
            $this->resetClientForm();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في إضافة عميل: ' . $e->getMessage());
            session()->flash('error', 'حدث خطأ أثناء إضافة ' . $this->modalClientTypeLabel . ': ' . $e->getMessage());
        }
    }

    private function refreshClientLists()
    {
        $this->clients = Client::whereIn('type', [ClientType::Person->value, ClientType::Company->value])->get()->toArray();
        $this->mainContractors = Client::where('type', ClientType::MainContractor->value)->get()->toArray();
        $this->consultants = Client::where('type', ClientType::Consultant->value)->get()->toArray();
        $this->owners = Client::where('type', ClientType::Owner->value)->get()->toArray();
        $this->engineers = Client::where('type', ClientType::ENGINEER->value)->get()->toArray();
        $this->clientCategories = ClientCategory::all()->toArray();
    }

    private function resetClientForm()
    {
        $this->newClient = array_fill_keys(array_keys($this->newClient), '');
        $this->newClient['is_active'] = true;
    }

    public function save()
    {
        try {
            DB::beginTransaction();

            $this->inquiry->update([
                'inquiry_name' => $this->inquiryName,
                'project_id' => $this->projectId,
                'inquiry_date' => $this->inquiryDate,
                'req_submittal_date' => $this->reqSubmittalDate,
                'project_start_date' => $this->projectStartDate,
                'city_id' => $this->cityId,
                'town_id' => $this->townId,
                'town_distance' => $this->townDistance,
                'status' => $this->status,
                'status_for_kon' => $this->statusForKon,
                'kon_title' => $this->konTitle,
                'work_type_id' => !empty($this->currentWorkTypeSteps) ? end($this->currentWorkTypeSteps) : null,
                'final_work_type' => $this->finalWorkType,
                'inquiry_source_id' => !empty($this->inquirySourceSteps) ? end($this->inquirySourceSteps) : null,
                'final_inquiry_source' => $this->finalInquirySource,
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
                'type_note' => $this->type_note,
            ]);

            // Sync relations
            $this->inquiry->submittalChecklists()->sync(
                collect($this->submittalChecklist)->where('checked', true)->pluck('id')
            );
            $this->inquiry->workConditions()->sync(
                collect($this->workingConditions)->where('checked', true)->pluck('id')
            );

            // Sync project documents with description
            $this->inquiry->projectDocuments()->detach();
            foreach ($this->projectDocuments as $document) {
                if (!empty($document['checked'])) {
                    $this->inquiry->projectDocuments()->attach($document['id'], [
                        'description' => $document['description'] ?? null
                    ]);
                }
            }

            // Sync quotation units
            $attachments = [];
            if (!empty($this->selectedQuotationUnits)) {
                foreach ($this->selectedQuotationUnits as $typeId => $unitIds) {
                    if (!empty($unitIds)) {
                        foreach (array_keys($unitIds) as $unitId) {
                            if ($unitIds[$unitId]) {
                                $attachments[$unitId] = ['quotation_type_id' => $typeId];
                            }
                        }
                    }
                }
            }
            $this->inquiry->quotationUnits()->sync($attachments);

            // Save new temporary comments
            foreach ($this->tempComments as $tempComment) {
                InquiryComment::create([
                    'inquiry_id' => $this->inquiry->id,
                    'user_id' => Auth::id(),
                    'comment' => $tempComment['comment'],
                ]);
            }

            DB::commit();
            return redirect()->route('inquiries.index')->with('message', 'تم تحديث الاستفسار بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في تحديث الاستفسار: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
        }

    public function render()
    {
        return view('inquiries::livewire.edit-inquiry');
    }
}
