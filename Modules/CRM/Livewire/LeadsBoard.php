<?php

namespace Modules\CRM\Livewire;

use App\Models\User;
use App\Models\Client;
use Livewire\Component;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\LeadStatus;
use Modules\CRM\Models\ChanceSource;

class LeadsBoard extends Component
{
    public $statuses;
    public $leads;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showReportModal = false;
    public $selectedStatus = null;
    public $selectedStatusForReport = null;
    public $selectedLead = null;
    public $sources;
    public $reportData = [];

    // بيانات الفرصة الجديدة
    public $newLead = [
        'title' => '',
        'client_id' => '',
        'amount' => '',
        'source' => '',
        'assigned_to' => '',
        'description' => ''
    ];

    // بيانات الفرصة للتعديل
    public $editingLead = [
        'id' => '',
        'title' => '',
        'client_id' => '',
        'amount' => '',
        'source' => '',
        'assigned_to' => '',
        'description' => ''
    ];

    public $clients;
    public $users;

    protected $rules = [
        'newLead.title' => 'required|string|max:255',
        'newLead.client_id' => 'required|exists:clients,id',
        'newLead.amount' => 'nullable|numeric|min:0',
        'newLead.source' => 'nullable|exists:chance_sources,id',
        'newLead.assigned_to' => 'nullable|exists:users,id',
        'newLead.description' => 'nullable|string'
    ];

    protected $messages = [
        'newLead.title.required' => 'عنوان الفرصة مطلوب',
        'newLead.title.max' => 'عنوان الفرصة يجب أن يكون أقل من 255 حرف',
        'newLead.client_id.required' => 'يجب اختيار العميل',
        'newLead.client_id.exists' => 'العميل المحدد غير موجود',
        'newLead.amount.numeric' => 'القيمة يجب أن تكون رقماً',
        'newLead.amount.min' => 'القيمة يجب أن تكون أكبر من أو تساوي صفر',
        'newLead.assigned_to.exists' => 'المستخدم المسؤول غير موجود',
        'newLead.source.exists' => 'المصدر المحدد غير موجود',
    ];

    public function mount()
    {
        // تحقق من وجود حالات الفرص
        $statusCount = LeadStatus::count();
        if ($statusCount === 0) {
            // إنشاء حالات افتراضية
            $this->createDefaultStatuses();
        }

        $this->loadData();
        $this->clients = Client::all();
        $this->users = User::all();
        $this->sources = ChanceSource::all();
    }

    private function createDefaultStatuses()
    {
        $defaultStatuses = [
            ['name' => 'جديد', 'color' => '#007bff', 'order_column' => 1],
            ['name' => 'قيد المتابعة', 'color' => '#ffc107', 'order_column' => 2],
            ['name' => 'مؤهل', 'color' => '#28a745', 'order_column' => 3],
            ['name' => 'مُتم', 'color' => '#6f42c1', 'order_column' => 4],
            ['name' => 'مُلغى', 'color' => '#dc3545', 'order_column' => 5],
        ];

        foreach ($defaultStatuses as $status) {
            LeadStatus::create($status);
        }
    }

    public function loadData()
    {
        try {
            $this->statuses = LeadStatus::orderBy('order_column')->get();

            $this->leads = Lead::with(['client', 'status', 'assignedTo', 'source'])
                ->get()
                ->groupBy('status_id')
                ->map(function ($leads) {
                    return $leads->map(function ($lead) {
                        return [
                            'id' => $lead->id,
                            'title' => $lead->title,
                            'client' => $lead->client ? $lead->client->only('name') : null,
                            'amount' => $lead->amount,
                            'source' => $lead->source,
                            'assigned_to' => $lead->assignedTo ? $lead->assignedTo->only('name') : null,
                            'description' => $lead->description
                        ];
                    });
                });
        } catch (\Exception $e) {
            $this->statuses = collect([]);
            $this->leads = collect([]);
            session()->flash('error', 'حدث خطأ في تحميل البيانات: ' . $e->getMessage());
        }
    }

