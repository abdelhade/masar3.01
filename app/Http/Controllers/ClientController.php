<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Models\ClientType;
use App\Http\Requests\ClientRequest;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\ClientCategory;
use RealRashid\SweetAlert\Facades\Alert;


class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view CRM Clients')->only(['index', 'show']);
        $this->middleware('permission:create CRM Clients')->only(['create', 'store']);
        $this->middleware('permission:edit CRM Clients')->only(['edit', 'update']);
        $this->middleware('permission:delete CRM Clients')->only(['destroy']);
    }

    public function index()
    {
        $clients = Client::paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $branches = userBranches();
        $clientTypes = ClientType::all();
        $categories = ClientCategory::all();
        return view('clients.create', compact('branches', 'categories', 'clientTypes'));
    }

    public function store(ClientRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;
            $data['created_by'] = Auth::id();

            Client::create($data);

            DB::commit();
            Alert::toast('تم إنشاء العميل بنجاح', 'success');
            return redirect()->route('clients.index');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::toast('حدث خطأ أثناء إنشاء العميل', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.show', compact('client'));
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $categories = ClientCategory::all();
        $clientTypes = ClientType::all();

        return view('clients.edit', compact('client', 'categories', 'clientTypes'));
    }


    public function update(ClientRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);

            $data = $request->all();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $client->update($data);

            DB::commit();
            Alert::toast('تم تحديث بيانات العميل بنجاح', 'success');
            return redirect()->route('clients.index');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::toast('حدث خطأ أثناء تحديث بيانات العميل', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();
            Alert::toast('تم حذف العنصر بنجاح', 'success');

            return redirect()->route('clients.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء حذف العميل', 'error');
            return redirect()->route('clients.index');
        }
    }

    public function toggleActive($id)
    {
        $client = Client::findOrFail($id);
        $client->is_active = !$client->is_active;
        $client->save();

        return response()->json([
            'success' => true,
            'status'  => $client->is_active ? 'نشط' : 'غير نشط',
        ]);
    }
}
