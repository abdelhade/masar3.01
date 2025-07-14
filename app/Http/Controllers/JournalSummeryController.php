<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Illuminate\Routing\Controller;


class JournalSummeryController extends Controller

{
   public function __construct()
{
    $this->middleware('can:عرض قيود يومية عمليات')->only(['index']);
    // $this->middleware('can:إضافة قيود يومية عمليات')->only(['create', 'store']);
    // $this->middleware('can:تعديل قيود يومية عمليات')->only(['edit', 'update']);
    // $this->middleware('can:حذف قيود يومية عمليات')->only(['destroy']);
}

    public function index()
    {
        $journalHeads = JournalHead::with('dets')->get();



        return view('journals.journal-summery', compact('journalHeads'));
    }
}
