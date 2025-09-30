<?php

namespace Modules\Inquiries\Http\Controllers;

use App\Http\Controllers\Controller;

class DifficultyMatrixController extends Controller
{
    public function create()
    {
        return view('inquiries::difficulty-matrix.create');
    }
}
