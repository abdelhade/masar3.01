<?php

namespace Modules\CRM\Http\Controllers;


use Modules\CRM\Models\ChanceSource;
use Modules\CRM\Http\Requests\ChanceSourceRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Routing\Controller;

class ChanceSourceController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض مصدر الفرص')->only(['index']);
        $this->middleware('can:إضافة مصدر الفرص')->only(['create', 'store']);
        $this->middleware('can:تعديل مصدر الفرص')->only(['edit', 'update']);
        $this->middleware('can:حذف مصدر الفرص')->only(['destroy']);
    }

    public function index()
    {
        $chanceSources = ChanceSource::all();
        return view('crm::chance-source.index', compact('chanceSources'));
    }

    public function create()
    {
        return view('crm::chance-source.create');
    }

    public function store(ChanceSourceRequest $request)
    {
        ChanceSource::create($request->validated());
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('chance-sources.index');
    }

    public function show($id)
    {
        // return view('crm::show');
    }

    public function edit($id)
    {
        $chanceSource = ChanceSource::findOrFail($id);

        return view('crm::chance-source.edit', compact('chanceSource'));
    }

    public function update(ChanceSourceRequest $request, $id)
    {
        $chanceSource = ChanceSource::findOrFail($id);
        $chanceSource->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('chance-sources.index');
    }

    public function destroy($id)
    {
        $chanceSource = ChanceSource::findOrFail($id);
        $chanceSource->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('chance-sources.index');
    }
}
