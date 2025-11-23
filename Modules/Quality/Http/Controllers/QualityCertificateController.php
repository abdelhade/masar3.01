<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\QualityCertificate;

class QualityCertificateController extends Controller
{
    public function index()
    {
        $certificates = QualityCertificate::orderBy('expiry_date', 'asc')->paginate(20);

        $stats = [
            'total' => QualityCertificate::count(),
            'active' => QualityCertificate::where('status', 'active')->count(),
            'expiring_soon' => QualityCertificate::expiringSoon()->count(),
            'expired' => QualityCertificate::expired()->count(),
        ];

        return view('quality::certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        return view('quality::certificates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_number' => 'required|string|unique:quality_certificates,certificate_number',
            'certificate_name' => 'required|string',
            'issuing_authority' => 'required|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'scope' => 'nullable|string',
            'notification_days' => 'required|integer|min:1',
            'certificate_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['status'] = 'active';
        $validated['notify_before_expiry'] = true;
        $validated['created_by'] = auth()->id();

        $certificate = QualityCertificate::create($validated);

        return redirect()->route('quality.certificates.show', $certificate)
            ->with('success', 'تم إضافة الشهادة بنجاح');
    }

    public function show(QualityCertificate $certificate)
    {
        return view('quality::certificates.show', compact('certificate'));
    }

    public function edit(QualityCertificate $certificate)
    {
        return view('quality::certificates.edit', compact('certificate'));
    }

    public function update(Request $request, QualityCertificate $certificate)
    {
        $validated = $request->validate([
            'certificate_name' => 'required|string',
            'issuing_authority' => 'required|string',
            'expiry_date' => 'required|date',
            'status' => 'required',
            'notification_days' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $certificate->update($validated);

        return redirect()->route('quality.certificates.show', $certificate)
            ->with('success', 'تم تحديث الشهادة بنجاح');
    }

    public function destroy(QualityCertificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('quality.certificates.index')
            ->with('success', 'تم حذف الشهادة بنجاح');
    }
}

