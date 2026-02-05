<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\HR\Models\City;
use Modules\HR\Models\Town;
use Modules\HR\Models\State;
use Livewire\WithFileUploads;
use Modules\HR\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Inquiries\Models\Contact;
use Modules\CRM\Models\ClientCategory;
use Modules\Inquiries\Models\InquirieRole;
use Modules\Inquiries\Models\PricingStatus;
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Services\DistanceCalculatorService;
use Modules\Inquiries\Enums\{KonPriorityEnum, ClientPriorityEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, InquiryComment, QuotationType, ProjectSize, WorkCondition, InquiryDocument};

class EditInquiry extends Component
{
    use WithFileUploads;

    public $inquiryId;

    public $isDraft = false;
    public $inquiry;

    public $selectedEngineers = [];
    public $availableEngineers = [];

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
    public $quotationState;

    public $difficultyPercentage = 0;
    public $cityId;
    public $townId;
    public $townDistance;

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

    public $modalClientType = null;
    public $modalClientTypeLabel = '';

    public $projectImage;
    public $existingProjectImage;

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
    public $existingComments = [];

    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];

    public $clientCategories = [];
    // public $quotationStateOptions = [];
    public $pricingStatuses = [];
    public $pricingStatusId;

    public $projectDocuments = [];

    public $selectedContacts = [
        'client' => null,
        'main_contractor' => null,
        'consultant' => null,
        'owner' => null,
        'engineer' => null,
    ];

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

    public $contacts = [];
    public $inquirieRoles = [];
    public $selectedRoles = [];


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

    protected $distanceCalculator;

    public function boot(DistanceCalculatorService $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }
    public $type_note = null;

    public $submittalChecklist = [];
    public $workingConditions = [];

    public $quotationTypes = [];
    public $selectedQuotationUnits = [];

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
        'openContactModal' => 'openContactModal',
        'locationSelected' => 'handleLocationSelected',
        'locationPicked' => 'handleLocationPickedEvent', // تأكد من وجودها
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
            'media',
            'contacts' // إضافة
        ])->findOrFail($id);

        $this->initializeRoles();

        $this->loadInitialData();
        $this->populateFormData();

        // Restore fuller state from draft_data if available
        if ($this->inquiry->is_draft && $this->inquiry->draft_data) {
            $draftData = json_decode($this->inquiry->draft_data, true);
            if (is_array($draftData)) {
                foreach ($draftData as $key => $value) {
                    // Skip system properties or problematic ones
                    if (in_array($key, ['inquiry', 'distanceCalculator', 'projects', 'cities', 'towns', 'availableEngineers'])) {
                        continue;
                    }
                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }

        $this->calculateScores();
        $this->isDraft = (bool) $this->inquiry->is_draft;
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
        // $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->pricingStatuses = PricingStatus::active()->get();
        $this->projectSizeOptions = ProjectSize::pluck('name', 'id')->toArray();
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
                'id'             => $item->id,
                'name'           => $item->name,
                'checked'        => false,
                'value'          => $item->score,
                'options' => is_string($item->options) ? json_decode($item->options, true) : ($item->options ?? null),
                'selectedOption' => null,
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
                'id'             => $item->id,
                'name'           => $item->name,
                'checked'        => false,
                'options' => is_string($item->options) ? json_decode($item->options, true) : ($item->options ?? null),
                'selectedOption' => null,
                'value'          => $item->options ? 0 : $item->score,
                'default_score'  => $item->score,
            ];
        })->toArray();

        $this->clientCategories = ClientCategory::all()->toArray();
        $this->documentFiles = [];

        $this->availableEngineers = \App\Models\User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->toArray();
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
        $this->assignEngineerDate = $inquiry->assigned_engineer_date;

        foreach ($inquiry->submittalChecklists as $pivot) {
            $index = collect($this->submittalChecklist)->search(fn($i) => $i['id'] == $pivot->id);
            if ($index !== false) {
                $this->submittalChecklist[$index]['checked'] = true;
                $this->submittalChecklist[$index]['selectedOption'] = $pivot->pivot->selected_option;
                $this->submittalChecklist[$index]['value'] = $pivot->pivot->selected_option ?? $pivot->score;
            }
        }

        foreach ($inquiry->workConditions as $pivot) {
            $index = collect($this->workingConditions)->search(fn($i) => $i['id'] == $pivot->id);
            if ($index !== false) {
                $this->workingConditions[$index]['checked'] = true;
                $this->workingConditions[$index]['selectedOption'] = $pivot->pivot->selected_option;
                $this->workingConditions[$index]['value'] = $pivot->pivot->selected_option ?? $pivot->score;
            }
        }
        // Load work type hierarchy
        if ($inquiry->work_type_id) {
            $this->buildWorkTypeHierarchy($inquiry->work_type_id);
        }

        // Load inquiry source hierarchy
        if ($inquiry->inquiry_source_id) {
            $this->buildInquirySourceHierarchy($inquiry->inquiry_source_id);
        }

        // Load checklists
        // $this->checkItems($this->submittalChecklist, $inquiry->submittalChecklists->pluck('id'));
        // $this->checkItems($this->workingConditions, $inquiry->workConditions->pluck('id'));
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

        $this->selectedWorkTypes = [];
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

        $this->loadContactsFromInquiry($inquiry);

        if ($inquiry->town_distance) {
            $this->calculatedDistance = $inquiry->town_distance;

            // استرجاع المواقع من Town
            if ($inquiry->town) {
                $this->toLocation = $inquiry->town->title;
                $this->toLocationLat = $inquiry->town->latitude;
                $this->toLocationLng = $inquiry->town->longitude;
            }
        }
        $this->selectedEngineers = $this->inquiry->assignedEngineers()
            ->pluck('users.id')
            ->toArray();
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
            'relatedContacts' => [],
        ];
        $this->selectedRoles = [];
        $this->resetValidation();
        $this->dispatch('openContactModal');
    }


    public function removeEngineer($engineerId)
    {
        $this->selectedEngineers = array_values(
            array_filter($this->selectedEngineers, function ($id) use ($engineerId) {
                return $id != $engineerId;
            })
        );
    }

    public function saveNewContact()
    {
        $this->validate([
            'newContact.name' => 'required|string|max:255',
            'newContact.phone_1' => 'nullable|string|max:20',
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
        } catch (\Exception) {
            DB::rollBack();
            session()->flash('error', __('Error Adding Contact: '));
        }
    }


    private function refreshContactsList()
    {
        $this->contacts = Contact::with(['roles', 'parent'])->get()->map(function ($contact) {
            $contactArray = $contact->toArray();
            $contactArray['roles'] = $contact->roles->toArray();
            if ($contact->parent) {
                $contactArray['parent'] = $contact->parent->toArray();
            }
            return $contactArray;
        })->toArray();
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

    public function updatedSelectedContacts($value, $key)
    {
        if (!$value) {
            return;
        }

        $contact = Contact::find($value);
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

        $roleName = $roleMap[$key] ?? null;
        if (!$roleName) {
            return;
        }

        $role = InquirieRole::where('name', $roleName)->first();
        if (!$role) {
            return;
        }

        if (!$contact->roles()->where('role_id', $role->id)->exists()) {
            $contact->roles()->attach($role->id);
        }
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

    private function storeLocationInDatabase()
    {
        if (!$this->calculatedDistance) {
            return [null, null];
        }

        try {
            $emirate = $this->extractEmirateFromAddress($this->toLocation) ?: 'Abu Dhabi';
            $country = Country::firstOrCreate(
                ['title' => 'United Arab Emirates'],
                ['title' => 'United Arab Emirates']
            );
            $state = State::firstOrCreate(
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

    private function buildWorkTypeHierarchy($workTypeId)
    {
        $workType = WorkType::find($workTypeId);
        if (!$workType) return;

        $hierarchy = collect();
        $current = $workType;

        // بناء الهيكل من الأسفل للأعلى
        while ($current) {
            $hierarchy->prepend($current);
            $current = $current->parent;
        }

        $this->currentWorkTypeSteps = [];
        $this->currentWorkPath = [];

        // تعبئة الخطوات بشكل صحيح
        foreach ($hierarchy as $index => $type) {
            $stepNumber = $index + 1;
            $this->currentWorkTypeSteps[$stepNumber] = $type->id;
            $this->currentWorkPath[$index] = $type->name;
        }

        // ✅ إرسال البيانات للـ JavaScript
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

        // التحقق إذا كان اختيار contact
        if (strpos($wireModel, 'selectedContacts.') === 0) {
            $key = str_replace('selectedContacts.', '', $wireModel);
            $this->selectedContacts[$key] = $value;

            // تحديث قائمة الـ contacts
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

        if (!$contact->roles()->where('role_id', $role->id)->exists()) {
            $contact->roles()->attach($role->id);
        }
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

    public function updated($propertyName, $property)
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

        if (
            str_contains($property, 'submittalChecklist') ||
            str_contains($property, 'workingConditions')
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

    public function save()
    {
        // dd($this->all());
        // إضافة Validation
        $this->validate([
            'projectId' => 'nullable|exists:projects,id',
            'inquiryDate' => 'required|date',
            'cityId' => 'nullable|exists:cities,id',
            'townId' => 'nullable|exists:towns,id',
            'status' => 'nullable|string',
            'tenderNo' => 'nullable|string|max:255',
            'totalProjectValue' => 'nullable|numeric|min:0',
        ]);

        // try {
        //     DB::beginTransaction();

        // ✅ إصلاح: تخزين الموقع فقط إذا كانت البيانات موجودة
        $cityId = $this->cityId;
        $townId = $this->townId;
        $townDistance = $this->calculatedDistance;

        // إذا كانت المواقع والمسافة محسوبة، احفظها
        if ($this->toLocationLat && $this->toLocationLng && $this->calculatedDistance) {
            list($city, $town) = $this->storeLocationInDatabase();
            $cityId = $city->id ?? $this->cityId;
            $townId = $town->id ?? $this->townId;
            $townDistance = $this->calculatedDistance;
        }

        // ✅ إصلاح: التأكد من الـ work_type_id
        $workTypeId = $this->getMainWorkTypeId();

        // ✅ إصلاح: التأكد من inquiry_source_id
        $inquirySourceId = null;
        if (!empty($this->inquirySourceSteps)) {
            $inquirySourceId = end($this->inquirySourceSteps);
            // تنظيف القيم الفارغة
            if ($inquirySourceId === '' || $inquirySourceId === null) {
                $inquirySourceId = null;
            }
        }

        // تحديث الـ Inquiry
        $this->inquiry->update([
            'project_id' => $this->projectId,
            'inquiry_date' => $this->inquiryDate,
            'req_submittal_date' => $this->reqSubmittalDate,
            'project_start_date' => $this->projectStartDate,
            'city_id' => $cityId,
            'town_id' => $townId,
            'town_distance' => $townDistance,
            'status' => $this->status,
            'status_for_kon' => $this->statusForKon,
            'kon_title' => $this->konTitle,
            'work_type_id' => $workTypeId,
            'final_work_type' => $this->finalWorkType,
            'inquiry_source_id' => $inquirySourceId,
            'final_inquiry_source' => $this->finalInquirySource,
            'total_check_list_score' => $this->totalScore,
            'project_difficulty' => $this->projectDifficulty,
            'tender_number' => $this->tenderNo,
            'tender_id' => $this->tenderId,
            'pricing_status_id' => $this->pricingStatusId,
            'estimation_start_date' => $this->estimationStartDate,
            'estimation_finished_date' => $this->estimationFinishedDate,
            'submitting_date' => $this->submittingDate,
            'total_project_value' => $this->totalProjectValue,
            'quotation_state' => $this->quotationState,
            'rejection_reason' => $this->quotationStateReason,
            'assigned_engineer_date' => $this->assignEngineerDate,
            'project_size_id' => $this->projectSize,
            'client_priority' => $this->clientPriority,
            'kon_priority' => $this->konPriority,
            'type_note' => $this->type_note,
        ]);

        // ✅ حفظ Contacts (حذف التكرار)
        $this->inquiry->contacts()->detach();

        $roleMap = [
            'client' => 'Client',
            'main_contractor' => 'Main Contractor',
            'consultant' => 'Consultant',
            'owner' => 'Owner',
            'engineer' => 'Engineer',
        ];

        foreach ($this->selectedContacts as $roleKey => $contactId) {
            if ($contactId && isset($roleMap[$roleKey])) {
                $role = InquirieRole::where('name', $roleMap[$roleKey])->first();
                if ($role) {
                    $this->inquiry->contacts()->attach($contactId, ['role_id' => $role->id]);
                }
            }
        }

        // ✅ حفظ Work Types
        $this->inquiry->workTypes()->detach();
        $this->saveAllWorkTypes($this->inquiry);

        // ✅ Handle project image
        if ($this->projectImage) {
            if ($this->existingProjectImage) {
                $this->existingProjectImage->delete();
            }
            $this->inquiry
                ->addMedia($this->projectImage->getRealPath())
                ->usingFileName($this->projectImage->getClientOriginalName())
                ->toMediaCollection('project-image');
        }

        // ✅ Handle document files
        if (!empty($this->documentFiles) && is_array($this->documentFiles)) {
            foreach ($this->documentFiles as $file) {
                if ($file && method_exists($file, 'getRealPath')) {
                    $this->inquiry
                        ->addMedia($file->getRealPath())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('inquiry-documents');
                }
            }
        }

        // ✅ Sync submittal checklists
        // $submittalIds = [];
        $this->inquiry->submittalChecklists()->detach();
        foreach ($this->submittalChecklist as $item) {
            if (!empty($item['checked']) && isset($item['id'])) {
                $data = [];
                if (isset($item['selectedOption'])) {
                    $data['selected_option'] = $item['selectedOption'];
                }
                $this->inquiry->submittalChecklists()->attach($item['id'], $data);
            }
        }

        // حفظ Working Conditions مع selectedOption
        $this->inquiry->workConditions()->detach();
        foreach ($this->workingConditions as $item) {
            if (!empty($item['checked']) && isset($item['id'])) {
                $data = [];
                if (isset($item['selectedOption'])) {
                    $data['selected_option'] = $item['selectedOption'];
                }
                $this->inquiry->workConditions()->attach($item['id'], $data);
            }
        }
        // ✅ Sync project documents
        $this->inquiry->projectDocuments()->detach();
        foreach ($this->projectDocuments as $document) {
            if (!empty($document['checked']) && isset($document['name'])) {
                $projectDocument = InquiryDocument::firstOrCreate(
                    ['name' => $document['name']]
                );

                $this->inquiry->projectDocuments()->attach($projectDocument->id, [
                    'description' => $document['description'] ?? null
                ]);
            }
        }

        // ✅ إصلاح: Sync quotation units
        $attachments = [];

        if (!empty($this->selectedQuotationUnits) && is_array($this->selectedQuotationUnits)) {
            foreach ($this->selectedQuotationUnits as $typeId => $unitIds) {
                // تنظيف typeId
                if (!is_numeric($typeId) || (int)$typeId <= 0) {
                    continue;
                }
                $typeId = (int) $typeId;

                if (!empty($unitIds) && is_array($unitIds)) {
                    foreach ($unitIds as $unitId => $isSelected) {
                        // تحقق من أن القيمة true
                        if (($isSelected === true || $isSelected === 1 || $isSelected === '1')) {
                            // تنظيف unitId
                            if (is_numeric($unitId) && (int)$unitId > 0) {
                                $unitId = (int) $unitId;
                                $attachments[$unitId] = ['quotation_type_id' => $typeId];
                            }
                        }
                    }
                }
            }
        }

        // حفظ الـ units
        if (!empty($attachments)) {
            $this->inquiry->quotationUnits()->sync($attachments);
        } else {
            // إذا لم يكن هناك units، احذف القديمة
            $this->inquiry->quotationUnits()->detach();
        }

        // ✅ Save new temporary comments
        if (!empty($this->tempComments) && is_array($this->tempComments)) {
            foreach ($this->tempComments as $tempComment) {
                if (isset($tempComment['comment']) && !empty($tempComment['comment'])) {
                    InquiryComment::create([
                        'inquiry_id' => $this->inquiry->id,
                        'user_id' => Auth::id(),
                        'comment' => $tempComment['comment'],
                    ]);
                }
            }
        }
        $this->inquiry->assignedEngineers()->sync($this->selectedEngineers);

        // إعادة حساب النتائج
        $this->calculateScores();

        $wasDraft = $this->isDraft;

        // If it was a draft and we're saving normally, it's no longer a draft
        if ($wasDraft) {
            $this->inquiry->update(['is_draft' => false]);
            $this->isDraft = false;
        }

        $message = $wasDraft ? __('Inquiry Published Successfully') : __('Inquiry Updated Successfully');
        session()->flash('success', $message);

        return redirect()->route('inquiries.index');
    }

    public function saveAsDraft()
    {
        $this->validate([
            'inquiryDate' => 'required|date',
        ]);

        $cityId = $this->cityId;
        $townId = $this->townId;
        $townDistance = $this->calculatedDistance;

        if ($this->toLocationLat && $this->toLocationLng && $this->calculatedDistance) {
            list($city, $town) = $this->storeLocationInDatabase();
            $cityId = $city->id ?? $this->cityId;
            $townId = $town->id ?? $this->townId;
            $townDistance = $this->calculatedDistance;
        }

        $workTypeId = $this->getMainWorkTypeId();
        $inquirySourceId = null;
        if (!empty($this->inquirySourceSteps)) {
            $inquirySourceId = end($this->inquirySourceSteps);
            if ($inquirySourceId === '' || $inquirySourceId === null) {
                $inquirySourceId = null;
            }
        }

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
            'calculatedDistance' => $this->calculatedDistance,
            'calculatedDuration' => $this->calculatedDuration,
            'submittalChecklist' => $this->submittalChecklist,
            'workingConditions' => $this->workingConditions,
            'projectDocuments' => $this->projectDocuments,
            'selectedQuotationUnits' => $this->selectedQuotationUnits,
            'tempComments' => $this->tempComments,
            'selectedEngineers' => $this->selectedEngineers,
            'selectedContacts' => $this->selectedContacts,
            'assignEngineerDate' => $this->assignEngineerDate,
            'pricingStatusId' => $this->pricingStatusId,
            'quotationState' => $this->quotationState,
            'projectSize' => $this->projectSize,
            'clientPriority' => $this->clientPriority,
            'konPriority' => $this->konPriority,
        ];

        $this->inquiry->update([
            'project_id' => $this->projectId,
            'inquiry_date' => $this->inquiryDate,
            'req_submittal_date' => $this->reqSubmittalDate,
            'project_start_date' => $this->projectStartDate,
            'city_id' => $cityId,
            'town_id' => $townId,
            'town_distance' => $townDistance,
            'status' => $this->status,
            'status_for_kon' => $this->statusForKon,
            'kon_title' => $this->konTitle,
            'work_type_id' => $workTypeId,
            'final_work_type' => $this->finalWorkType,
            'inquiry_source_id' => $inquirySourceId,
            'final_inquiry_source' => $this->finalInquirySource,
            'total_check_list_score' => $this->totalScore,
            'project_difficulty' => $this->projectDifficulty,
            'tender_number' => $this->tenderNo,
            'tender_id' => $this->tenderId,
            'pricing_status_id' => $this->pricingStatusId,
            'estimation_start_date' => $this->estimationStartDate,
            'estimation_finished_date' => $this->estimationFinishedDate,
            'submitting_date' => $this->submittingDate,
            'total_project_value' => $this->totalProjectValue,
            'quotation_state' => $this->quotationState,
            'rejection_reason' => $this->quotationStateReason,
            'assigned_engineer_date' => $this->assignEngineerDate,
            'project_size_id' => $this->projectSize,
            'client_priority' => $this->clientPriority,
            'kon_priority' => $this->konPriority,
            'type_note' => $this->type_note,
            'is_draft' => true,
            'draft_data' => json_encode($draftData),
            'last_draft_saved_at' => now(),
        ]);

        $this->inquiry->contacts()->detach();
        $roleMap = [
            'client' => 'Client',
            'main_contractor' => 'Main Contractor',
            'consultant' => 'Consultant',
            'owner' => 'Owner',
            'engineer' => 'Engineer',
        ];

        foreach ($this->selectedContacts as $roleKey => $contactId) {
            if ($contactId && isset($roleMap[$roleKey])) {
                $role = InquirieRole::where('name', $roleMap[$roleKey])->first();
                if ($role) {
                    $this->inquiry->contacts()->attach($contactId, ['role_id' => $role->id]);
                }
            }
        }

        $this->inquiry->workTypes()->detach();
        $this->saveAllWorkTypes($this->inquiry);

        $this->inquiry->assignedEngineers()->sync($this->selectedEngineers);


        session()->flash('success', __('Draft updated successfully'));
        $this->dispatch('draftSaved', ['inquiryId' => $this->inquiry->id]);
        // } catch (\Exception) {
        //     DB::rollBack();
        //     session()->flash('error', __('Error During Update: '));
        //     return back()->withInput();
        // }
    }

    private function getMainWorkTypeId()
    {
        // أولاً: تحقق من الـ work types المختارة
        if (!empty($this->selectedWorkTypes) && is_array($this->selectedWorkTypes)) {
            $lastWorkType = end($this->selectedWorkTypes);
            if (isset($lastWorkType['steps']) && is_array($lastWorkType['steps'])) {
                $lastStep = end($lastWorkType['steps']);
                if ($lastStep && is_numeric($lastStep)) {
                    return (int) $lastStep;
                }
            }
        }

        // ثانياً: تحقق من current work type
        if (!empty($this->currentWorkTypeSteps) && is_array($this->currentWorkTypeSteps)) {
            $lastStep = end($this->currentWorkTypeSteps);
            if ($lastStep && is_numeric($lastStep)) {
                return (int) $lastStep;
            }
        }

        // إذا لم يوجد، ارجع القيمة القديمة من الـ inquiry
        return $this->inquiry->work_type_id;
    }

    // ✅ إصلاح: saveAllWorkTypes
    private function saveAllWorkTypes($inquiry)
    {
        $order = 0;

        // حفظ الـ Work Types المختارة
        if (!empty($this->selectedWorkTypes) && is_array($this->selectedWorkTypes)) {
            foreach ($this->selectedWorkTypes as $workType) {
                if (!isset($workType['steps']) || !is_array($workType['steps'])) {
                    continue;
                }

                $lastStepId = end($workType['steps']);

                if ($lastStepId && is_numeric($lastStepId)) {
                    $inquiry->workTypes()->attach((int) $lastStepId, [
                        'hierarchy_path' => json_encode(array_map('intval', $workType['steps'])),
                        'description' => $workType['final_description'] ?? '',
                        'order' => $order++
                    ]);
                }
            }
        }

        // حفظ الـ Work Type الحالي (إذا كان موجوداً ومختلفاً)
        if (!empty($this->currentWorkTypeSteps) && is_array($this->currentWorkTypeSteps)) {
            $currentLastId = end($this->currentWorkTypeSteps);

            if ($currentLastId && is_numeric($currentLastId)) {
                // تحقق أنه غير موجود في المختارين
                $alreadyExists = false;

                if (!empty($this->selectedWorkTypes)) {
                    foreach ($this->selectedWorkTypes as $wt) {
                        if (isset($wt['steps']) && end($wt['steps']) == $currentLastId) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                }

                if (!$alreadyExists) {
                    $inquiry->workTypes()->attach((int) $currentLastId, [
                        'hierarchy_path' => json_encode(array_map('intval', $this->currentWorkTypeSteps)),
                        'description' => $this->finalWorkType ?? '',
                        'order' => $order
                    ]);
                }
            }
        }
    }

    public function render()
    {
        return view('inquiries::livewire.edit-inquiry');
    }
}
