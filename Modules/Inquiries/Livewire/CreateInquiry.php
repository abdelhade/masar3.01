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
use Modules\Inquiries\Enums\{KonTitle, StatusForKon, InquiryStatus};
use Modules\Inquiries\Enums\{KonPriorityEnum, ProjectSizeEnum, ClientPriorityEnum};
use Modules\Inquiries\Models\{WorkType, Inquiry, InquirySource, SubmittalChecklist, ProjectDocument, WorkCondition, InquiryComment};

class CreateInquiry extends Component
{
    use WithFileUploads;

    public $selectedWorkTypes = [];
    public $currentWorkTypeSteps = [1 => null];
    public $currentWorkPath = [];

    public $selectedInquiryPath = [];
    public $inquirySourceSteps = [1 => null];
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
    public $projectDifficulty = 1;
    public $documentFiles = [];

    public $totalScore = 0;

    public $modalClientType = null;
    public $modalClientTypeLabel = '';

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

    public $tempComments = [];
    public $newTempComment = '';

    public $clientPriority;
    public $konPriority;
    public $clientPriorityOptions = [];
    public $konPriorityOptions = [];

    public $clientCategories = [];
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

    protected $listeners = [
        'getWorkTypeChildren' => 'emitWorkTypeChildren',
        'getInquirySourceChildren' => 'emitInquirySourceChildren',
        'itemSelected' => 'handleItemSelected',
        'openClientModal' => 'openClientModal',
    ];

