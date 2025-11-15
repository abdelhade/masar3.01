<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\ClientTypeRequest;
use Modules\CRM\Models\ClientType;
use RealRashid\SweetAlert\Facades\Alert;

class ClientTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Client Types')->only(['index']);
        $this->middleware('can:create Client Types')->only(['create', 'store']);
        $this->middleware('can:edit Client Types')->only(['edit', 'update']);
        $this->middleware('can:delete Client Types')->only(['destroy']);
    }

    public function index()
    {
        $customerTypes = ClientType::paginate(20);
        return view('crm::client-type.index', compact('customerTypes'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('crm::client-type.create', compact('branches'));
    }

    public function store(ClientTypeRequest $request)
    {
        ClientType::create($request->validated());
        Alert::toast(__('Client type created successfully'), 'success');
        return redirect()->route('client-types.index');
    }

    public function edit(ClientType $client_type)
    {
        return view('crm::client-type.edit', compact('client_type'));
    }

    public function update(ClientTypeRequest $request, ClientType $client_type)
    {
        $client_type->update($request->validated());
        Alert::toast(__('Client type updated successfully'), 'success');
        return redirect()->route('client-types.index');
    }

    public function destroy(ClientType $client_type)
    {
        try {
            $client_type->delete();
            Alert::toast(__('Client type deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the client type'), 'error');
        }
        return redirect()->route('client-types.index');
    }
}
