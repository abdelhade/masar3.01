<!-- Temporary Comments Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-comments me-2"></i>
                    {{ __('Comments and Notes') }}
                </h6>
                <small class="d-block mt-1">{{ __('Comments will be saved with the inquiry') }}</small>
            </div>
            <div class="card-body">
                <!-- Form لإضافة تعليق -->
                <div class="mb-3">
                    <label for="newTempComment" class="form-label fw-bold">
                        <i class="fas fa-pen me-2"></i>
                        {{ __('Add Note') }}
                    </label>
                    <div class="input-group">
                        <textarea wire:model="newTempComment" id="newTempComment" class="form-control" rows="2"
                            placeholder="{{ __('Write your notes here...') }}"></textarea>
                        <button type="button" wire:click="addTempComment" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            {{ __('Add') }}
                        </button>
                    </div>
                    @error('newTempComment')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- عرض التعليقات المؤقتة -->
                @if (!empty($tempComments))
                    <div class="comments-list">
                        @foreach ($tempComments as $index => $comment)
                            <div class="alert alert-info d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <div class="mb-1">
                                        <strong>
                                            <i class="fas fa-user me-1"></i>
                                            {{ $comment['user_name'] }}
                                        </strong>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                    <p class="mb-0">{{ $comment['comment'] }}</p>
                                </div>
                                <button type="button" wire:click="removeTempComment({{ $index }})"
                                    class="btn btn-sm btn-outline-danger ms-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('No notes available. You can add your notes before saving the inquiry.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
