<?php

namespace Modules\Rentals\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Rentals\Http\Requests\RentalsBuildingRequest;
use Modules\Rentals\Models\RentalsBuilding;
use Modules\Rentals\Models\RentalsUnit;
use RealRashid\SweetAlert\Facades\Alert;

class RentalsBuildingController extends Controller
{
    public function index()
    {
        $buildings = RentalsBuilding::with('units')->get();
        $buildingUnits = RentalsUnit::buildings()->get();
        $itemUnits = RentalsUnit::items()->with('item')->get();
        $units = RentalsUnit::all(); // For compatibility with existing statistics

        return view('rentals::buildings.index', compact('buildings', 'buildingUnits', 'itemUnits', 'units'));
    }

    public function create()
    {
        $branches = userBranches();

        return view('rentals::buildings.create', compact('branches'));
    }

    public function store(RentalsBuildingRequest $request)
    {
        try {
            $data = $request->validated();
            RentalsBuilding::create($data);
            Alert::toast(__('Building added successfully'), 'success');

            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast(__('An error occurred while adding the building'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $building = RentalsBuilding::with([
            'units.leases.client',
        ])->findOrFail($id);

        return view('rentals::buildings.show', compact('building'));
    }

    public function edit($id)
    {
        $building = RentalsBuilding::findOrFail($id);

        return view('rentals::buildings.edit', compact('building'));
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $building = RentalsBuilding::findOrFail($id);
            $building->update($data);
            Alert::toast(__('Building data updated successfully'), 'success');

            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast(__('An error occurred while updating building data'), 'error');

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $building = RentalsBuilding::findOrFail($id);
            $building->units()->delete();
            $building->delete();
            Alert::toast(__('Building deleted successfully'), 'success');

            return redirect()->route('rentals.buildings.index');
        } catch (Exception) {
            Alert::toast(__('An error occurred while deleting the building'), 'error');

            return redirect()->back();
        }
    }
}
