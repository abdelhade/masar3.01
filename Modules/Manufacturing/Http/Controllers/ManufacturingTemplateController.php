<?php

declare(strict_types=1);

namespace Modules\Manufacturing\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Invoices\Models\InvoiceTemplate;

/**
 * Controller for managing manufacturing invoice templates
 */
class ManufacturingTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Manufacturing Invoices')->only(['index']);
    }

    /**
     * Display manufacturing templates
     */
    public function index(): View
    {
        // Get manufacturing templates from operhead (pro_type = 63)
        $templates = \App\Models\OperHead::where('pro_type', 63)
            ->with(['acc1Head', 'acc2Head', 'employee', 'branch'])
            ->orderBy('pro_date', 'desc')
            ->paginate(15);

        return view('manufacturing::templates.index', compact('templates'));
    }

    /**
     * Toggle template active status
     */
    public function toggleActive(int $templateId): RedirectResponse
    {
        $template = \App\Models\OperHead::where('pro_type', 63)->findOrFail($templateId);
        
        // Toggle is_manager field (0 = inactive, 1 = active)
        $template->update([
            'is_manager' => !$template->is_manager,
        ]);

        return back()->with('success', 'تم تحديث حالة النموذج بنجاح');
    }

    /**
     * Delete template
     */
    public function destroy(int $templateId): RedirectResponse
    {
        $template = \App\Models\OperHead::where('pro_type', 63)->findOrFail($templateId);
        
        // Delete related operation items
        \App\Models\OperationItems::where('pro_id', $template->id)->delete();
        
        // Delete related expenses
        \App\Models\Expense::where('op_id', $template->id)->delete();
        
        // Delete the template
        $template->delete();

        return redirect()->route('manufacturing.templates.index')
            ->with('success', 'تم حذف النموذج بنجاح');
    }
}
