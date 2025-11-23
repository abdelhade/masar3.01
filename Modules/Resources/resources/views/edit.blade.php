@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">تعديل المورد: {{ $resource->name }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @livewire('edit-resource', ['resource' => $resource])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

