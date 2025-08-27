<?php

namespace Modules\Rentals\Http\Controllers;

use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Rentals\Models\RentalsUnit;
use Modules\Rentals\Models\RentalsLease;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Rentals\Http\Requests\RentalsLeaseRequest;

class RentalsLeaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leases = RentalsLease::all();
        return view('rentals::leases.index', compact('leases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = RentalsUnit::pluck('name', 'id');
        $clients = Client::pluck('cname', 'id');
        return view('rentals::leases.create', compact('units', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalsLeaseRequest $request)
    {
        try {
            $data = $request->validated();
            RentalsLease::create($data);
            Alert::toast('تم إضافة العقد بنجاح.', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء إضافة العقد: ', 'error');
            return redirect()->back();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('rentals::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lease = RentalsLease::findOrFail($id);
        $clients = Client::pluck('cname', 'id');
        $units = RentalsUnit::pluck('name', 'id');
        return view('rentals::leases.edit', compact('lease', 'units', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentalsLeaseRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $lease = RentalsLease::findOrFail($id);
            $lease->update($data);
            Alert::toast('تم تحديث العقد بنجاح.', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء تحديث العقد: ', 'error');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $lease = RentalsLease::findOrFail($id);
            $lease->delete();
            Alert::toast('تم حذف العقد بنجاح.', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء حذف العقد: ', 'error');
            return redirect()->back();
        }
    }
}