    public function mount()
    {
        $this->engineers = Client::where('type', ClientType::ENGINEER->value)->get()->toArray();
        $this->quotationStateOptions = Inquiry::getQuotationStateOptions();
        $this->projectSizeOptions = ProjectSizeEnum::values();
        $this->inquiryDate = now()->format('Y-m-d');
        $this->workTypes = WorkType::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->inquirySources = InquirySource::where('is_active', true)->whereNull('parent_id')->get()->toArray();
        $this->projects = ProjectProgress::all()->toArray();
        $this->cities = City::all()->toArray();
        $this->towns = $this->cityId ? Town::where('city_id', $this->cityId)->get()->toArray() : [];
        $this->clients = Client::whereIn('type', [ClientType::Person->value, ClientType::Company->value])->get()->toArray();
        $this->mainContractors = Client::where('type', ClientType::MainContractor->value)->get()->toArray();
        $this->consultants = Client::where('type', ClientType::Consultant->value)->get()->toArray();
        $this->owners = Client::where('type', ClientType::Owner->value)->get()->toArray();
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
        $this->tenderNo = 'T-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $submittalsFromDB = SubmittalChecklist::all();
        $this->submittalChecklist = $submittalsFromDB->map(function ($item) {
            return [
                'id' => $item->id, // مهم للحفظ لاحقاً
                'name' => $item->name,
                'checked' => false, // القيمة الافتراضية
                'value' => $item->score // استخدام score من قاعدة البيانات
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
    }

    public function generateTenderId()
    {
        // جلب اسم آخر نوع عمل مختار (لو موجود)
        $workTypeName = '';
        if (!empty($this->currentWorkPath)) {
            $workTypeName = end($this->currentWorkPath);
        } elseif (!empty($this->selectedWorkTypes)) {
            $workTypeName = end($this->selectedWorkTypes)['path']
                ? end(end($this->selectedWorkTypes)['path'])
                : '';
        }

        // جلب اسم المدينة والمنطقة
        $cityName = $this->cityId ? City::find($this->cityId)?->title : '';
        $townName = $this->townId ? Town::find($this->townId)?->title : '';

        // دمجهم مع رقم المناقصة
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

    // تحديث method للتعامل مع working conditions
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

    // method جديد لحساب النتائج
    public function calculateScores()
    {
        // حساب اسكور التقديمات
        $this->totalSubmittalScore = 0;
        foreach ($this->submittalChecklist as $item) {
            if ($item['checked']) {
                $this->totalSubmittalScore += (int) $item['value' ?? 0];
            }
        }

        // حساب اسكور الشروط
        $this->totalConditionsScore = 0;
        foreach ($this->workingConditions as $condition) {
            if ($condition['checked']) {
                $this->totalConditionsScore += (int) ($condition['value'] ?? 0);
            }
        }

        // حساب السكور الإجمالي (مجموع الاتنين)
        $this->totalScore = $this->totalSubmittalScore + $this->totalConditionsScore;

        // حساب صعوبة المشروع بناءً على السكور الإجمالي
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

    // تحديث method للتعامل مع submittal checklist
    public function updatedSubmittalChecklist($value, $key)
    {
        $this->calculateScores();
    }

    // إزالة الـ methods القديمة واستبدالها بـ methods جديدة
    public function updated($propertyName)
    {
        // لو المستخدم اختار منطقة
        if ($propertyName === 'townId' && $this->townId) {
            $town = Town::find($this->townId);
            $this->townDistance = $town?->distance;
        }
        // إعادة حساب النتائج عند أي تحديث في الـ checklists
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

    public function openClientModal($type)
    {
        $this->modalClientType = $type;
        $clientTypeEnum = ClientType::tryFrom($type);
        $this->modalClientTypeLabel = $clientTypeEnum ? $clientTypeEnum->label() : 'عميل';
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
            'type' => $type,
        ];

        $this->resetValidation();
    }

    public function saveNewClient()
    {
        $this->validate([
            'newClient.cname' => 'required|string|max:255',
            'newClient.phone' => 'required|string|max:20',
            'newClient.email' => 'nullable|email|unique:clients,email',
            'newClient.gender' => 'required|in:male,female',
        ], [
            'newClient.cname.required' => 'اسم العميل مطلوب',
            'newClient.phone.required' => 'رقم الهاتف مطلوب',
            'newClient.email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'newClient.email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
        ]);

        try {
            DB::beginTransaction();
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
                'client_category_id' => $this->newClient['client_category_id'],
                'is_active' => $this->newClient['is_active'] ?? true,
                'type' => $this->modalClientType,
                'created_by' => Auth::id(),
                'tenant' => Auth::user()->tenant ?? 0,
                'branch' => Auth::user()->branch ?? 0,
                'branch_id' => Auth::user()->branch_id ?? 1,
            ]);

            Log::info('القيم عند الحفظ:', $this->newClient);

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

            // Force refresh the component
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

    private function getWireModelForClientType()
    {
        return match ($this->modalClientType) {
            ClientType::Person->value, ClientType::Company->value => 'clientId',
            ClientType::MainContractor->value => 'mainContractorId',
            ClientType::Consultant->value => 'consultantId',
            ClientType::Owner->value => 'ownerId',
            ClientType::ENGINEER->value => 'assignedEngineer',
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
            'type' => null,
        ];
    }

    public function save()
    {
        dd($this->all());
        // التحقق من البيانات
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
            $inquiry = Inquiry::create([
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

                // Work Type - آخر خطوة محددة
                'work_type_id' => !empty($this->currentWorkTypeSteps)
                    ? end($this->currentWorkTypeSteps)
                    : null,
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

                'project_size' => $this->projectSize,

                'client_priority' => $this->clientPriority,
                'kon_priority' => $this->konPriority,

                'types' => json_encode($selectedTypes),
                'unit' => json_encode($selectedUnits),
                'type_note' => $this->type_note,
            ]);

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
                    $projectDocument = ProjectDocument::firstOrCreate(
                        ['name' => $document['name']]
                    );

                    $inquiry->projectDocuments()->attach($projectDocument->id, [
                        'description' => $document['description'] ?? null
                    ]);
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

            DB::commit();

            session()->flash('message', 'تم حفظ الاستفسار بنجاح!');
            session()->flash('alert-type', 'success');

            return redirect()->route('inquiries.index');
        } catch (\Exception $e) {
            DB::rollBack();

            // تسجيل الخطأ
            Log::error('خطأ في حفظ الاستفسار: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('message', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
            session()->flash('alert-type', 'error');

            return back();
        }
    }

    public function render()
    {
        return view('inquiries::livewire.create-inquiry');
    }
}
