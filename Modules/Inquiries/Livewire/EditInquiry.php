<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{City, Town, Client};
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\ClientCategory;
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Enums\{KonPriorityEnum, ClientPriorityEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, InquiryComment, QuotationType, ProjectSize, WorkCondition, InquiryDocument};

class EditInquiry extends Component
{
    use WithFileUploads;

    public $inquiryId;
    public $inquiry;

    // Multi-worktype selection (match CreateInquiry)
    public $selectedWorkTypes = [];
    public $currentWorkTypeSteps = [1 => null];
    public $currentWorkPath = [];
    public $existingDocuments = [];

    public $selectedInquiryPath = [];
    public $inquirySourceSteps = [1 => null];
    public $finalWorkType = '';
    public $finalInquirySource = '';
    public $projectId;
    public $inquiryDate;
    public $reqSubmittalDate;
    public $projectStartDate;

    public $difficultyPercentage = 0;
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
    public $projectDifficulty = 1;
    public $documentFiles = [];

    public $totalScore = 0;

    public $modalClientType = null;
    public $modalClientTypeLabel = '';

    public $projectImage;
    public $existingProjectImage;

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

    public $tempComments = [];
    public $newTempComment = '';
    public $existingComments = [];

    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];

    public $clientCategories = [];
    public $engineers = [];
    public $quotationStateOptions = [];

    public $projectDocuments = [];

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
    ];

    public $type_note = null;

    public $submittalChecklist = [];
    public $workingConditions = [];

    public $quotationTypes = [];
    public $selectedQuotationUnits = [];

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
            'workType',
            'inquirySource',
            'comments.user',
            'quotationUnits',
            'media'
        ])->findOrFail($id);

        $this->loadInitialData();
        $this->populateFormData();
        $this->calculateScores();
    }

    private function loadInitialData()
    {
        $this->engineers = Client::with('clientType')->get()->toArray();
        $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->projectSizeOptions = ProjectSize::all();
        $this->inquiryDate = now()->format('Y-m-d');
        $this->workTypes = WorkType::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->inquirySources = InquirySource::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->projects = ProjectProgress::all()->toArray();
        $this->cities = City::all()->toArray();
        $this->towns = $this->cityId ? Town::where('city_id', $this->cityId)->get()->toArray() : [];
        $this->statusOptions = Inquiry::getStatusOptions();
        $this->statusForKonOptions = Inquiry::getStatusForKonOptions();
        $this->konTitleOptions = Inquiry::getKonTitleOptions();
        $this->clientPriorityOptions = ClientPriorityEnum::values();
        $this->konPriorityOptions = KonPriorityEnum::values();

        $this->quotationTypes = QuotationType::with('units')->orderBy('name')->get();

        $submittalsFromDB = SubmittalChecklist::all();
        $this->submittalChecklist = $submittalsFromDB->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'checked' => false,
                'value' => $item->score
            ];
        })->toArray();

        $documentsFromDB = InquiryDocument::orderBy('name')->get();
        $this->projectDocuments = $documentsFromDB->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'checked' => false,
                'description' => ''
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

        $this->clientCategories = ClientCategory::all()->toArray();
        $this->documentFiles = [];
    }

    private function populateFormData()
    {
        $inquiry = $this->inquiry;

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
        $this->projectSize = $inquiry->project_size_id;
        $this->quotationState = $inquiry->quotation_state;
        $this->tenderNo = $inquiry->tender_number;
        $this->tenderId = $inquiry->tender_id;
        $this->estimationStartDate = $inquiry->estimation_start_date?->format('Y-m-d');
        $this->estimationFinishedDate = $inquiry->estimation_finished_date?->format('Y-m-d');
        $this->submittingDate = $inquiry->submitting_date?->format('Y-m-d');
        $this->totalProjectValue = $inquiry->total_project_value;
        $this->quotationStateReason = $inquiry->rejection_reason;
        $this->type_note = $inquiry->type_note;

        // Load work type hierarchy
        if ($inquiry->work_type_id) {
            $this->buildWorkTypeHierarchy($inquiry->work_type_id);
        }

        // Load inquiry source hierarchy
        if ($inquiry->inquiry_source_id) {
            $this->buildInquirySourceHierarchy($inquiry->inquiry_source_id);
        }

        // Load checklists
        $this->checkItems($this->submittalChecklist, $inquiry->submittalChecklists->pluck('id'));
        $this->checkItems($this->workingConditions, $inquiry->workConditions->pluck('id'));
        $this->checkItems($this->projectDocuments, $inquiry->projectDocuments->pluck('id'));

        // Load quotation units
        foreach ($inquiry->quotationUnits as $unit) {
            if (isset($unit->pivot->quotation_type_id)) {
                $this->selectedQuotationUnits[$unit->pivot->quotation_type_id][$unit->id] = true;
            }
        }

        // Load existing comments
        $this->existingComments = $inquiry->comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_name' => $comment->user->name ?? 'Unknown',
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();


        if ($inquiry->workTypes->isNotEmpty()) {
            foreach ($inquiry->workTypes as $workType) {
                $hierarchyPath = json_decode($workType->pivot->hierarchy_path, true);

                if ($hierarchyPath && is_array($hierarchyPath)) {
                    // بناء المسار النصي
                    $pathNames = [];
                    foreach ($hierarchyPath as $stepId) {
                        $wt = WorkType::find($stepId);
                        if ($wt) {
                            $pathNames[] = $wt->name;
                        }
                    }

                    $this->selectedWorkTypes[] = [
                        'steps' => $hierarchyPath,
                        'path' => $pathNames,
                        'final_description' => $workType->pivot->description ?? ''
                    ];
                }
            }
        }

        // ⭐ تحميل الـ Work Type الرئيسي في الخطوة الحالية (لو موجود)
        if ($inquiry->work_type_id && empty($this->selectedWorkTypes)) {
            $this->buildWorkTypeHierarchy($inquiry->work_type_id);
        }
        // Load media
        $this->existingProjectImage = $inquiry->getFirstMedia('project-image');
        $this->existingDocuments = $inquiry->getMedia('inquiry-documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl()
            ];
        })->toArray();
    }

    private function buildWorkTypeHierarchy($workTypeId)
    {
        $workType = WorkType::find($workTypeId);
        if (!$workType) return;

        $hierarchy = collect();
        $current = $workType;

        while ($current) {
            $hierarchy->prepend($current);
            $current = $current->parent;
        }

        $this->currentWorkTypeSteps = [];
        $this->currentWorkPath = [];

        foreach ($hierarchy as $index => $type) {
            $stepNumber = $index + 1;
            $this->currentWorkTypeSteps[$stepNumber] = $type->id;
            $this->currentWorkPath[$index] = $type->name;
        }

        $this->dispatch('prepopulateWorkTypes', [
            'steps' => $this->currentWorkTypeSteps,
            'path' => $this->currentWorkPath
        ]);
    }

    private function buildInquirySourceHierarchy($inquirySourceId)
    {
        $inquirySource = InquirySource::find($inquirySourceId);
        if (!$inquirySource) return;

        $hierarchy = collect();
        $current = $inquirySource;

        while ($current) {
            $hierarchy->prepend($current);
            $current = $current->parent;
        }

        $this->inquirySourceSteps = [];
        $this->selectedInquiryPath = [];

        foreach ($hierarchy as $index => $source) {
            $stepNumber = $index + 1;
            $this->inquirySourceSteps[$stepNumber] = $source->id;
            $this->selectedInquiryPath[$index] = $source->name;
        }

        $this->dispatch('prepopulateInquirySources', [
            'steps' => $this->inquirySourceSteps,
            'path' => $this->selectedInquiryPath
        ]);
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
        $workTypeName = '';
        if (!empty($this->currentWorkPath)) {
            $workTypeName = end($this->currentWorkPath);
        } elseif (!empty($this->selectedWorkTypes)) {
            $workTypeName = end($this->selectedWorkTypes)['path']
                ? end(end($this->selectedWorkTypes)['path'])
                : '';
        }

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
        $this->existingDocuments = $this->inquiry->getMedia('inquiry-documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl()
            ];
        })->toArray();
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
        $this->currentWorkTypeSteps = array_slice($this->currentWorkTypeSteps, 0, $stepNum + 1, true);

        if ($value) {
            $selectedWorkType = WorkType::where('is_active', true)->find($value);
            if ($selectedWorkType) {
                $this->currentWorkPath = array_slice($this->currentWorkPath, 0, $stepNum, true);
                $this->currentWorkPath[$stepNum] = $selectedWorkType->name;
            }
        } else {
            $this->currentWorkPath = array_slice($this->currentWorkPath, 0, $stepNum, true);
        }
        $this->generateTenderId();
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
                if ($this->projectDocuments[$index]['name'] === 'other' && $property === 'checked' && !$value) {
                    $this->projectDocuments[$index]['description'] = '';
                }
            }
        }
    }

    public function calculateScores()
    {
        // Calculate total submittal score
        $this->totalSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            if ($item['checked'] ?? false) {
                $this->totalSubmittalScore += (int) ($item['value'] ?? 0);
            }
        }

        // Calculate total conditions score
        $this->totalConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked'] ?? false) {
                // Use selectedOption if available, otherwise fallback to value or default_score
                $score = (int) ($condition['selectedOption'] ?? $condition['value'] ?? $condition['default_score'] ?? 0);
                $this->totalConditionsScore += $score;
            }
        }

        // Calculate total score
        $this->totalScore = $this->totalSubmittalScore + $this->totalConditionsScore;

        // Calculate maximum submittal score
        $maxSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            $maxSubmittalScore += (int) ($item['value'] ?? 0);
        }

        // Calculate maximum conditions score
        $maxConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if (isset($condition['options']) && is_array($condition['options'])) {
                // Use the maximum value from options
                $maxConditionsScore += max(array_values($condition['options']));
            } else {
                // Fallback to default_score or value
                $maxConditionsScore += (int) ($condition['default_score'] ?? $condition['value'] ?? 0);
            }
        }

        // Total maximum score
        $maxTotalScore = $maxSubmittalScore + $maxConditionsScore;

        // Calculate percentage
        $this->difficultyPercentage = $maxTotalScore > 0 ? round(($this->totalScore / $maxTotalScore) * 100, 2) : 0;

        // Determine project difficulty based on percentage
        if ($this->difficultyPercentage < 25) {
            $this->projectDifficulty = 1; // Easy (< 25%)
        } elseif ($this->difficultyPercentage < 50) {
            $this->projectDifficulty = 2; // Medium (25% - 50%)
        } elseif ($this->difficultyPercentage < 75) {
            $this->projectDifficulty = 3; // Hard (50% - 75%)
        } else {
            $this->projectDifficulty = 4; // Very Hard (75%+)
        }
    }

    public function updatedWorkingConditions($value, $key)
    {
        $this->calculateScores();
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
    }

    public function openClientModal($type = null)
    {
        if (!$type) {
            // session()->flash('error', 'يرجى تحديد نوع العميل');
            return;
        }

        $clientTypes = [
            1 => 'Person',
            2 => 'Main Contractor',
            3 => 'Consultant',
            4 => 'Owner',
            5 => 'Engineer',
        ];

        if (!isset($clientTypes[$type])) {
            // session()->flash('error', 'نوع العميل غير صالح');
            return;
        }

        // تحقق إذا كان النوع موجود بناءً على العنوان
        $clientType = \Modules\CRM\Models\ClientType::firstOrCreate(
            ['title' => $clientTypes[$type]],
            [
                'id' => $type,
                'title' => $clientTypes[$type],
                'created_at' => now(),
                'updated_at' => now(),
                'branch_id' => Auth::user()->branch_id ?? 1,
            ]
        );

        $this->modalClientType = $clientType->id;
        $this->modalClientTypeLabel = $clientType->title;

        $this->newClient = [
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
            'client_category_id' => null,
            'is_active' => true,
            'client_type_id' => $clientType->id,
        ];

        $this->resetValidation();
        $this->dispatch('openClientModal');
    }

    public function saveNewClient()
    {
        $this->validate([
            'newClient.cname' => 'required|string|max:255',
            'newClient.phone' => 'required|string|max:20',
            'newClient.email' => 'nullable|email|unique:clients,email',
            'newClient.gender' => 'required|in:male,female',
            'modalClientType' => 'required|integer|min:1',
        ], [
            'newClient.cname.required' => __('Client Name Required'),
            'newClient.phone.required' => __('Phone Number Required'),
            'newClient.email.email' => __('Invalid Email Format'),
            'newClient.email.unique' => __('Email Already In Use'),
            'modalClientType.required' => __('Client Type Required'),
            'modalClientType.integer' => __('Client Type Must Be Integer'),
        ]);

        try {
            DB::beginTransaction();

            $clientType = \Modules\CRM\Models\ClientType::findOrFail($this->modalClientType);

            $client = Client::create([
                'cname' => $this->newClient['cname'],
                'email' => $this->newClient['email'],
                'phone' => $this->newClient['phone'],
                'phone2' => $this->newClient['phone2'],
                'company' => $this->newClient['company'],
                'address' => $this->newClient['address'],
                'address2' => $this->newClient['address2'],
                'date_of_birth' => $this->newClient['date_of_birth'],
                'national_id' => $this->newClient['national_id'],
                'contact_person' => $this->newClient['contact_person'],
                'contact_phone' => $this->newClient['contact_phone'],
                'contact_relation' => $this->newClient['contact_relation'],
                'info' => $this->newClient['info'],
                'job' => $this->newClient['job'],
                'gender' => $this->newClient['gender'],
                'client_category_id' => $this->newClient['client_category_id'] ?? null,
                'is_active' => $this->newClient['is_active'] ?? true,
                'client_type_id' => $this->modalClientType,
                'created_by' => Auth::id(),
                'tenant' => Auth::user()->tenant ?? 0,
                'branch' => Auth::user()->branch ?? 0,
                'branch_id' => Auth::user()->branch_id ?? 1,
            ]);

            switch ($clientType->title) {
                case 'Person':
                case 'Company':
                    $this->clientId = $client->id;
                    break;
                case 'Main Contractor':
                    $this->mainContractorId = $client->id;
                    break;
                case 'Consultant':
                    $this->consultantId = $client->id;
                    break;
                case 'Owner':
                    $this->ownerId = $client->id;
                    break;
                case 'Engineer':
                    $this->assignedEngineer = $client->id;
                    break;
                default:
                    $this->clientId = $client->id;
            }

            DB::commit();

            $this->dispatch('closeClientModal');
            $this->refreshClientLists();

            session()->flash('message', __('Added Successfully', ['type' => $clientType->title]));

            $this->resetClientForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('Error Adding '));
        }
    }

    private function refreshClientLists()
    {
        $allClients = Client::with('clientType')->get()->toArray();

        $this->clients = $allClients;
        $this->mainContractors = $allClients;
        $this->consultants = $allClients;
        $this->owners = $allClients;
        $this->engineers = $allClients;
        $this->clientCategories = ClientCategory::all()->toArray();
    }

    private function resetClientForm()
    {
        $this->newClient = [
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
        ];
    }

    public function save()
    {
        try {
            DB::beginTransaction();

            $this->inquiry->update([
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

                'work_type_id' => $this->getMainWorkTypeId(),
                'final_work_type' => $this->finalWorkType,

                'inquiry_source_id' => !empty($this->inquirySourceSteps)
                    ? end($this->inquirySourceSteps)
                    : null,
                'final_inquiry_source' =>  $this->finalInquirySource,

                'client_id' => $this->clientId,
                'main_contractor_id' => $this->mainContractorId,
                'consultant_id' => $this->consultantId,
                'owner_id' => $this->ownerId,
                'assigned_engineer_id' => $this->assignedEngineer,

                'total_check_list_score' => $this->totalScore,
                'project_difficulty' => $this->projectDifficulty,

                'tender_number' => $this->tenderNo,
                'tender_id' => $this->tenderId,
                // 'difficulty_percentage' => $this->difficultyPercentage, // إضافة النسبة المئوية
                'estimation_start_date' => $this->estimationStartDate,
                'estimation_finished_date' => $this->estimationFinishedDate,
                'submitting_date' => $this->submittingDate,
                'total_project_value' => $this->totalProjectValue,

                'quotation_state' => $this->quotationState,
                'rejection_reason' => $this->quotationStateReason,

                'project_size_id' => $this->projectSize,

                'client_priority' => $this->clientPriority,
                'kon_priority' => $this->konPriority,

                'type_note' => $this->type_note,
            ]);

            $this->inquiry->workTypes()->detach();
            $this->saveAllWorkTypes($this->inquiry);

            // Handle project image
            if ($this->projectImage) {
                // Remove existing project image
                if ($this->existingProjectImage) {
                    $this->existingProjectImage->delete();
                }
                $this->inquiry
                    ->addMedia($this->projectImage->getRealPath())
                    ->usingFileName($this->projectImage->getClientOriginalName())
                    ->toMediaCollection('project-image');
            }

            // Handle document files
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

            // Sync working conditions
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
                    $projectDocument = InquiryDocument::firstOrCreate(
                        ['name' => $document['name']]
                    );

                    $this->inquiry->projectDocuments()->attach($projectDocument->id, [
                        'description' => $document['description'] ?? null
                    ]);
                }
            }

            // Sync quotation units
            $attachments = [];
            if (!empty($this->selectedQuotationUnits) && is_array($this->selectedQuotationUnits)) {
                foreach ($this->selectedQuotationUnits as $typeId => $unitIds) {
                    if (!is_numeric($typeId) || (int)$typeId <= 0) {
                        continue;
                    }
                    $typeId = (int) $typeId;

                    if (!empty($unitIds) && is_array($unitIds)) {
                        foreach ($unitIds as $unitId => $isSelected) {
                            if ($isSelected === true || $isSelected === 1) {
                                if (is_numeric($unitId) && (int)$unitId > 0) {
                                    $unitId = (int) $unitId;
                                    $attachments[$unitId] = ['quotation_type_id' => $typeId];
                                }
                            }
                        }
                    }
                }
                if (!empty($attachments)) {
                    $this->inquiry->quotationUnits()->sync($attachments);
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
            $this->calculateScores();
            DB::commit();
            return redirect()->route('inquiries.index')->with('message', __('Inquiry Updated Success'));
        } catch (\Exception) {
            DB::rollBack();
            Log::error(__('Error Updating Inquiry: '));
            return back()->with('error', __('Error During Update: '));
        }
    }

    private function saveAllWorkTypes($inquiry)
    {
        $order = 0;

        // حفظ الـ Work Types المختارة والمضافة
        foreach ($this->selectedWorkTypes as $workType) {
            $lastStepId = end($workType['steps']);
            if ($lastStepId) {
                $inquiry->workTypes()->attach($lastStepId, [
                    'hierarchy_path' => json_encode($workType['steps']),
                    'description' => $workType['final_description'] ?? '',
                    'order' => $order++
                ]);
            }
        }

        // حفظ الـ Work Type الحالي (لو موجود ومختلف)
        if (!empty($this->currentWorkTypeSteps) && end($this->currentWorkTypeSteps)) {
            $currentLastId = end($this->currentWorkTypeSteps);

            // تحقق إنه مش موجود في المختارين
            $alreadyExists = collect($this->selectedWorkTypes)->contains(function ($wt) use ($currentLastId) {
                return end($wt['steps']) == $currentLastId;
            });

            if (!$alreadyExists) {
                $inquiry->workTypes()->attach($currentLastId, [
                    'hierarchy_path' => json_encode($this->currentWorkTypeSteps),
                    'description' => $this->finalWorkType ?? '',
                    'order' => $order
                ]);
            }
        }
    }

    private function getMainWorkTypeId()
    {
        // لو في work types مختارة، خد آخر واحد
        if (!empty($this->selectedWorkTypes)) {
            return end($this->selectedWorkTypes)['steps'][array_key_last(end($this->selectedWorkTypes)['steps'])];
        }

        // لو في current work type
        if (!empty($this->currentWorkTypeSteps)) {
            return end($this->currentWorkTypeSteps);
        }

        return null;
    }

    public function render()
    {
        return view('inquiries::livewire.edit-inquiry');
    }
}
