<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\{CrmClient, Lead, Task, ChanceSource, LeadStatus, ClientContact};
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index()
    {
        $statistics = $this->getStatistics();
        return view('crm::statistics.index', compact('statistics'));
    }

    public function getStatistics()
    {
        return [
            // إحصائيات العملاء
            'clients' => $this->getClientStatistics(),

            // إحصائيات الفرص (Leads)
            'leads' => $this->getLeadStatistics(),

            // إحصائيات المهام
            'tasks' => $this->getTaskStatistics(),

            // إحصائيات المصادر
            'sources' => $this->getSourceStatistics(),

            // إحصائيات جهات الاتصال
            'contacts' => $this->getContactStatistics(),

        ];
    }

    private function getClientStatistics()
    {
        $totalClients = CrmClient::count();
        $personClients = CrmClient::where('type', 'person')->count();
        $companyClients = CrmClient::where('type', 'company')->count();

        // العملاء الجدد هذا الشهر
        $newClientsThisMonth = CrmClient::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // العملاء النشطون (الذين لديهم فرص نشطة)
        $activeClients = CrmClient::whereHas('leads', function ($query) {
            $query->whereHas('status', function ($statusQuery) {
                $statusQuery->where('name', '!=', 'مغلق');
            });
        })->count();

        return [
            'total' => $totalClients,
            'person' => $personClients,
            'company' => $companyClients,
            'new_this_month' => $newClientsThisMonth,
            'active' => $activeClients,
            'person_percentage' => $totalClients > 0 ? round(($personClients / $totalClients) * 100, 1) : 0,
            'company_percentage' => $totalClients > 0 ? round(($companyClients / $totalClients) * 100, 1) : 0,
        ];
    }

    private function getLeadStatistics()
    {
        $totalLeads = Lead::count();
        $totalValue = Lead::sum('amount') ?? 0;
        $averageValue = $totalLeads > 0 ? $totalValue / $totalLeads : 0;

        // الفرص حسب الحالة
        $leadsByStatus = LeadStatus::withCount('leads')->get();

        // الفرص الجديدة هذا الشهر
        $newLeadsThisMonth = Lead::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // الفرص المغلقة بنجاح
        $wonLeads = Lead::whereHas('status', function ($query) {
            $query->where('name', 'مغلق - فوز');
        })->count();

        // معدل النجاح
        $successRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        return [
            'total' => $totalLeads,
            'total_value' => $totalValue,
            'average_value' => round($averageValue, 2),
            'new_this_month' => $newLeadsThisMonth,
            'won_leads' => $wonLeads,
            'success_rate' => $successRate,
            'by_status' => $leadsByStatus,
        ];
    }

    private function getTaskStatistics()
    {
        $totalTasks = Task::count();

        // المهام حسب الحالة
        $tasksByStatus = [];
        foreach (TaskStatusEnum::cases() as $status) {
            $count = Task::where('status', $status->value)->count();
            $tasksByStatus[] = [
                'status' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => $count,
                'percentage' => $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0
            ];
        }

        // المهام حسب الأولوية
        $tasksByPriority = [];
        foreach (TaskPriorityEnum::cases() as $priority) {
            $count = Task::where('priority', $priority->value)->count();
            $tasksByPriority[] = [
                'priority' => $priority->value,
                'label' => $priority->label(),
                'color' => $priority->color(),
                'count' => $count,
                'percentage' => $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0
            ];
        }

        // المهام المتأخرة
        $overdueTasks = Task::where('due_date', '<', Carbon::now())
            ->where('status', '!=', TaskStatusEnum::COMPLETED->value)
            ->count();

        // المهام المستحقة اليوم
        $tasksDueToday = Task::whereDate('due_date', Carbon::today())
            ->where('status', '!=', TaskStatusEnum::COMPLETED->value)
            ->count();

        return [
            'total' => $totalTasks,
            'by_status' => $tasksByStatus,
            'by_priority' => $tasksByPriority,
            'overdue' => $overdueTasks,
            'due_today' => $tasksDueToday,
        ];
    }

    private function getSourceStatistics()
    {
        $sources = ChanceSource::withCount('leads')
            ->orderBy('leads_count', 'desc')
            ->get();

        $totalLeads = Lead::count();

        $sourceStats = $sources->map(function ($source) use ($totalLeads) {
            return [
                'title' => $source->title,
                'count' => $source->leads_count,
                'percentage' => $totalLeads > 0 ? round(($source->leads_count / $totalLeads) * 100, 1) : 0
            ];
        });

        return [
            'sources' => $sourceStats,
            'most_productive' => $sourceStats->first(),
        ];
    }

    private function getContactStatistics()
    {
        $totalContacts = ClientContact::count();

        // جهات الاتصال حسب العميل
        $contactsPerClient = ClientContact::select('client_id')
            ->groupBy('client_id')
            ->get()
            ->count();

        $averageContactsPerClient = $contactsPerClient > 0 ? round($totalContacts / $contactsPerClient, 1) : 0;

        return [
            'total' => $totalContacts,
            'average_per_client' => $averageContactsPerClient,
        ];
    }
}
