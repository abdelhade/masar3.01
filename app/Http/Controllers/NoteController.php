<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;


class NoteController extends Controller
{
    public function __construct()
    {
        // $this->middleware('can:عرض المجموعات')->only(['index', 'noteDetails']);
    }
    public function index()
    {
        return view('item-management.notes.manage-notes');
    }
    public function noteDetails($noteId)
{
    $user = Auth::user(); // تعريف المستخدم الحالي

    $note = Note::findOrFail($noteId); // جلب النوت أو إظهار 404 لو مش موجودة

    if (! $user->can('عرض ' . $note->name)) {
        abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
    }

    return view('item-management.notes.note-details', compact('noteId'));
}

}
