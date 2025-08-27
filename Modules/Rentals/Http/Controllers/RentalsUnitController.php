<?php

namespace Modules\Rentals\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Modules\Rentals\Models\RentalsUnit;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Rentals\Models\RentalsBuilding;
use Modules\Rentals\Enums\UnitStatus;
use Modules\Rentals\Http\Requests\RentalsUnitRequest;

class RentalsUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('rentals::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $building = RentalsBuilding::findOrFail($id);
        $floors = range(1, $building->floors);
        return view('rentals::units.create', compact('building', 'floors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalsUnitRequest $request)
    {
        try {
            $data = $request->validated();
            RentalsUnit::create($data);
            Alert::toast('تم إضافة الوحدة بنجاح.', 'success');
            return redirect()->route('rentals.buildings.show', $data['building_id'])
                ->with('success', 'تم إضافة الوحدة بنجاح.');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء إضافة الوحدة: ', 'error');
            return redirect()->back();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // return view('rentals::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $unit = RentalsUnit::findOrFail($id);
        $building = $unit->building;

        $count = (int) ($building->floors ?? $building->number_of_floors ?? 10);
        $count = max(1, $count);
        $floors = range(1, $count);

        return view('rentals::units.edit', compact('unit', 'building', 'floors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentalsUnitRequest $request, $id)
    {
        // try {
        $unit = RentalsUnit::findOrFail($id);

        $data = $request->validated();
        if (!isset($data['status'])) {
            $data['status'] = UnitStatus::AVAILABLE;
        }

        if (empty($data['details'])) {
            $data['details'] = null;
        }

        $unit->update($data);

        Alert::toast('تم تحديث الوحدة بنجاح.', 'success');
        return redirect()->back()
            ->with('success', 'تم تحديث الوحدة بنجاح.');
        // } catch (\Exception $e) {
        //     Alert::toast('حدث خطأ أثناء تحديث الوحدة.', 'error');
        //     return redirect()->back()->withInput();
        // }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $unit = RentalsUnit::findOrFail($id);
            $unit->delete();
            Alert::toast('تم حذف الوحدة بنجاح.', 'success');
            return redirect()->back()
                ->with('success', 'تم حذف الوحدة بنجاح.');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء حذف الوحدة: ', 'error');
            return redirect()->back();
        }
    }
}
