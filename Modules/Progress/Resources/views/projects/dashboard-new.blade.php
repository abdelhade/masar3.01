@extends('progress::layouts.app')

@section('title', __('general.project_dashboard'))

@section('content')
    @include('progress::components.project-dashboard.styles')

    <div class="project-dashboard">
        <div class="dashboard-container">
            @include('progress::components.project-dashboard.header', ['project' => $project, 'projectStatus' => $projectStatus])

            @include('progress::components.project-dashboard.actions', ['project' => $project])

            @include('progress::components.project-dashboard.stats-cards', [
                'totalItems' => $totalItems,
                'overallProgress' => $overallProgress,
                'daysPassed' => $daysPassed,
                'daysRemaining' => $daysRemaining,
                'totalEmployees' => $totalEmployees
            ])

            
            <div class="container-fluid">
                <div class="row g-4 mb-5">
                    @include('progress::components.project-dashboard.client-info', ['project' => $project])

                    @include('progress::components.project-dashboard.team-members', ['project' => $project])
                </div>
            </div>

            <div class="container-fluid">
                @include('progress::components.project-dashboard.charts', [
                    'chartData' => $chartData,
                    'overallProgress' => $overallProgress
                ])

                @include('progress::components.project-dashboard.work-items-progress', [
                    'project' => $project,
                    'totalItems' => $totalItems
                ])

                
                <div class="row g-4">
                    @include('progress::components.project-dashboard.recent-activity', ['recentProgress' => $recentProgress])

                    @include('progress::components.project-dashboard.project-timeline', [
                        'project' => $project,
                        'overallProgress' => $overallProgress,
                        'daysPassed' => $daysPassed,
                        'daysRemaining' => $daysRemaining
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

