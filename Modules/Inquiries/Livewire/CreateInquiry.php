<?php

namespace Modules\Inquiries\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\{City, Town};
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Inquiries\Models\ProjectSize;
use Modules\Inquiries\Models\PricingStatus;
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Models\InquiryDocument;
use Modules\Inquiries\Models\{Contact, InquirieRole};
use Modules\Inquiries\Services\DistanceCalculatorService;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus, KonPriorityEnum, ClientPriorityEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition, InquiryComment, QuotationType};

class CreateInquiry extends Component
{
    use WithFileUploads;

    public $inquiryId;
    public $isDraft = false;
    public $autoSaveEnabled = true;
    public $lastAutoSaveTime = null;

    public $selectedEngineers = [];
    public $availableEngineers = [];

    public $selectedWorkTypes = [];
    public $currentWorkTypeSteps = [1 => null];
    public $currentWorkPath = [];
    public $difficultyPercentage = 0;
    public $selectedInquiryPath = [];
    public $inquirySourceSteps = [1 => null];
    public $finalWorkType = '';
    public $finalInquirySource = '';
    public $projectId;
    public $inquiryDate;
    public $reqSubmittalDate;
    public $projectStartDate;

    // متغيرات الموقع
    public $fromLocation = 'Abu Dhabi, UAE';
    public $fromLocationLat = 24.45388;
    public $fromLocationLng = 54.37734;
    public $toLocation = '';
    public $toLocationLat = null;
    public $toLocationLng = null;
    public $calculatedDistance = null;
    public $calculatedDuration = null;
    public $showMapModal = false;
    public $mapModalType = '';
    public $cityId;
    public $townId;

    public $selectedRoles = [];

    // متغيرات المشروع
    public $assignEngineerDate;
    public $status;
    public $statusForKon;
    public $konTitle;
    public $isPriority = false;
    public $projectSize;
    public $tenderNo;
    public $tenderId;
    public $estimationStartDate;
    public $estimationFinishedDate;
    public $submittingDate;
    public $totalProjectValue;
    public $quotationStateReason;
    // public $quotationState;
    public $totalSubmittalScore = 0;
    public $totalConditionsScore = 0;
    public $projectDifficulty = 1;
    public $documentFiles = [];
    public $totalScore = 0;
    public $projectImage;

    // متغيرات Contacts (بدلاً من Clients)
    public $selectedContacts = [
        'client' => null,
        'main_contractor' => null,
        'consultant' => null,
        'owner' => null,
        'engineer' => null,
    ];

    // Modal للإضافة
    public $modalContactType = null;
    public $modalContactTypeLabel = '';
    public $newContact = [
        'name' => '',
        'email' => '',
        'phone_1' => '',
        'phone_2' => '',
        'type' => 'person',
        'address_1' => '',
        'address_2' => '',
        'tax_number' => '',
        'parent_id' => null,
        'notes' => '',
        'relatedContacts' => [],
    ];

    public $pricingStatuses = [];
    public $pricingStatusId;


