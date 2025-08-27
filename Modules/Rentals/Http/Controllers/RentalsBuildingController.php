<?php

namespace Modules\Rentals\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Rentals\Models\RentalsUnit;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Rentals\Models\RentalsBuilding;
use Modules\Rentals\Http\Requests\RentalsBuildingRequest;

class RentalsBuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buildings = RentalsBuilding::all();
        $units = RentalsUnit::all();
        return view('rentals::buildings.index', compact('buildings', 'units'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rentals::buildings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalsBuildingRequest $request)
    {
        try {
            $data = $request->validated();
            RentalsBuilding::create($data);
            Alert::toast('تم إضافة المبنى بنجاح', 'success');
            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء إضافة المبنى', 'error');
            return redirect()->back();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $building = RentalsBuilding::findOrFail($id);
        $units = RentalsUnit::where('building_id', $id)->get();
        return view('rentals::buildings.show', compact('building', 'units'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $building = RentalsBuilding::findOrFail($id);
        return view('rentals::buildings.edit', compact('building'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $building = RentalsBuilding::findOrFail($id);
            $building->update($data);
            Alert::toast('تم تعديل بيانات المبنى بنجاح', 'success');
            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء تعديل بيانات المبنى', 'error');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $building = RentalsBuilding::findOrFail($id);
            $building->units()->delete();
            $building->delete();
            Alert::toast('تم حذف المبنى بنجاح', 'success');
            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء حذف المبنى', 'error');
            return redirect()->back();
        }
    }
}
