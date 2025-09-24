<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use App\Models\City;
use App\Models\Client;
use App\Enums\ClientType;
use App\Models\Town;
use Modules\Inquiries\Models\WorkType;
use Modules\Inquiries\Models\InquirySource;
use Modules\Inquiries\Models\InquiryData;
use Modules\Progress\Models\ProjectProgress;

class CreateInquiry extends Component
{
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
    public $mainContractorId;
    public $consultantId;
    public $ownerId;
    public $isPriority = false;

    public $totalSubmittalScore;
    public $totalConditionsScore;
    public $projectDifficulty;

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

    public function mount()
    {
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
        $this->statusOptions = InquiryData::getStatusOptions();
        $this->statusForKonOptions = InquiryData::getStatusForKonOptions();
        $this->konTitleOptions = InquiryData::getKonTitleOptions();
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

    public function updatedProjectDocuments($value, $index)
    {
        if ($this->projectDocuments[$index]['name'] !== 'other') {
            $this->projectDocuments[$index]['description'] = '';
        }
    }

    public function updatedWorkingConditions($value, $index)
    {
        if ($this->workingConditions[$index]['checked'] && isset($this->workingConditions[$index]['options'])) {
            $this->workingConditions[$index]['value'] = $this->workingConditions[$index]['selectedOption'] ?? array_values($this->workingConditions[$index]['options'])[0];
        } else {
            $this->workingConditions[$index]['value'] = $this->workingConditions[$index]['checked'] ? $this->workingConditions[$index]['value'] : 0;
        }
    }

    public function getTotalSubmittalScoreProperty()
    {
        return array_sum(array_column(array_filter($this->submittalChecklist, fn($item) => $item['checked']), 'value'));
    }

    public function getTotalConditionsScoreProperty()
    {
        return array_sum(array_column(array_filter($this->workingConditions, fn($item) => $item['checked']), 'value'));
    }

    public function getProjectDifficultyProperty()
    {
        $score = $this->totalConditionsScore;
        if ($score < 6) return 1;
        if ($score <= 10) return 2;
        if ($score <= 15) return 3;
        return 4;
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
            'projectId' => 'required|exists:project_progress,id',
            'inquiryDate' => 'required|date',
            'reqSubmittalDate' => 'nullable|date',
            'projectStartDate' => 'nullable|date',
            'cityId' => 'required|exists:cities,id',
            'townId' => 'nullable|exists:towns,id',
            'status' => 'required|in:' . implode(',', array_column(InquiryData::getStatusOptions(), 'value')),
            'statusForKon' => 'nullable|in:' . implode(',', array_column(InquiryData::getStatusForKonOptions(), 'value')),
            'konTitle' => 'required|in:' . implode(',', array_column(InquiryData::getKonTitleOptions(), 'value')),
            'clientId' => 'nullable|exists:clients,id',
            'mainContractorId' => 'nullable|exists:clients,id',
            'consultantId' => 'nullable|exists:clients,id',
            'ownerId' => 'nullable|exists:clients,id',
            'isPriority' => 'boolean',
        ]);

        session()->flash('message', 'تم حفظ الاستفسار بنجاح!');
        return redirect()->route('inquiries.index');
    }

    public function render()
    {
        return view('inquiries::livewire.create-inquiry');
    }
}
