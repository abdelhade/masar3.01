<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{City, Town, Client};
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\ClientCategory;
use Modules\Inquiries\Models\ProjectSize;
use Modules\Progress\Models\ProjectProgress;
use Modules\Inquiries\Models\InquiryDocument;
use Modules\Inquiries\Services\DistanceCalculatorService;
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus, KonPriorityEnum, ClientPriorityEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition, InquiryComment, QuotationType};

class CreateInquiry extends Component
{
    use WithFileUploads;

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

    public $fromLocation = 'Abu Dhabi, UAE';
    public $fromLocationLat = 24.45388;
    public $fromLocationLng = 54.37734;

    public $toLocation = '';
    public $toLocationLat = null;
    public $toLocationLng = null;

    public $calculatedDistance = null;
    public $calculatedDuration = null;

    public $showMapModal = false;
    public $mapModalType = ''; // 'from' أو 'to'

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
    public $quotationState;
    public $totalSubmittalScore = 0;
    public $totalConditionsScore = 0;
    public $projectDifficulty = 1;
    public $documentFiles = [];

    public $totalScore = 0;

    public $modalClientType = null;
    public $modalClientTypeLabel = '';

    public $projectImage;

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


    protected $distanceCalculator;

    public function boot(DistanceCalculatorService $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
        'openClientModal' => 'openClientModal',
        'locationSelected' => 'handleLocationSelected',
        // 'locationPicked' => 'handleLocationPicked',
    ];

