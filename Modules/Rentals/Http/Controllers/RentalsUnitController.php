<?php

namespace Modules\Rentals\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Modules\Rentals\Models\RentalsUnit;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Rentals\Models\RentalsBuilding;
use Modules\Rentals\Enums\UnitStatus;
use Modules\Rentals\Http\Requests\RentalsUnitRequest;

class RentalsUnitController extends Controller
{

    public function index()
    {
        return view('rentals::index');
    }

    public function create($id)
    {
        $building = RentalsBuilding::findOrFail($id);
        $floors = range(1, $building->floors);
        return view('rentals::units.create', compact('building', 'floors'));
    }

    public function store(RentalsUnitRequest $request)
    {
        try {
            $data = $request->validated();
            RentalsUnit::create($data);
            Alert::toast(__('Unit added successfully.'), 'success');
            return redirect()->route('rentals.buildings.show', $data['building_id']);
        } catch (Exception) {
            Alert::toast(__('An error occurred while adding the unit.'), 'error');
            return redirect()->back();
        }
    }

    public function show($id)
    {
        // return view('rentals::show');
    }

    public function edit($id)
    {
        $unit = RentalsUnit::findOrFail($id);
        $building = $unit->building;

        $count = (int) ($building->floors ?? $building->number_of_floors ?? 10);
        $count = max(1, $count);
        $floors = range(1, $count);

        return view('rentals::units.edit', compact('unit', 'building', 'floors'));
    }

    public function update(RentalsUnitRequest $request, $id)
    {
        try {
            $unit = RentalsUnit::findOrFail($id);

            $data = $request->validated();
            if (!isset($data['status'])) {
                $data['status'] = UnitStatus::AVAILABLE;
            }

            if (empty($data['details'])) {
                $data['details'] = null;
            }

            $unit->update($data);

            Alert::toast(__('Unit updated successfully.'), 'success');
            return redirect()->back();
        } catch (\Exception) {
            Alert::toast(__('An error occurred while updating the unit.'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $unit = RentalsUnit::findOrFail($id);
            $unit->delete();
            Alert::toast(__('Unit deleted successfully.'), 'success');
            return redirect()->back();
        } catch (Exception) {
            Alert::toast(__('An error occurred while deleting the unit.'), 'error');
            return redirect()->back();
        }
    }
}
