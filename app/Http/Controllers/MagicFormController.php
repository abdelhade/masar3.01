<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MagicFormController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        return view('magic-forms.index', compact('type'));
    }

    public function showForm(Request $request)
    {
        $type = $request->query('type');
        // يمكنك هنا جلب بيانات أو عرض فورم حسب النوع
        return view('magicals.form', compact('type'));
    }
} 