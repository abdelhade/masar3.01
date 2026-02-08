@extends('progress::layouts.app')

@section('title', __('projects.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.projects.index') }}" class="text-decoration-none">
            {{ __('general.projects') }}
        </a>
    </li>
    <li class="breadcrumb-item active">
        {{ __('projects.create') }}
    </li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-folder-plus me-2"></i>
                        {{ __('projects.create') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('progress.projects.store') }}" 
          method="POST" 
          id="projectForm" 
          novalidate>
        @include('progress::projects.form.index')
    </form>
</div>
@endsection

@push('styles')
<style>
    
    .drag-handle {
        cursor: move;
    }
    
    .drag-handle:hover {
        color: #0d6efd !important;
    }

    .sortable-chosen {
        background-color: #e7f3ff;
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    
    .category-group {
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        user-select: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .category-header:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .category-header.collapsed {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .category-title {
        font-weight: 600;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 200px;
    }

    .category-icon {
        font-size: 1.4rem;
    }
    
    .category-data-inline {
        font-size: 0.9rem;
        flex-wrap: wrap;
    }
    
    .category-data-inline .data-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0;
    }
    
    .category-data-inline .data-item strong {
        font-size: 1rem;
        color: #fff;
    }
    
    .category-data-inline .vr {
        height: 20px;
        width: 1px;
    }
    
    
    .category-data-inline input[type="number"] {
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
        text-align: center;
        border-radius: 0.25rem;
    }
    
    .category-data-inline input[type="number"]:focus {
        background: rgba(255,255,255,0.3) !important;
        outline: none;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
    }

    .category-stats {
        display: flex;
        gap: 1.5rem;
        font-size: 0.9rem;
        opacity: 0.95;
    }

    .category-stat {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .category-toggle {
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .category-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .category-body {
        background: white;
        transition: all 0.3s ease;
    }

    .category-body.collapsed {
        display: none;
    }

    .category-items-table {
        margin: 0;
        table-layout: fixed;
        width: 100%;
    }

    .category-items-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    
    .category-items-table td:nth-child(1),
    .category-items-table th:nth-child(1) { width: 40px !important; min-width: 40px; max-width: 40px; }   
    
    .category-items-table td:nth-child(2),
    .category-items-table th:nth-child(2) { width: 40px !important; min-width: 40px; max-width: 40px; }   
    
    .category-items-table td:nth-child(3),
    .category-items-table th:nth-child(3) { width: 50px !important; min-width: 50px; max-width: 50px; }   
    
    .category-items-table td:nth-child(4),
    .category-items-table th:nth-child(4) { width: 300px !important; min-width: 200px; }  
    
    .category-items-table td:nth-child(5),
    .category-items-table th:nth-child(5) { width: 200px !important; min-width: 150px; }  
    
    .category-items-table td:nth-child(6),
    .category-items-table th:nth-child(6) { 
        width: 350px !important; 
        min-width: 350px !important; 
        max-width: 350px !important;
    }  
    
    .category-items-table td:nth-child(7),
    .category-items-table th:nth-child(7) { width: 100px !important; min-width: 90px; max-width: 100px; }  
    
    .category-items-table td:nth-child(8),
    .category-items-table th:nth-child(8) { width: 120px !important; min-width: 100px; max-width: 120px; }  
    
    .category-items-table td:nth-child(9),
    .category-items-table th:nth-child(9) { width: 120px !important; min-width: 100px; max-width: 120px; }  
    
    .category-items-table td:nth-child(10),
    .category-items-table th:nth-child(10) { width: 100px !important; min-width: 80px; max-width: 100px; }  
    
    .category-items-table td:nth-child(11),
    .category-items-table th:nth-child(11) { width: 150px !important; min-width: 120px; max-width: 150px; } 
    
    .category-items-table td:nth-child(12),
    .category-items-table th:nth-child(12) { width: 150px !important; min-width: 120px; max-width: 150px; } 
    
    .category-items-table td:nth-child(13),
    .category-items-table th:nth-child(13) { width: 100px !important; min-width: 80px; max-width: 100px; } 
    
    .category-items-table td:nth-child(14),
    .category-items-table th:nth-child(14) { width: 140px !important; min-width: 120px; max-width: 140px; } 
    
    .category-items-table td:nth-child(15),
    .category-items-table th:nth-child(15) { width: 140px !important; min-width: 120px; max-width: 140px; } 
    
    .category-items-table td:nth-child(16),
    .category-items-table th:nth-child(16) { width: 100px !important; min-width: 90px; max-width: 100px; } 
    
    
    .category-items-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0.5rem !important;
    }
    
    
    .category-items-table td:nth-child(4) {  
        white-space: normal;
        word-wrap: break-word;
        overflow: visible;
    }
    
    .category-items-table td:nth-child(6) { 
        white-space: normal;
        word-wrap: break-word;
        overflow: hidden !important;
    }
    
    
    .category-items-table input[type="text"],
    .category-items-table input[type="number"],
    .category-items-table input[type="date"],
    .category-items-table select {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
    }

    
    .category-header[data-category="كهرباء"],
    .category-header[data-category="electrical"] {
        background: linear-gradient(135deg, #FFB020 0%, #FF8C00 100%);
    }

    .category-header[data-category="سباكة"],
    .category-header[data-category="plumbing"] {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
    }

    .category-header[data-category="نجارة"],
    .category-header[data-category="carpentry"] {
        background: linear-gradient(135deg, #8B4513 0%, #654321 100%);
    }

    .category-header[data-category="دهانات"],
    .category-header[data-category="painting"] {
        background: linear-gradient(135deg, #E91E63 0%, #C2185B 100%);
    }

    .category-header[data-category="uncategorized"] {
        background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%);
    }

    
    .category-empty-state {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }
    
    
    .form-label .text-danger {
        font-size: 1.2rem;
        margin-left: 3px;
    }
    
    
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    
    .is-valid {
        border-color: #198754 !important;
    }
</style>
@endpush

@push('scripts')
    <script src="{{ asset('js/template-predecessor-fix.js') }}"></script>
@endpush
