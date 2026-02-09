<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
{
    $this->middleware('can:view progress-clients')->only('index');
    $this->middleware('can:create progress-clients')->only(['create', 'store']);
    $this->middleware('can:edit progress-clients')->only(['edit', 'update']);
    $this->middleware('can:delete progress-clients')->only('destroy');
}
    public function index()
    {
        $clients = Client::latest()->get();
        return view('progress::clients.index', compact('clients'));
    }

    public function create()
    {
        return view('progress::clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string'
        ]);

        Client::create([
            'cname' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->route('progress.clients.index')
            ->with('success', 'تم إضافة العميل بنجاح');
    }

    public function show(Client $client)
    {
        return view('progress::clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('progress::clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        $client->update([
            'cname' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->route('progress.clients.index')
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    public function destroy(Client $client)
    {
        try {
            // التحقق من وجود مشاريع مرتبطة
            $projectsCount = $client->projects()->count();

            if ($projectsCount > 0) {
                $message = "لا يمكن حذف العميل لأنه مرتبط بـ {$projectsCount} مشروع";
                return back()->with('error', $message);
            }

            $client->delete();

            return redirect()
                ->route('progress.clients.index')
                ->with('success', 'تم حذف العميل بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الحذف: '.$e->getMessage());
        }
    }
}