    // دالة تغيير الحالة عن طريق drag & drop
    public function updateLeadStatus($leadId, $newStatusId)
    {
        try {
            $lead = Lead::find($leadId);
            if ($lead) {
                $lead->changeStatus($newStatusId);
                $this->loadData();

                $this->dispatch('lead-moved', [
                    'leadId' => $leadId,
                    'newStatus' => LeadStatus::find($newStatusId)->name
                ]);

                session()->flash('message', 'تم تحديث حالة الفرصة بنجاح!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ في تحديث الحالة: ' . $e->getMessage());
        }
    }

    // فتح نافذة إضافة فرصة جديدة
    public function openAddModal($statusId = null)
    {
        $this->selectedStatus = $statusId;
        $this->showAddModal = true;
        $this->resetNewLead();
    }

    // فتح نافذة تعديل الفرصة
    public function editLead($leadId)
    {
        try {
            $lead = Lead::with(['client', 'assignedTo', 'source'])->findOrFail($leadId);

            $this->selectedLead = $lead;
            $this->editingLead = [
                'id' => $lead->id,
                'title' => $lead->title,
                'client_id' => $lead->client_id,
                'amount' => $lead->amount,
                'source' => $lead->source_id,
                'assigned_to' => $lead->assigned_to,
                'description' => $lead->description
            ];

            $this->showEditModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ في تحميل بيانات الفرصة: ' . $e->getMessage());
        }
    }

    // تحديث الفرصة
    public function updateLead()
    {
        $this->validate([
            'editingLead.title' => 'required|string|max:255',
            'editingLead.client_id' => 'required|exists:crm_clients,id',
            'editingLead.amount' => 'nullable|numeric|min:0',
            'editingLead.source' => 'nullable|exists:chance_sources,id',
            'editingLead.assigned_to' => 'nullable|exists:users,id',
            'editingLead.description' => 'nullable|string'
        ], [
            'editingLead.title.required' => 'عنوان الفرصة مطلوب',
            'editingLead.title.max' => 'عنوان الفرصة يجب أن يكون أقل من 255 حرف',
            'editingLead.client_id.required' => 'يجب اختيار العميل',
            'editingLead.client_id.exists' => 'العميل المحدد غير موجود',
            'editingLead.amount.numeric' => 'القيمة يجب أن تكون رقماً',
            'editingLead.amount.min' => 'القيمة يجب أن تكون أكبر من أو تساوي صفر',
            'editingLead.assigned_to.exists' => 'المستخدم المسؤول غير موجود',
            'editingLead.source.exists' => 'المصدر المحدد غير موجود',
        ]);

        try {
            $lead = Lead::findOrFail($this->editingLead['id']);
            $lead->update([
                'title' => $this->editingLead['title'],
                'client_id' => $this->editingLead['client_id'],
                'amount' => $this->editingLead['amount'],
                'source_id' => $this->editingLead['source'],
                'assigned_to' => $this->editingLead['assigned_to'],
                'description' => $this->editingLead['description']
            ]);

            $this->closeModal();
            $this->loadData();
            session()->flash('message', 'تم تحديث الفرصة بنجاح!');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء تحديث الفرصة: ' . $e->getMessage());
        }
    }

    // فتح تقرير المرحلة
    public function openStatusReport($statusId)
    {
        try {
            $this->selectedStatusForReport = LeadStatus::findOrFail($statusId);

            $leads = Lead::with(['client', 'assignedTo', 'source'])
                ->where('status_id', $statusId)
                ->get();

            $this->reportData = [
                'total_leads' => $leads->count(),
                'total_amount' => $leads->sum('amount'),
                'avg_amount' => $leads->count() > 0 ? $leads->avg('amount') : 0,
                'leads_by_source' => $leads->groupBy('source.title')->map->count(),
                'leads_by_user' => $leads->groupBy('assignedTo.name')->map->count(),
                'leads_details' => $leads->map(function ($lead) {
                    return [
                        'title' => $lead->title,
                        'client_name' => $lead->client->name ?? 'غير محدد',
                        'amount' => $lead->amount,
                        'source' => $lead->source->title ?? 'غير محدد',
                        'assigned_to' => $lead->assignedTo->name ?? 'غير مُعين',
                        'created_at' => $lead->created_at->format('Y-m-d')
                    ];
                })
            ];

            $this->showReportModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ في تحميل التقرير: ' . $e->getMessage());
        }
    }

    // إغلاق النوافذ
    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showReportModal = false;
        $this->selectedStatus = null;
        $this->selectedLead = null;
        $this->selectedStatusForReport = null;
        $this->resetNewLead();
        $this->resetEditingLead();
        $this->resetErrorBag();
    }

    // إضافة فرصة جديدة
    public function addLead()
    {
        $this->validate();

        try {
            $leadData = $this->newLead;
            if ($this->selectedStatus) {
                $leadData['status_id'] = $this->selectedStatus;
            } else {
                $firstStatus = LeadStatus::orderBy('order_column')->first();
                if ($firstStatus) {
                    $leadData['status_id'] = $firstStatus->id;
                } else {
                    session()->flash('error', 'يجب إنشاء حالات الفرص أولاً');
                    return;
                }
            }

            // تحويل source إلى source_id
            if (!empty($leadData['source'])) {
                $leadData['source_id'] = $leadData['source'];
                unset($leadData['source']);
            }

            Lead::create($leadData);
            $this->closeModal();
            $this->loadData();

            session()->flash('message', 'تم إضافة الفرصة بنجاح!');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء إضافة الفرصة: ' . $e->getMessage());
        }
    }

    // إعادة تعيين بيانات الفرصة الجديدة
    private function resetNewLead()
    {
        $this->newLead = [
            'title' => '',
            'client_id' => '',
            'amount' => '',
            'source' => '',
            'assigned_to' => '',
            'description' => ''
        ];
    }

    // إعادة تعيين بيانات الفرصة للتعديل
    private function resetEditingLead()
    {
        $this->editingLead = [
            'id' => '',
            'title' => '',
            'client_id' => '',
            'amount' => '',
            'source' => '',
            'assigned_to' => '',
            'description' => ''
        ];
    }

    // حذف فرصة
    public function deleteLead($leadId)
    {
        try {
            $lead = Lead::find($leadId);
            if ($lead) {
                $lead->delete();
                $this->loadData();
                session()->flash('message', 'تم حذف الفرصة بنجاح!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء حذف الفرصة: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('crm::livewire.leads-board');
    }
}