    // البيانات المحملة
    public $contacts = [];
    public $inquirieRoles = [];
    public $workTypes = [];
    public $inquirySources = [];
    public $projects = [];
    public $cities = [];
    public $towns = [];
    public $statusOptions = [];
    public $statusForKonOptions = [];
    public $konTitleOptions = [];
    public $projectSizeOptions = [];
    public $tempComments = [];
    public $newTempComment = '';
    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];
    // public $quotationStateOptions = [];
    public $projectDocuments = [];
    public $type_note = null;
    public $submittalChecklist = [];
    public $workingConditions = [];
    public $quotationTypes = [];
    public $selectedQuotationUnits = [];

    protected $distanceCalculator;

    public function boot(DistanceCalculatorService $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
        'openContactModal' => 'openContactModal',
        'locationSelected' => 'handleLocationSelected',
    ];

    public function mount($inquiryId = null)
    {
        // تهيئة الأدوار
        $this->initializeRoles();

        // تحميل البيانات
        $this->loadInitialData();

        if ($inquiryId) {
            $this->loadDraft($inquiryId);
        } else {
            // تهيئة النماذج
            $this->initializeForms();
        }
        $this->availableEngineers = \App\Models\User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function saveAssignedEngineers($inquiry)
    {
        if (!empty($this->selectedEngineers) && is_array($this->selectedEngineers)) {
            $attachData = [];

            foreach ($this->selectedEngineers as $engineerId) {
                $attachData[$engineerId] = [
                    'assigned_at' => now(),
                    'notes' => null
                ];
            }

            $inquiry->assignedEngineers()->sync($attachData);
        }
    }

    public function removeEngineer($engineerId)
    {
        $this->selectedEngineers = array_values(
            array_filter($this->selectedEngineers, function ($id) use ($engineerId) {
                return $id != $engineerId;
            })
        );
    }

    private function loadDraft($inquiryId)
    {
        $inquiry = Inquiry::findOrFail($inquiryId);

        if (!$inquiry->is_draft) {
            abort(404, 'This is not a draft inquiry');
        }

        $this->inquiryId = $inquiry->id;
        $this->isDraft = true;

        // تحميل البيانات المحفوظة
        $draftData = $inquiry->draft_data ? json_decode($inquiry->draft_data, true) : [];

        // ملء الحقول من المسودة
        $this->fill([
            'projectId' => $inquiry->project_id,
            'inquiryDate' => $inquiry->inquiry_date?->format('Y-m-d'),
            'reqSubmittalDate' => $inquiry->req_submittal_date?->format('Y-m-d'),
            'projectStartDate' => $inquiry->project_start_date?->format('Y-m-d'),
            'status' => $inquiry->status->value,
            'statusForKon' => $inquiry->status_for_kon?->value,
            'konTitle' => $inquiry->kon_title?->value,
            'projectSize' => $inquiry->project_size_id,
            'tenderNo' => $inquiry->tender_number,
            'tenderId' => $inquiry->tender_id,
            'estimationStartDate' => $inquiry->estimation_start_date?->format('Y-m-d'),
            'estimationFinishedDate' => $inquiry->estimation_finished_date?->format('Y-m-d'),
            'submittingDate' => $inquiry->submitting_date?->format('Y-m-d'),
            'totalProjectValue' => $inquiry->total_project_value,
            'quotationState' => $inquiry->quotation_state?->value,
            'quotationStateReason' => $inquiry->rejection_reason,
            'clientPriority' => $inquiry->client_priority,
            'konPriority' => $inquiry->kon_priority,
            'type_note' => $inquiry->type_note,
            'cityId' => $inquiry->city_id,
            'townId' => $inquiry->town_id,
            'calculatedDistance' => $inquiry->town_distance,
        ]);

        // تحميل البيانات من draft_data
        if (!empty($draftData)) {
            $this->selectedWorkTypes = $draftData['selectedWorkTypes'] ?? [];
            $this->currentWorkTypeSteps = $draftData['currentWorkTypeSteps'] ?? [1 => null];
            $this->currentWorkPath = $draftData['currentWorkPath'] ?? [];
            $this->selectedInquiryPath = $draftData['selectedInquiryPath'] ?? [];
            $this->inquirySourceSteps = $draftData['inquirySourceSteps'] ?? [1 => null];
            $this->finalWorkType = $draftData['finalWorkType'] ?? '';
            $this->finalInquirySource = $draftData['finalInquirySource'] ?? '';
            $this->fromLocation = $draftData['fromLocation'] ?? 'Abu Dhabi, UAE';
            $this->fromLocationLat = $draftData['fromLocationLat'] ?? 24.45388;
            $this->fromLocationLng = $draftData['fromLocationLng'] ?? 54.37734;
            $this->toLocation = $draftData['toLocation'] ?? '';
            $this->toLocationLat = $draftData['toLocationLat'] ?? null;
            $this->toLocationLng = $draftData['toLocationLng'] ?? null;
            $this->calculatedDuration = $draftData['calculatedDuration'] ?? null;
            $this->submittalChecklist = $draftData['submittalChecklist'] ?? [];
            $this->workingConditions = $draftData['workingConditions'] ?? [];
            $this->projectDocuments = $draftData['projectDocuments'] ?? [];
            $this->selectedQuotationUnits = $draftData['selectedQuotationUnits'] ?? [];
            $this->tempComments = $draftData['tempComments'] ?? [];
        }

        // تحميل Contacts
        $this->loadContactsFromInquiry($inquiry);

        // إعادة حساب الدرجات
        if (empty($this->submittalChecklist)) {
            $this->initializeForms();
        } else {
            $this->calculateScores();
        }

        session()->flash('info', __('Draft loaded successfully. Continue editing or save as final.'));
    }

    private function loadContactsFromInquiry($inquiry)
    {
        $roleMap = [
            'Client' => 'client',
            'Main Contractor' => 'main_contractor',
            'Consultant' => 'consultant',
            'Owner' => 'owner',
            'Engineer' => 'engineer',
        ];

        foreach ($inquiry->contacts as $contact) {
            $role = $contact->pivot->role_id;
            $roleName = InquirieRole::find($role)?->name;

            if ($roleName && isset($roleMap[$roleName])) {
                $this->selectedContacts[$roleMap[$roleName]] = $contact->id;
            }
        }
    }

    public function saveAsDraft()
    {
        // dd($this->all());
        // try {
        //     DB::beginTransaction();

        $draftData = [
            'selectedWorkTypes' => $this->selectedWorkTypes,
            'currentWorkTypeSteps' => $this->currentWorkTypeSteps,
            'currentWorkPath' => $this->currentWorkPath,
            'selectedInquiryPath' => $this->selectedInquiryPath,
            'inquirySourceSteps' => $this->inquirySourceSteps,
            'finalWorkType' => $this->finalWorkType,
            'finalInquirySource' => $this->finalInquirySource,
            'fromLocation' => $this->fromLocation,
            'fromLocationLat' => $this->fromLocationLat,
            'fromLocationLng' => $this->fromLocationLng,
            'toLocation' => $this->toLocation,
            'toLocationLat' => $this->toLocationLat,
            'toLocationLng' => $this->toLocationLng,
            'calculatedDuration' => $this->calculatedDuration,
            'submittalChecklist' => $this->submittalChecklist,
            'workingConditions' => $this->workingConditions,
            'projectDocuments' => $this->projectDocuments,
            'selectedQuotationUnits' => $this->selectedQuotationUnits,
            'tempComments' => $this->tempComments,
        ];

        list($city, $town) = $this->storeLocationInDatabase();

        if ($this->inquiryId) {
            // تحديث المسودة الموجودة
            $inquiry = Inquiry::findOrFail($this->inquiryId);
            $inquiry->update([
                'project_id' => $this->projectId,
                'inquiry_date' => $this->inquiryDate,
                'req_submittal_date' => $this->reqSubmittalDate,
                'project_start_date' => $this->projectStartDate,
                'city_id' => $city->id ?? null,
                'town_id' => $town->id ?? null,
                'town_distance' => $this->calculatedDistance,
                'status' => $this->status,
                'status_for_kon' => $this->statusForKon,
                'kon_title' => $this->konTitle,
                'work_type_id' => $this->getMainWorkTypeId(),
                'final_work_type' => $this->finalWorkType,
                'inquiry_source_id' => !empty($this->inquirySourceSteps) ? end($this->inquirySourceSteps) : null,
                'final_inquiry_source' => $this->finalInquirySource,
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
                'project_size_id' => $this->projectSize,
                'client_priority' => $this->clientPriority,
                'kon_priority' => $this->konPriority,
                'type_note' => $this->type_note,
                'is_draft' => true,
                'draft_data' => json_encode($draftData),
                'last_draft_saved_at' => now(),
            ]);
        } else {
            // إنشاء مسودة جديدة
            $inquiry = Inquiry::create([
                'project_id' => $this->projectId,
                'inquiry_date' => $this->inquiryDate,
                'req_submittal_date' => $this->reqSubmittalDate,
                'project_start_date' => $this->projectStartDate,
                'city_id' => $city->id ?? null,
                'town_id' => $town->id ?? null,
                'town_distance' => $this->calculatedDistance,
                'status' => $this->status,
                'status_for_kon' => $this->statusForKon,
                'kon_title' => $this->konTitle,
                'work_type_id' => $this->getMainWorkTypeId(),
                'final_work_type' => $this->finalWorkType,
                'inquiry_source_id' => !empty($this->inquirySourceSteps) ? end($this->inquirySourceSteps) : null,
                'final_inquiry_source' => $this->finalInquirySource,
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
                'project_size_id' => $this->projectSize,
                'client_priority' => $this->clientPriority,
                'kon_priority' => $this->konPriority,
                'type_note' => $this->type_note,
                'is_draft' => true,
                'draft_data' => json_encode($draftData),
                'last_draft_saved_at' => now(),
            ]);

            $this->inquiryId = $inquiry->id;
            $this->isDraft = true;
        }

        // حفظ Contacts
        $inquiry->contacts()->detach(); // حذف القديم
        foreach ($this->selectedContacts as $roleKey => $contactId) {
            if ($contactId) {
                $roleMap = [
                    'client' => 'Client',
                    'main_contractor' => 'Main Contractor',
                    'consultant' => 'Consultant',
                    'owner' => 'Owner',
                    'engineer' => 'Engineer',
                ];

                $role = InquirieRole::where('name', $roleMap[$roleKey])->first();
                if ($role) {
                    $inquiry->contacts()->attach($contactId, ['role_id' => $role->id]);
                }
            }
        }

        // DB::commit();

        $this->lastAutoSaveTime = now()->format('H:i:s');
        session()->flash('success', __('Draft saved successfully at ') . $this->lastAutoSaveTime);

        $this->dispatch('draftSaved', ['inquiryId' => $inquiry->id]);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     session()->flash('error', __('Error saving draft: ') . $e->getMessage());
        // }
    }

    private function initializeRoles()
    {
        $roles = [
            ['name' => 'Client', 'description' => 'Project Client'],
            ['name' => 'Main Contractor', 'description' => 'Main Contractor'],
            ['name' => 'Consultant', 'description' => 'Project Consultant'],
            ['name' => 'Owner', 'description' => 'Project Owner'],
            ['name' => 'Engineer', 'description' => 'Assigned Engineer'],
        ];

        foreach ($roles as $role) {
            InquirieRole::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }

        $this->inquirieRoles = InquirieRole::all()->toArray();
    }

    private function loadInitialData()
    {
        $this->contacts = Contact::with(['roles', 'parent'])->get()->toArray();
        $this->pricingStatuses = PricingStatus::active()->get();

        // $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->projectSizeOptions = ProjectSize::pluck('name', 'id')->toArray();
        $this->inquiryDate = now()->format('Y-m-d');
        $this->workTypes = WorkType::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->inquirySources = InquirySource::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->projects = ProjectProgress::all()->toArray();
        $this->statusOptions = Inquiry::getStatusOptions();
        $this->statusForKonOptions = Inquiry::getStatusForKonOptions();
        $this->konTitleOptions = Inquiry::getKonTitleOptions();
        $this->clientPriorityOptions = ClientPriorityEnum::values();
        $this->konPriorityOptions = KonPriorityEnum::values();
        $this->status = InquiryStatus::JOB_IN_HAND->value;
        $this->statusForKon = StatusForKon::EXTENSION->value;
        $this->konTitle = KonTitle::MAIN_PILING_CONTRACTOR->value;

        $lastTender = Inquiry::latest('id')->first();
        $nextNumber = $lastTender ? $lastTender->id + 1 : 1;
        $this->tenderNo = 'T-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $this->quotationTypes = QuotationType::with('units')->orderBy('name')->get();
    }

    private function initializeForms()
    {
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

        $this->documentFiles = [];
        $this->calculateScores();
    }

    public function openContactModal($roleType = null)
    {
        if (!$roleType) {
            return;
        }

        $roleMap = [
            1 => 'Client',
            2 => 'Main Contractor',
            3 => 'Consultant',
            4 => 'Owner',
            5 => 'Engineer',
        ];

        if (!isset($roleMap[$roleType])) {
            return;
        }

        $role = InquirieRole::where('name', $roleMap[$roleType])->first();
        if (!$role) {
            return;
        }

        $this->modalContactType = $role->id;
        $this->modalContactTypeLabel = $role->name;
        $this->newContact = [
            'name' => '',
            'email' => '',
            'phone_1' => '',
            'phone_2' => '',
            'type' => 'person',
            'address_1' => '',
            'address_2' => '',
            'tax_number' => '',
            'parent_id' => null,
            'notes' => '',
            'relatedContacts' => [], // مهم: array فاضي
        ];
        $this->selectedRoles = []; // Reset selected roles
        $this->resetValidation();
        $this->dispatch('openContactModal');
    }


    public function saveNewContact()
    {
        $this->validate([
            'newContact.name' => 'required|string|max:255',
            'newContact.phone_1' => 'required|string|max:20',
            'newContact.email' => 'nullable|email|unique:contacts,email',
            'newContact.type' => 'required|in:person,company',
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:inquiries_roles,id',
            'newContact.relatedContacts' => 'nullable|array', // جديد
            'newContact.relatedContacts.*' => 'exists:contacts,id', // جديد
        ]);

        try {
            DB::beginTransaction();

            $contact = Contact::create([
                'name' => $this->newContact['name'],
                'email' => $this->newContact['email'],
                'phone_1' => $this->newContact['phone_1'],
                'phone_2' => $this->newContact['phone_2'],
                'type' => $this->newContact['type'],
                'address_1' => $this->newContact['address_1'],
                'address_2' => $this->newContact['address_2'],
                'tax_number' => $this->newContact['tax_number'],
                'notes' => $this->newContact['notes'],
            ]);

            // ربط الـ Roles
            $contact->roles()->attach($this->selectedRoles);

            // ربط الشركات أو الأشخاص (Many-to-Many) - جديد
            if (!empty($this->newContact['relatedContacts'])) {
                if ($this->newContact['type'] === 'person') {
                    // شخص يتبع لشركات
                    $contact->companies()->attach($this->newContact['relatedContacts']);
                } else {
                    // شركة لديها أشخاص
                    $contact->persons()->attach($this->newContact['relatedContacts']);
                }
            }

            // تحديد الخانة المناسبة للدور الأساسي
            $mainRole = InquirieRole::find($this->modalContactType);
            if ($mainRole) {
                $roleKey = match ($mainRole->name) {
                    'Client' => 'client',
                    'Main Contractor' => 'main_contractor',
                    'Consultant' => 'consultant',
                    'Owner' => 'owner',
                    'Engineer' => 'engineer',
                    default => 'client'
                };
                $this->selectedContacts[$roleKey] = $contact->id;
            }

            DB::commit();
            $this->dispatch('closeContactModal');
            $this->refreshContactsList();
            $this->dispatch('contactAdded');
            session()->flash('message', __('Added Successfully'));
            $this->resetContactForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('Error Adding Contact: ') . $e->getMessage());
        }
    }


    private function refreshContactsList()
    {
        $this->contacts = Contact::with(['roles', 'parent'])->get()->map(function ($contact) {
            $contactArray = $contact->toArray();
            // التأكد إن الـ roles و parent محملين صح
            $contactArray['roles'] = $contact->roles->toArray();
            if ($contact->parent) {
                $contactArray['parent'] = $contact->parent->toArray();
            }
            return $contactArray;
        })->toArray();
    }

    private function assignRoleToContact($contactId, $roleKey)
    {
        $contact = Contact::find($contactId);
        if (!$contact) {
            return;
        }

        $roleMap = [
            'client' => 'Client',
            'main_contractor' => 'Main Contractor',
            'consultant' => 'Consultant',
            'owner' => 'Owner',
            'engineer' => 'Engineer',
        ];

        $roleName = $roleMap[$roleKey] ?? null;
        if (!$roleName) {
            return;
        }

        $role = InquirieRole::where('name', $roleName)->first();
        if (!$role) {
            return;
        }

        // التحقق إذا كان الـ Contact لديه هذا الدور بالفعل
        if (!$contact->roles()->where('role_id', $role->id)->exists()) {
            // إضافة الدور للـ Contact
            $contact->roles()->attach($role->id);
        }
    }

    private function resetContactForm()
    {
        $this->newContact = [
            'name' => '',
            'email' => '',
            'phone_1' => '',
            'phone_2' => '',
            'type' => 'person',
            'address_1' => '',
            'address_2' => '',
            'tax_number' => '',
            'parent_id' => null,
            'notes' => '',
            'relatedContacts' => [],
        ];
        $this->modalContactType = null;
        $this->modalContactTypeLabel = '';
        $this->selectedRoles = [];
    }

    // عند اختيار Contact من القائمة
    public function updatedSelectedContacts($value, $key)
    {
        if (!$value) {
            return;
        }

        $contact = Contact::find($value);
        if (!$contact) {
            return;
        }

        // الحصول على الدور المناسب للخانة
        $roleMap = [
            'client' => 'Client',
            'main_contractor' => 'Main Contractor',
            'consultant' => 'Consultant',
            'owner' => 'Owner',
            'engineer' => 'Engineer',
        ];

        $roleName = $roleMap[$key] ?? null;
        if (!$roleName) {
            return;
        }

        $role = InquirieRole::where('name', $roleName)->first();
        if (!$role) {
            return;
        }

        // التحقق إذا كان الـ Contact لديه هذا الدور بالفعل
        if (!$contact->roles()->where('role_id', $role->id)->exists()) {
            // إضافة الدور للـ Contact
            $contact->roles()->attach($role->id);
        }
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

        $maxSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            $maxSubmittalScore += (int) ($item['value'] ?? 0);
        }

        $maxConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if (isset($condition['options'])) {
                $maxConditionsScore += max(array_values($condition['options']));
            } else {
                $maxConditionsScore += (int) ($condition['default_score'] ?? 0);
            }
        }

        $maxTotalScore = $maxSubmittalScore + $maxConditionsScore;
        $percentage = $maxTotalScore > 0 ? ($this->totalScore / $maxTotalScore) * 100 : 0;

        if ($percentage < 25) {
            $this->projectDifficulty = 1;
        } elseif ($percentage < 50) {
            $this->projectDifficulty = 2;
        } elseif ($percentage < 75) {
            $this->projectDifficulty = 3;
        } else {
            $this->projectDifficulty = 4;
        }

        $this->difficultyPercentage = round($percentage, 2);
    }

    public function save($saveType = 'final')
    {
        // dd($this->all());
        if ($saveType === 'draft') {
            return $this->saveAsDraft();
        }
        // try {
        //     DB::beginTransaction();

        list($city, $town) = $this->storeLocationInDatabase();

        $inquiry = Inquiry::create([
            'project_id' => $this->projectId,
            'inquiry_date' => $this->inquiryDate,
            'req_submittal_date' => $this->reqSubmittalDate,
            'project_start_date' => $this->projectStartDate,
            'city_id' => $city->id ?? null,
            'town_id' => $town->id ?? null,
            'pricing_status_id' => $this->pricingStatusId,
            'town_distance' => $this->calculatedDistance,
            'status' => $this->status,
            'status_for_kon' => $this->statusForKon,
            'kon_title' => $this->konTitle,
            'work_type_id' => $this->getMainWorkTypeId(),
            'final_work_type' => $this->finalWorkType,
            'inquiry_source_id' => !empty($this->inquirySourceSteps) ? end($this->inquirySourceSteps) : null,
            'final_inquiry_source' => $this->finalInquirySource,
            'total_check_list_score' => $this->totalScore,
            'project_difficulty' => $this->projectDifficulty,
            'tender_number' => $this->tenderNo,
            'tender_id' => $this->tenderId,
            'estimation_start_date' => $this->estimationStartDate,
            'estimation_finished_date' => $this->estimationFinishedDate,
            'submitting_date' => $this->submittingDate,
            'total_project_value' => $this->totalProjectValue,
            // 'quotation_state' => $this->quotationState,
            'rejection_reason' => $this->quotationStateReason,
            'assigned_engineer_date' => $this->assignEngineerDate,
            'project_size_id' => $this->projectSize,
            'client_priority' => $this->clientPriority,
            'kon_priority' => $this->konPriority,
            'type_note' => $this->type_note,
            'created_by' => Auth::id()
        ]);

        // حفظ Contacts مع أدوارهم
        foreach ($this->selectedContacts as $roleKey => $contactId) {
            if ($contactId) {
                $roleMap = [
                    'client' => 'Client',
                    'main_contractor' => 'Main Contractor',
                    'consultant' => 'Consultant',
                    'owner' => 'Owner',
                    'engineer' => 'Engineer',
                ];

                $role = InquirieRole::where('name', $roleMap[$roleKey])->first();
                if ($role) {
                    $inquiry->contacts()->attach($contactId, ['role_id' => $role->id]);
                }
            }
        }

        $this->saveAllWorkTypes($inquiry);

        // حفظ الملفات والبيانات الأخرى...
        if ($this->projectImage) {
            $inquiry->addMedia($this->projectImage->getRealPath())
                ->usingFileName($this->projectImage->getClientOriginalName())
                ->toMediaCollection('project-image');
        }

        if (!empty($this->documentFiles)) {
            foreach ($this->documentFiles as $file) {
                $inquiry->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('inquiry-documents');
            }
        }

        // حفظ باقي العلاقات...
        $this->saveSubmittalChecklists($inquiry);
        $this->saveWorkConditions($inquiry);
        $this->saveProjectDocuments($inquiry);
        $this->saveQuotationUnits($inquiry);
        $this->saveComments($inquiry);
        $this->saveAssignedEngineers($inquiry);

        // DB::commit();
        return redirect()->route('inquiries.index');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     session()->flash('error', 'Error saving inquiry: ' . $e->getMessage());
        //     return back();
        // }
    }

    private function saveSubmittalChecklists($inquiry)
    {
        $submittalIds = [];
        foreach ($this->submittalChecklist as $item) {
            if (!empty($item['checked']) && isset($item['id'])) {
                $submittalIds[] = $item['id'];
            }
        }
        if (!empty($submittalIds)) {
            $inquiry->submittalChecklists()->attach($submittalIds);
        }
    }

    private function saveWorkConditions($inquiry)
    {
        $conditionIds = [];
        foreach ($this->workingConditions as $condition) {
            if (!empty($condition['checked']) && isset($condition['id'])) {
                $conditionIds[] = $condition['id'];
            }
        }
        if (!empty($conditionIds)) {
            $inquiry->workConditions()->attach($conditionIds);
        }
    }

    private function saveProjectDocuments($inquiry)
    {
        foreach ($this->projectDocuments as $document) {
            if (!empty($document['checked'])) {
                $projectDocument = InquiryDocument::firstOrCreate(['name' => $document['name']]);
                $inquiry->projectDocuments()->attach($projectDocument->id, [
                    'description' => $document['description'] ?? null
                ]);
            }
        }
    }

    private function saveQuotationUnits($inquiry)
    {
        if (!empty($this->selectedQuotationUnits) && is_array($this->selectedQuotationUnits)) {
            $attachments = [];
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
                $inquiry->quotationUnits()->attach($attachments);
            }
        }
    }

    private function saveComments($inquiry)
    {
        foreach ($this->tempComments as $tempComment) {
            InquiryComment::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => Auth::id(),
                'comment' => $tempComment['comment'],
            ]);
        }
    }

    private function saveAllWorkTypes($inquiry)
    {
        $order = 0;
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

        if (!empty($this->currentWorkTypeSteps) && end($this->currentWorkTypeSteps)) {
            $currentLastId = end($this->currentWorkTypeSteps);
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
        if (!empty($this->selectedWorkTypes)) {
            return end($this->selectedWorkTypes)['steps'][array_key_last(end($this->selectedWorkTypes)['steps'])];
        }
        if (!empty($this->currentWorkTypeSteps)) {
            return end($this->currentWorkTypeSteps);
        }
        return null;
    }

    private function storeLocationInDatabase()
    {
        if (!$this->calculatedDistance) {
            return [null, null];
        }

        try {
            $emirate = $this->extractEmirateFromAddress($this->toLocation) ?: 'Abu Dhabi';
            $country = \App\Models\Country::firstOrCreate(
                ['title' => 'United Arab Emirates'],
                ['title' => 'United Arab Emirates']
            );
            $state = \App\Models\State::firstOrCreate(
                ['title' => 'United Arab Emirates', 'country_id' => $country->id],
                ['title' => 'United Arab Emirates', 'country_id' => $country->id]
            );
            $city = City::firstOrCreate(
                ['title' => $emirate, 'state_id' => $state->id],
                [
                    'latitude' => $this->toLocationLat,
                    'longitude' => $this->toLocationLng,
                    'state_id' => $state->id
                ]
            );
            $town = Town::updateOrCreate(
                ['title' => $this->toLocation],
                [
                    'latitude' => $this->toLocationLat,
                    'longitude' => $this->toLocationLng,
                    'city_id' => $city->id,
                    'distance_from_headquarters' => $this->calculatedDistance
                ]
            );
            $this->cityId = $city->id;
            $this->townId = $town->id;
            return [$city, $town];
        } catch (\Exception $e) {
            return [null, null];
        }
    }

    private function extractEmirateFromAddress($address)
    {
        $emirates = ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'];
        foreach ($emirates as $emirate) {
            if (stripos($address, $emirate) !== false) {
                return $emirate;
            }
        }
        return null;
    }

    public function generateTenderId()
    {
        $allWorkTypes = [];
        foreach ($this->selectedWorkTypes as $workType) {
            if (!empty($workType['path'])) {
                $allWorkTypes[] = end($workType['path']);
            }
        }
        if (!empty($this->currentWorkPath)) {
            $currentWorkTypeName = end($this->currentWorkPath);
            if (!in_array($currentWorkTypeName, $allWorkTypes)) {
                $allWorkTypes[] = $currentWorkTypeName;
            }
        }
        $workTypesString = implode(', ', $allWorkTypes);
        $cityName = '';
        if ($this->toLocation) {
            $cityName = $this->extractEmirateFromAddress($this->toLocation);
        }
        if (empty($cityName) && $this->cityId) {
            $cityName = City::find($this->cityId)?->title ?? '';
        }
        $townName = $this->townId ? Town::find($this->townId)?->title : '';
        $parts = array_filter([
            $this->tenderNo,
            $workTypesString,
            $cityName,
            $townName
        ]);
        $this->tenderId = implode(' - ', $parts);
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
            $this->generateTenderId();
        }
        $this->dispatch('workTypeAdded');
    }

    public function removeWorkType($index)
    {
        unset($this->selectedWorkTypes[$index]);
        $this->selectedWorkTypes = array_values($this->selectedWorkTypes);
        $this->generateTenderId();
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

    public function handleItemSelected($data)
    {
        $wireModel = $data['wireModel'];
        $value = $data['value'];

        // التحقق إذا كان اختيار contact
        if (strpos($wireModel, 'selectedContacts.') === 0) {
            $key = str_replace('selectedContacts.', '', $wireModel);
            $this->selectedContacts[$key] = $value;

            // تحديث قائمة الـ contacts عشان التفاصيل تظهر
            $this->refreshContactsList();

            // إضافة الدور للـ Contact
            if ($value) {
                $this->assignRoleToContact($value, $key);
            }
        } else {
            // باقي الحقول العادية
            $this->{$wireModel} = $value;
        }
    }

    public function removeDocumentFile($index)
    {
        unset($this->documentFiles[$index]);
        $this->documentFiles = array_values($this->documentFiles);
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

    public function updatedSubmittalChecklist($value, $key)
    {
        $this->calculateScores();
    }

    public function updatedCityId($value)
    {
        $this->generateTenderId();
    }

    public function updatedTownId($value)
    {
        $this->generateTenderId();
    }

    public function openFromMapModal()
    {
        $this->mapModalType = 'from';
        $this->showMapModal = true;

        $this->dispatch('initMapPicker', [
            'type' => 'from',
            'lat' => $this->fromLocationLat,
            'lng' => $this->fromLocationLng,
            'title' => __('Select First Location (From)'),
        ]);
    }

    public function openToMapModal()
    {
        $this->mapModalType = 'to';
        $this->showMapModal = true;

        $defaultLat = $this->toLocationLat ?? 25.20485;
        $defaultLng = $this->toLocationLng ?? 55.27078;

        $this->dispatch('initMapPicker', [
            'type' => 'to',
            'lat' => $defaultLat,
            'lng' => $defaultLng,
            'title' => __('Select Second Location (To)'),
        ]);
    }

    public function closeMapModal()
    {
        $this->showMapModal = false;
        $this->mapModalType = '';
    }

    #[On('locationPicked')]
    public function handleLocationPickedEvent(...$args)
    {
        $data = [
            'type' => $args[0] ?? null,
            'address' => $args[1] ?? null,
            'lat' => $args[2] ?? null,
            'lng' => $args[3] ?? null,
        ];

        $this->handleLocationPicked($data['type'], $data['address'], $data['lat'], $data['lng']);
    }

    public function handleLocationPicked($type, $address, $lat, $lng)
    {
        if (!$type || !$address || !$lat || !$lng) {
            return;
        }

        if ($type === 'from') {
            $this->fromLocation = $address;
            $this->fromLocationLat = $lat;
            $this->fromLocationLng = $lng;
        } else {
            $this->toLocation = $address;
            $this->toLocationLat = $lat;
            $this->toLocationLng = $lng;
        }

        if ($type === 'to') {
            $this->toLocation = $address;
            $this->toLocationLat = $lat;
            $this->toLocationLng = $lng;
            $this->generateTenderId();
        }

        $this->closeMapModal();

        if ($this->fromLocationLat && $this->fromLocationLng && $this->toLocationLat && $this->toLocationLng) {
            $this->calculateDistance();
        }
    }

    public function calculateDistance()
    {
        if (!$this->fromLocationLat || !$this->fromLocationLng) {
            session()->flash('warning', __('Please Select First Location'));
            return;
        }

        if (!$this->toLocationLat || !$this->toLocationLng) {
            session()->flash('warning', __('Please Select Second Location'));
            return;
        }

        try {
            $distanceCalculator = app(DistanceCalculatorService::class);

            $result = $distanceCalculator->calculateDrivingDistanceWithDetails(
                $this->fromLocationLat,
                $this->fromLocationLng,
                $this->toLocationLat,
                $this->toLocationLng
            );

            if ($result) {
                $this->calculatedDistance = $result['distance'];
                $this->calculatedDuration = $result['duration'] ?? null;
                $this->storeLocationInDatabase();
            } else {
                session()->flash('error', __('Failed To Calculate Distance. Please Try Again.'));
            }
        } catch (\Exception $e) {
            session()->flash('error', __('Error Calculating Distance: '));
        }
    }

    public function resetAll()
    {
        $this->fromLocation = 'Abu Dhabi, UAE';
        $this->fromLocationLat = 24.45388;
        $this->fromLocationLng = 54.37734;

        $this->toLocation = '';
        $this->toLocationLat = null;
        $this->toLocationLng = null;

        $this->calculatedDistance = null;
        $this->calculatedDuration = null;
    }

    public function resetFromLocation()
    {
        $this->fromLocation = 'Abu Dhabi, UAE';
        $this->fromLocationLat = 24.45388;
        $this->fromLocationLng = 54.37734;

        $this->calculatedDistance = null;
        $this->calculatedDuration = null;
    }

    public function resetToLocation()
    {
        $this->toLocation = '';
        $this->toLocationLat = null;
        $this->toLocationLng = null;

        $this->calculatedDistance = null;
        $this->calculatedDuration = null;
    }

    public function render()
    {
        return view('inquiries::livewire.create-inquiry');
    }
}
