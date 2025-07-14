<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض العملاء')->only(['index']);
        $this->middleware('can:عرض تفاصيل عميل')->only(['show']);
        $this->middleware('can:إنشاء العملاء')->only(['create', 'store']);
        $this->middleware('can:تعديل العملاء')->only(['edit', 'update']);
        $this->middleware('can:حذف العملاء')->only(['destroy']);
    }

    // عرض جميع العملاء
    public function index()
    {
        $clients = Client::all(); // جلب جميع العملاء
        return view('clients.index', compact('clients')); // عرض البيانات في صفحة
    }

    // عرض نموذج إضافة عميل جديد
    public function create()
    {
        return view('clients.create');
    }

    // تخزين عميل جديد في قاعدة البيانات
    public function store(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'company' => 'nullable|string|max:100',
            'info' => 'nullable|string|max:500',
        ]);

        // إضافة عميل جديد
        Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'info' => $request->info,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client added successfully');
    }

    // عرض تفاصيل عميل معين
    public function show($id)
    {
        $client = Client::findOrFail($id); // جلب العميل بناءً على ID
        return view('clients.show', compact('client')); // عرض التفاصيل
    }

    // عرض نموذج تعديل عميل معين
    public function edit($id)
    {
        $client = Client::findOrFail($id); // العثور على العميل بناءً على ID
        return view('clients.edit', compact('client')); // عرض نموذج التعديل
    }

    // تحديث بيانات العميل في قاعدة البيانات
    public function update(Request $request, $id)
    {
        // التحقق من المدخلات
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'company' => 'nullable|string|max:100',
            'info' => 'nullable|string|max:500',
        ]);

        $client = Client::findOrFail($id); // العثور على العميل بناءً على ID
        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'info' => $request->info,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully');
    }

    // حذف عميل معين
    public function destroy($id)
    {
        $client = Client::findOrFail($id); // العثور على العميل بناءً على ID
        $client->delete(); // حذف العميل

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully');
    }
}
