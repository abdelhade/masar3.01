<?php

namespace Modules\CRM\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};
use Modules\CRM\Models\{Lead, Task, ChanceSource, LeadStatus, ClientContact};

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
        // Single query to get all client statistics
        $clientStats = Client::leftJoin('client_types', 'clients.client_type_id', '=', 'client_types.id')
            ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN client_types.title = "person" THEN 1 ELSE 0 END) as person_count,
        SUM(CASE WHEN client_types.title = "company" THEN 1 ELSE 0 END) as company_count
    ')
            ->first();


        // Get active clients count with single query
        $activeClients = Client::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('leads')
                ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
                ->whereColumn('clients.id', 'leads.client_id')
                ->where('lead_statuses.name', '!=', 'مغلق');
        })->count();

        return [
            'total' => $clientStats->total,
            'person' => $clientStats->person_count,
            'company' => $clientStats->company_count,
            'new_this_month' => $clientStats->new_this_month,
            'active' => $activeClients,
            'person_percentage' => $clientStats->total > 0 ? round(($clientStats->person_count / $clientStats->total) * 100, 1) : 0,
            'company_percentage' => $clientStats->total > 0 ? round(($clientStats->company_count / $clientStats->total) * 100, 1) : 0,
        ];
    }

    private function getLeadStatistics()
    {
        // Single query for basic lead statistics
        $leadStats = Lead::selectRaw('
            COUNT(*) as total,
            COALESCE(SUM(amount), 0) as total_value,
            SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month
        ', [Carbon::now()->month, Carbon::now()->year])
            ->first();

        $averageValue = $leadStats->total > 0 ? $leadStats->total_value / $leadStats->total : 0;

        // Get leads by status in single query with left join
        $leadsByStatus = LeadStatus::leftJoin('leads', 'lead_statuses.id', '=', 'leads.status_id')
            ->selectRaw('lead_statuses.*, COUNT(leads.id) as leads_count')
            ->groupBy('lead_statuses.id')
            ->get();

        // Get won leads count with single query
        $wonLeads = Lead::join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->where('lead_statuses.name', 'مغلق - فوز')
            ->count();

        $successRate = $leadStats->total > 0 ? round(($wonLeads / $leadStats->total) * 100, 1) : 0;

        return [
            'total' => $leadStats->total,
            'total_value' => $leadStats->total_value,
            'average_value' => round($averageValue, 2),
            'new_this_month' => $leadStats->new_this_month,
            'won_leads' => $wonLeads,
            'success_rate' => $successRate,
            'by_status' => $leadsByStatus,
        ];
    }

    private function getTaskStatistics()
    {
        // Single query for all task statistics including status and priority counts
        $now = Carbon::now();
        $today = Carbon::today();

        $taskStats = Task::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN priority = ? THEN 1 ELSE 0 END) as urgent,
            SUM(CASE WHEN priority = ? THEN 1 ELSE 0 END) as medium,
            SUM(CASE WHEN priority = ? THEN 1 ELSE 0 END) as low,
            SUM(CASE WHEN due_date < ? AND status != ? THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN DATE(due_date) = ? AND status != ? THEN 1 ELSE 0 END) as due_today
        ', [
            TaskStatusEnum::PENDING->value,
            TaskStatusEnum::IN_PROGRESS->value,
            TaskStatusEnum::COMPLETED->value,
            TaskStatusEnum::CANCELLED->value,
            TaskPriorityEnum::HIGH->value,
            TaskPriorityEnum::MEDIUM->value,
            TaskPriorityEnum::LOW->value,
            $now,
            TaskStatusEnum::COMPLETED->value,
            $today->format('Y-m-d'),
            TaskStatusEnum::COMPLETED->value
        ])->first();

        // Build status statistics from single query result
        $tasksByStatus = [];
        $statusCounts = [
            TaskStatusEnum::PENDING->value => $taskStats->pending,
            TaskStatusEnum::IN_PROGRESS->value => $taskStats->in_progress,
            TaskStatusEnum::COMPLETED->value => $taskStats->completed,
            TaskStatusEnum::CANCELLED->value => $taskStats->cancelled,
        ];

        foreach (TaskStatusEnum::cases() as $status) {
            $count = $statusCounts[$status->value] ?? 0;
            $tasksByStatus[] = [
                'status' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => $count,
                'percentage' => $taskStats->total > 0 ? round(($count / $taskStats->total) * 100, 1) : 0
            ];
        }

        // Build priority statistics from single query result
        $tasksByPriority = [];
        $priorityCounts = [
            TaskPriorityEnum::HIGH->value => $taskStats->urgent,
            TaskPriorityEnum::MEDIUM->value => $taskStats->medium,
            TaskPriorityEnum::LOW->value => $taskStats->low,
        ];

        foreach (TaskPriorityEnum::cases() as $priority) {
            $count = $priorityCounts[$priority->value] ?? 0;
            $tasksByPriority[] = [
                'priority' => $priority->value,
                'label' => $priority->label(),
                'color' => $priority->color(),
                'count' => $count,
                'percentage' => $taskStats->total > 0 ? round(($count / $taskStats->total) * 100, 1) : 0
            ];
        }

        return [
            'total' => $taskStats->total,
            'by_status' => $tasksByStatus,
            'by_priority' => $tasksByPriority,
            'overdue' => $taskStats->overdue,
            'due_today' => $taskStats->due_today,
        ];
    }

    private function getSourceStatistics()
    {
        // Single query with left join to get all sources with their lead counts
        $sources = ChanceSource::leftJoin('leads', 'chance_sources.id', '=', 'leads.source_id')
            ->selectRaw('chance_sources.title, COUNT(leads.id) as leads_count')
            ->groupBy('chance_sources.id', 'chance_sources.title')
            ->orderBy('leads_count', 'desc')
            ->get();

        // Calculate total from the results instead of separate query
        $totalLeads = $sources->sum('leads_count');

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
        // Single query to get both total contacts and unique clients
        $contactStats = ClientContact::selectRaw('
            COUNT(*) as total_contacts,
            COUNT(DISTINCT client_id) as unique_clients
        ')->first();

        $averageContactsPerClient = $contactStats->unique_clients > 0
            ? round($contactStats->total_contacts / $contactStats->unique_clients, 1)
            : 0;

        return [
            'total' => $contactStats->total_contacts,
            'average_per_client' => $averageContactsPerClient,
        ];
    }
}