    public function mount()
    {
        try {
            DB::beginTransaction();
            $clientTypes = [
                ['id' => 1, 'title' => 'Person'],
                ['id' => 2, 'title' => 'Main Contractor'],
                ['id' => 3, 'title' => 'Consultant'],
                ['id' => 4, 'title' => 'Owner'],
                ['id' => 5, 'title' => 'Engineer'],
            ];

            foreach ($clientTypes as $type) {
                \Modules\CRM\Models\ClientType::firstOrCreate(
                    ['title' => $type['title']], // البحث بناءً على العنوان
                    [
                        'id' => $type['id'],
                        'title' => $type['title'],
                        'created_at' => now(),
                        'updated_at' => now(),
                        'branch_id' => Auth::user()->branch_id ?? 1,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception) {
            DB::rollBack();
            return;
        }
        $this->engineers = Client::with('clientType')->get()->toArray();
        $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->projectSizeOptions = ProjectSize::pluck('name', 'id')->toArray();
        $this->inquiryDate = now()->format('Y-m-d');
        $this->workTypes = WorkType::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->inquirySources = InquirySource::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->projects = ProjectProgress::all()->toArray();

        $this->clients = Client::with('clientType')->get()->toArray();
        $this->mainContractors = Client::with('clientType')->get()->toArray();
        $this->consultants = Client::with('clientType')->get()->toArray();
        $this->owners = Client::with('clientType')->get()->toArray();
        $this->engineers = Client::with('clientType')->get()->toArray();

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

        $submittalsFromDB = SubmittalChecklist::all();
        $this->submittalChecklist = $submittalsFromDB->map(function ($item) {
            return [
                'id' => $item->id, // مهم للحفظ لاحقاً
                'name' => $item->name,
                'checked' => false, // القيمة الافتراضية
                'value' => $item->score // استخدام score من قاعدة البيانات
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
        // جلب Working Conditions
        $conditionsFromDB = WorkCondition::all();
        $this->workingConditions = $conditionsFromDB->map(function ($item) {
            return [
                'id' => $item->id, // مهم للحفظ لاحقاً
                'name' => $item->name,
                'checked' => false, // القيمة الافتراضية
                'options' => $item->options, // الخيارات من قاعدة البيانات
                'selectedOption' => null, // القيمة الافتراضية
                'value' => $item->options ? 0 : $item->score, // القيمة الأولية، ستتغير عند التحديد
                'default_score' => $item->score // نحتفظ بالسكور الافتراضي
            ];
        })->toArray();

        $this->clientCategories = ClientCategory::all()->toArray();

        $this->documentFiles = [];
        $this->calculateScores();

        $this->fromLocation = 'Abu Dhabi, UAE';
        $this->fromLocationLat = 24.45388;
        $this->fromLocationLng = 54.37734;
    }

    public function generateTenderId()
    {
        // جمع كل أنواع العمل المختارة
        $allWorkTypes = [];

        // إضافة الـ work types المحفوظة
        foreach ($this->selectedWorkTypes as $workType) {
            if (!empty($workType['path'])) {
                $allWorkTypes[] = end($workType['path']);
            }
        }

        // إضافة الـ work type الحالي لو موجود
        if (!empty($this->currentWorkPath)) {
            $currentWorkTypeName = end($this->currentWorkPath);
            if (!in_array($currentWorkTypeName, $allWorkTypes)) {
                $allWorkTypes[] = $currentWorkTypeName;
            }
        }

        // دمج كل أنواع العمل بفاصلة
        $workTypesString = implode(', ', $allWorkTypes);

        // جلب اسم المدينة من الموقع المحدد (toLocation)
        $cityName = '';
        if ($this->toLocation) {
            // استخراج المدينة من العنوان
            $cityName = $this->extractEmirateFromAddress($this->toLocation);
        }

        // لو مفيش مدينة من الموقع، استخدم cityId
        if (empty($cityName) && $this->cityId) {
            $cityName = City::find($this->cityId)?->title ?? '';
        }

        // لو فيه townId، أضفه كمان
        $townName = $this->townId ? Town::find($this->townId)?->title : '';

        // بناء معرف المناقصة
        $parts = array_filter([
            $this->tenderNo,
            $workTypesString,
            $cityName,
            $townName
        ]);

        $this->tenderId = implode(' - ', $parts);
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

    public function addWorkType()
    {
        if (!empty($this->currentWorkTypeSteps) && end($this->currentWorkTypeSteps)) {
            $this->selectedWorkTypes[] = [
                'steps' => $this->currentWorkTypeSteps,
                'path' => $this->currentWorkPath,
                'final_description' => '' // يمكن إضافة وصف لكل نوع
            ];

            // Reset current selection
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
        // تحويل المعاملات إلى مصفوفة
        $data = [
            'type' => $args[0] ?? null,
            'address' => $args[1] ?? null,
            'lat' => $args[2] ?? null,
            'lng' => $args[3] ?? null,
        ];

        // استدعاء الدالة الأصلية مع المعاملات المنفصلة
        $this->handleLocationPicked($data['type'], $data['address'], $data['lat'], $data['lng']);
    }

    public function updatedCityId($value)
    {
        $this->generateTenderId();
    }

    public function updatedTownId($value)
    {
        $this->generateTenderId();
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
                // حفظ الموقع في قاعدة البيانات (اختياري)
                $this->storeLocationInDatabase();
            } else {
                session()->flash('error', __('Failed To Calculate Distance. Please Try Again.'));
            }
        } catch (\Exception) {

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
            return;
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

            $town =  Town::updateOrCreate(
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
            session()->flash('error', __('Error Saving Location: '));
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
        // التحقق من نوع التحديث
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $property = $parts[1] ?? 'checked';

        if ($property === 'checked') {
            // إذا تم إلغاء التحديد، إعادة تعيين القيمة المختارة
            if (!$this->workingConditions[$index]['checked']) {
                $this->workingConditions[$index]['selectedOption'] = null;
                $this->workingConditions[$index]['value'] = 0;
            } else {
                // إذا تم التحديد وله خيارات، استخدم القيمة الافتراضية
                if (isset($this->workingConditions[$index]['options'])) {
                    if (!$this->workingConditions[$index]['selectedOption']) {
                        $firstOption = array_values($this->workingConditions[$index]['options'])[0];
                        $this->workingConditions[$index]['selectedOption'] = $firstOption;
                        $this->workingConditions[$index]['value'] = $firstOption;
                    }
                } else {
                    // إذا لم تكن له خيارات، استخدم القيمة الافتراضية
                    $this->workingConditions[$index]['value'] = $this->workingConditions[$index]['value'] ?? 0;
                }
            }
        } elseif ($property === 'selectedOption') {
            // تحديث القيمة عند تغيير الخيار المحدد
            $this->workingConditions[$index]['value'] = $value;
        }

        // إعادة حساب النتائج
        $this->calculateScores();
    }

    public function calculateScores()
    {
        // حساب اسكور التقديمات المحدد
        $this->totalSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            if ($item['checked']) {
                $this->totalSubmittalScore += (int) ($item['value'] ?? 0);
            }
        }

        // حساب اسكور الشروط المحدد
        $this->totalConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked']) {
                $this->totalConditionsScore += (int) ($condition['value'] ?? 0);
            }
        }

        // حساب السكور الإجمالي المحدد
        $this->totalScore = $this->totalSubmittalScore + $this->totalConditionsScore;

        // ========== حساب النسبة المئوية ==========

        // حساب مجموع كل الـ submittals المتاحة
        $maxSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            $maxSubmittalScore += (int) ($item['value'] ?? 0);
        }

        // حساب مجموع كل الـ conditions المتاحة
        $maxConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if (isset($condition['options'])) {
                // لو عنده options، ناخد أعلى قيمة
                $maxConditionsScore += max(array_values($condition['options']));
            } else {
                // لو مفيش options، ناخد الـ default score
                $maxConditionsScore += (int) ($condition['default_score'] ?? 0);
            }
        }

        // مجموع كل الـ scores المتاحة
        $maxTotalScore = $maxSubmittalScore + $maxConditionsScore;

        // حساب النسبة المئوية
        $percentage = $maxTotalScore > 0 ? ($this->totalScore / $maxTotalScore) * 100 : 0;

        // تحديد صعوبة المشروع بناءً على النسبة المئوية
        if ($percentage < 25) {
            $this->projectDifficulty = 1; // سهل (أقل من 25%)
        } elseif ($percentage < 50) {
            $this->projectDifficulty = 2; // متوسط (25% - 50%)
        } elseif ($percentage < 75) {
            $this->projectDifficulty = 3; // صعب (50% - 75%)
        } else {
            $this->projectDifficulty = 4; // صعب جداً (75% فأكثر)
        }

        // اختياري: حفظ النسبة المئوية في property لعرضها
        $this->difficultyPercentage = round($percentage, 2);
    }

    public function updatedSubmittalChecklist($value, $key)
    {
        $this->calculateScores();
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
        } catch (\Exception) {
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
        $this->engineers = $allClients; // تأكد إن المهندسين بيتحدثوا
        $this->clientCategories = ClientCategory::all()->toArray();
    }

    private function getWireModelForClientType()
    {
        $clientType = \Modules\CRM\Models\ClientType::find($this->modalClientType);

        if (!$clientType) {
            return 'clientId'; // default
        }

        return match ($clientType->title) {
            'Person', 'Company' => 'clientId',
            'Main Contractor' => 'mainContractorId',
            'Consultant' => 'consultantId',
            'Owner' => 'ownerId',
            'Engineer' => 'assignedEngineer',
            default => 'clientId'
        };
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
            'client_type_id' => null,
        ];
        $this->modalClientType = null;
        $this->modalClientTypeLabel = '';
    }

    public function save()
    {
        // dd($this->all());
        // $this->validate([
        //     'projectId' => 'required|exists:projects,id',
        //     'inquiryDate' => 'required|date',
        //     'reqSubmittalDate' => 'nullable|date',
        //     'projectStartDate' => 'nullable|date',
        //     'cityId' => 'nullable|exists:cities,id',
        //     'townId' => 'nullable|exists:towns,id',
        //     'status' => 'required',
        //     'statusForKon' => 'nullable',
        //     'konTitle' => 'required',
        //     'clientId' => 'nullable|exists:clients,id',
        //     'mainContractorId' => 'nullable|exists:clients,id',
        //     'consultantId' => 'nullable|exists:clients,id',
        //     'ownerId' => 'nullable|exists:clients,id',
        //     'assignedEngineer' => 'nullable|exists:clients,id',
        //     'projectSize' => 'nullable',
        //     'quotationState' => 'nullable',
        //     'clientPriority' => 'nullable',
        //     'konPriority' => 'nullable',
        //     'documentFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        // ]);

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
            'town_distance' => $this->calculatedDistance,

            'status' => $this->status,
            'status_for_kon' => $this->statusForKon,
            'kon_title' => $this->konTitle,

            // Work Type - آخر خطوة محددة
            'work_type_id' =>  $this->getMainWorkTypeId(),
            'final_work_type' => $this->finalWorkType,

            // Inquiry Source - آخر خطوة محددة
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

        $this->saveAllWorkTypes($inquiry);

        if ($this->projectImage) {
            $inquiry
                ->addMedia($this->projectImage->getRealPath())
                ->usingFileName($this->projectImage->getClientOriginalName())
                ->toMediaCollection('project-image'); // اسم المجموعة
        }

        // 2. حفظ الملفات المتعددة
        if (!empty($this->documentFiles)) {
            foreach ($this->documentFiles as $file) {
                $inquiry
                    ->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('inquiry-documents');
            }
        }

        // 3. حفظ Submittal Checklist
        $submittalIds = [];
        foreach ($this->submittalChecklist as $item) {
            if (!empty($item['checked']) && isset($item['id'])) {
                $submittalIds[] = $item['id'];
            }
        }
        if (!empty($submittalIds)) {
            $inquiry->submittalChecklists()->attach($submittalIds);
        }

        // 4. حفظ Working Conditions
        $conditionIds = [];
        foreach ($this->workingConditions as $condition) {
            if (!empty($condition['checked']) && isset($condition['id'])) {
                $conditionIds[] = $condition['id'];
            }
        }
        if (!empty($conditionIds)) {
            $inquiry->workConditions()->attach($conditionIds);
        }

        // 5. حفظ Project Documents
        foreach ($this->projectDocuments as $document) {
            if (!empty($document['checked'])) {
                $projectDocument = InquiryDocument::firstOrCreate(
                    ['name' => $document['name']]
                );

                $inquiry->projectDocuments()->attach($projectDocument->id, [
                    'description' => $document['description'] ?? null
                ]);
            }
        }

        if (!empty($this->selectedQuotationUnits) && is_array($this->selectedQuotationUnits)) {
            $attachments = []; // array للـ attach الجماعي

            foreach ($this->selectedQuotationUnits as $typeId => $unitIds) {
                // تأكد إن typeId valid (عدد > 0)
                if (!is_numeric($typeId) || (int)$typeId <= 0) {
                    continue; // تجاهل types غير صحيحة
                }
                $typeId = (int) $typeId; // cast لـ int

                if (!empty($unitIds) && is_array($unitIds)) {
                    foreach ($unitIds as $unitId => $isSelected) {
                        // الفلترة الرئيسية: تأكد إن unitId valid و selected
                        if ($isSelected === true || $isSelected === 1) {
                            // تحقق من unitId: يبقى numeric، > 0، ومش string فاضية
                            if (is_numeric($unitId) && (int)$unitId > 0) {
                                $unitId = (int) $unitId; // cast لـ int آمن
                                $attachments[$unitId] = ['quotation_type_id' => $typeId];
                            }
                            // لو $unitId مش numeric أو =0، هيتجاهل تلقائيًا
                        }
                    }
                }
            }
            // attach بس لو في attachments valid
            if (!empty($attachments)) {
                $inquiry->quotationUnits()->attach($attachments);
            } else {
            }
        }
        // 6. حفظ التعليقات المؤقتة
        foreach ($this->tempComments as $tempComment) {
            InquiryComment::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => Auth::id(),
                'comment' => $tempComment['comment'],
            ]);
        }

        // DB::commit();
        return redirect()->route('inquiries.index');
        // } catch (\Exception) {
        //     DB::rollBack();
        //     return back();
        // }
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
        return view('inquiries::livewire.create-inquiry');
    }
}
