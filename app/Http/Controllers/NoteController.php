<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        $note = Note::findOrFail($noteId);

        // Check permissions based on note type
        // Note ID 1 = Groups, Note ID 2 = Categories
        $permission = match ($noteId) {
            1 => 'view groups',
            2 => 'view Categories',
            default => 'view items', // fallback for other notes
        };

        if (! $user->can($permission)) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        return view('item-management.notes.note-details', compact('noteId'));
    }
}
