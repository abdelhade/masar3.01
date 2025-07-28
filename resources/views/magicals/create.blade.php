@extends('admin.dashboard')
@section('content')
    <div class="container">
        <div class="card mb-4" style="font-family: 'Cairo', sans-serif; direction: rtl;">
            <div class="card-header bg-primary text-white">
                <h2>magical forms</h2>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('magicals.store') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="magic_name">الاسم</label>
                            <input type="text" name="magic_name" class="form-control form-control-sm" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="magic_link">النوع</label>
                            <input type="text" name="magic_link" class="form-control form-control-sm" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="is_journal">له قيد</label>
                            <div class="form-check">
                                <input class="form-check-input" unchecked type="checkbox" name="is_journal" id="is_journal" value="1">
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="info">الوصف</label>
                            <input type="text" name="info" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="row">
                        @livewire('magicals.magical-form')
                    </div>

                    <button type="submit" class="btn btn-primary">اضافة</button>
                </form>
            </div>
        </div>

    </div>
@endsection