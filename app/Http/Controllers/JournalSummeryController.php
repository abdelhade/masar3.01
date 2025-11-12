<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Illuminate\Routing\Controller;


class JournalSummeryController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:عرض قيود يومية عمليات')->only(['index']);
    // }

    public function index()
    {
        $journalHeads = JournalHead::with(['dets' => function ($query) {
            $query->orderBy('debit', 'desc');
        }])->get();

        return view('journals.journal-summery', compact('journalHeads'));
    }
}
