<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Modules\Notifications\Notifications\GeneralNotification;

class ProjectObserver
{
    public function updated(Project $project)
    {
        $this->checkDeadline($project);
    }

    public function retrieved(Project $project)
    {
        $this->checkDeadline($project);
    }

    private function checkDeadline(Project $project)
    {
        if ($project->status === 'completed' || !$project->end_date) {
            return;
        }

        $today = now()->startOfDay();
        $endDate = \Carbon\Carbon::parse($project->end_date)->startOfDay();

        if ($endDate->lt($today)) {
            $daysOverdue = $today->diffInDays($endDate);
            
            $users = User::all();
            Notification::send($users, new GeneralNotification(
                title: 'تأخر موعد تسليم مشروع',
                message: "المشروع \"{$project->name}\" تأخر عن موعد التسليم المتوقع بـ {$daysOverdue} يوم",
                url: route('projects.show', $project->id),
                type: 'warning',
                icon: 'las la-exclamation-triangle'
            ));
        }
    }
}
