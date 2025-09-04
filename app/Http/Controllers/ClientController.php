<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ClientRequest;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;


class ClientController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:عرض العملااء')->only(['index']);
    //     $this->middleware('can:عرض تفاصيل عميل')->only(['show']);
    //     $this->middleware('can:إضافة العملااء')->only(['create', 'store']);
    //     $this->middleware('can:تعديل العملااء')->only(['edit', 'update']);
    //     $this->middleware('can:حذف العملااء')->only(['destroy']);
    // }

    public function index()
    {
        $clients = Client::paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            Client::create([
                'cname'            => $request->cname,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'phone2'           => $request->phone2,
                'address'          => $request->address,
                'address2'         => $request->address2,
                'date_of_birth'    => $request->date_of_birth,
                'national_id'      => $request->national_id,
                'contact_person'   => $request->contact_person,
                'contact_phone'    => $request->contact_phone,
                'contact_relation' => $request->contact_relation,
                'info'             => $request->info,
                'job'              => $request->job,
                'gender'           => $request->gender,
                'type'             => $request->type,
                'is_active'        => $request->has('is_active') ? 1 : 0,
                'created_by' => Auth::id(),
            ]);
            DB::commit();
            Alert::toast('تم إنشاء العميل بنجاح', 'success');
            return redirect()->route('clients.index');
        } catch (Exception) {
            DB::rollBack();
            Alert::toast('حدث خطأ أثناء إنشاء العميل', 'error');
            return redirect();
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
        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $client = Client::findOrFail($id);
            $client->update([
                'cname'            => $request->cname,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'phone2'           => $request->phone2,
                'address'          => $request->address,
                'address2'         => $request->address2,
                'date_of_birth'    => $request->date_of_birth,
                'national_id'      => $request->national_id,
                'contact_person'   => $request->contact_person,
                'contact_phone'    => $request->contact_phone,
                'contact_relation' => $request->contact_relation,
                'info'             => $request->info,
                'job'              => $request->job,
                'gender'           => $request->gender,
                'type'             => $request->type,
                'is_active'        => $request->has('is_active') ? 1 : 0,
            ]);

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
