<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;


class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض المجموعات')->only(['index', 'noteDetails']);
    }
    public function index()
    {
        return view('item-management.notes.manage-notes');
    }
    public function noteDetails($noteId)
    {
        return view('item-management.notes.note-details', compact('noteId'));
    }
}
