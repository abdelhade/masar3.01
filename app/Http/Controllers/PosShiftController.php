<?php

namespace App\Http\Controllers;
use App\Models\PosShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosShiftController extends Controller
{
    public function index()
    {
        $shifts = PosShift::with('user')->latest()->get();
        return view('pos_shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('pos_shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        PosShift::create([
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        return redirect()->route('pos-shifts.index')->with('success', 'تم فتح الشيفت بنجاح');
    }


    // عرض فورم الإغلاق
    public function close(PosShift $shift)
    {
        if ($shift->status !== 'open') {
            return redirect()->route('pos-shifts.index')->with('error', 'الشيفت مغلق بالفعل.');
        }

        return view('pos_shifts.close', compact('shift'));
    }

    // حفظ عملية الإغلاق
    public function closeConfirm(Request $request, PosShift $shift)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift->update([
            'closing_balance' => $request->closing_balance,
            'closed_at' => now(),
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos-shifts.index')->with('success', 'تم إغلاق الشيفت بنجاح.');
    }
}