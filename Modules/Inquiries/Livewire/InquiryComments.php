<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Modules\Inquiries\Models\{Inquiry, InquiryComment};

class InquiryComments extends Component
{
    public $inquiryId;
    public $newComment = '';
    public $comments = [];

    protected $rules = [
        'newComment' => 'required|string|min:3|max:1000',
    ];

    protected $messages = [
        'newComment.required' => 'الرجاء إدخال التعليق',
        'newComment.min' => 'التعليق يجب أن يكون 3 أحرف على الأقل',
        'newComment.max' => 'التعليق لا يجب أن يتجاوز 1000 حرف',
    ];

    public function mount($inquiryId)
    {
        $this->inquiryId = $inquiryId;
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = InquiryComment::where('inquiry_id', $this->inquiryId)
            ->with('user')
            ->latest()
            ->get()
            ->toArray();
    }

    public function addComment()
    {
        $this->validate();

        InquiryComment::create([
            'inquiry_id' => $this->inquiryId,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->loadComments();

        session()->flash('comment_success', 'تم إضافة التعليق بنجاح');
    }

    public function deleteComment($commentId)
    {
        $comment = InquiryComment::find($commentId);

        if ($comment && ($comment->user_id === Auth::id())) {
            $comment->delete();
            $this->loadComments();
            session()->flash('comment_success', 'تم حذف التعليق بنجاح');
        }
    }
    public function render()
    {
        return view('inquiries::livewire.inquiry-comments');
    }
}
