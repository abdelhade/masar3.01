<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Modules\Inquiries\Models\InquiryComment;

class InquiryComments extends Component
{
    public $inquiryId;
    public $newComment = '';
    public $comments = [];
    public $messages = [];

    protected $rules = [
        'newComment' => 'required|string|min:3|max:1000',
    ];

    public function mount($inquiryId)
    {
        $this->inquiryId = $inquiryId;
        $this->messages = [
            'newComment.required' => __('Comment Required'),
            'newComment.min' => __('Comment Min Length 3 Characters'),
            'newComment.max' => __('Comment Max Length'),
        ];

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
        $this->validate($this->rules, $this->messages);

        InquiryComment::create([
            'inquiry_id' => $this->inquiryId,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->loadComments();

        session()->flash('comment_success', __('Comment Added Success'));
    }

    public function deleteComment($commentId)
    {
        $comment = InquiryComment::find($commentId);

        if ($comment && ($comment->user_id === Auth::id())) {
            $comment->delete();
            $this->loadComments();
            session()->flash('comment_success', __('Comment Deleted Success'));
        }
    }

    public function render()
    {
        return view('inquiries::livewire.inquiry-comments');
    }
}
