@extends('progress::layouts.daily-progress')

@section('title', __('general.project_dashboard'))

@section('content')
    <!-- Include Styles -->
    @include('progress::projects.components.project-dashboard.styles')
    
    <!-- Alpine Data Scope Wrapper -->
    <div x-data="{ 
            isCustomizationModalOpen: false,
            viewSettings: JSON.parse(localStorage.getItem('dashboardViewSettings_v2')) || {
                showStats: true,
                showAdvancedChart: true,
                showSubprojectsChart: true,
                showCategoriesChart: true,
                showItemsBySubproject: true,
                showItemsByCategory: true,
                showItemsByStatus: true,
                showHierarchicalView: true,
                showCharts: true,
                showWorkItems: true,
                showClientInfo: true,
                showTimeline: true,
                showRecentActivity: true,
                showTeamMembers: true
            }
         }"
         x-init="$watch('viewSettings', val => localStorage.setItem('dashboardViewSettings_v2', JSON.stringify(val)))">

        <div class="project-dashboard py-4">
            <div class="container dashboard-container">
                
                <!-- Customization Button & Header -->
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-outline-primary shadow-sm rounded-pill" @click="isCustomizationModalOpen = true">
                        <i class="fas fa-cog me-2"></i>{{ __('general.customize_view') }}
                    </button>
                </div>

                <!-- Header -->
                @include('progress::projects.components.project-dashboard.header')

                <!-- Stats Cards -->
                <div x-show="viewSettings.showStats" x-transition>
                    @include('progress::projects.components.project-dashboard.stats-cards')
                </div>

                <!-- Advanced Chart (Planned vs Actual) -->
                <div x-show="viewSettings.showAdvancedChart" x-transition>
                    @include('progress::projects.components.project-dashboard.advanced-chart')
                </div>

                <!-- Subprojects Chart -->
                <div x-show="viewSettings.showSubprojectsChart" x-transition>
                    @include('progress::projects.components.project-dashboard.subprojects-chart')
                </div>

                <!-- Categories Chart -->
                <div x-show="viewSettings.showCategoriesChart" x-transition>
                    @include('progress::projects.components.project-dashboard.categories-chart')
                </div>

                <!-- Items by Subproject Chart -->
                <div x-show="viewSettings.showItemsBySubproject" x-transition>
                    @include('progress::projects.components.project-dashboard.items-by-subproject-chart')
                </div>

                <!-- Items by Category Chart -->
                <div x-show="viewSettings.showItemsByCategory" x-transition>
                    @include('progress::projects.components.project-dashboard.items-by-category-chart')
                </div>



                <!-- Hierarchical View -->
                <div x-show="viewSettings.showHierarchicalView" x-transition>
                    @include('progress::projects.components.project-dashboard.hierarchical-view')
                </div>

                <!-- Charts -->
                <div x-show="viewSettings.showCharts" x-transition class="mb-4">
                    @include('progress::projects.components.project-dashboard.charts')
                </div>

                <!-- Client Info -->
                <div x-show="viewSettings.showClientInfo" x-transition class="mb-4">
                    @include('progress::projects.components.project-dashboard.client-info')
                </div>

                <!-- Timeline -->
                <div x-show="viewSettings.showTimeline" x-transition class="mb-4">
                    @include('progress::projects.components.project-dashboard.project-timeline')
                </div>

                <!-- Recent Activity -->
                <div x-show="viewSettings.showRecentActivity" x-transition class="mb-4">
                    @include('progress::projects.components.project-dashboard.recent-activity')
                </div>

                <!-- Team Members -->
                <div x-show="viewSettings.showTeamMembers" x-transition class="mb-4">
                    @include('progress::projects.components.project-dashboard.team-members')
                </div>

                <!-- Work Items Progress (Full Width) -->
                <div class="row mt-4" x-show="viewSettings.showWorkItems" x-transition>
                    <div class="col-12">
                         @include('progress::projects.components.project-dashboard.work-items-progress')
                    </div>
                </div>

            </div>
        </div>

        <!-- Include Modal (Outside Dashboard Container) -->
        @include('progress::projects.components.project-dashboard.customize-view-modal')

    </div>
@endsection
