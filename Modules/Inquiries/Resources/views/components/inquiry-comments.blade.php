<!-- Comments Section -->
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

                <!-- إضافة تعليق جديد (اختياري: يظهر فقط لو فيه addTempComment) -->
                @if (method_exists($this, 'addTempComment'))
                    <div class="mb-3">
                        <label for="newTempComment" class="form-label fw-bold">
                            <i class="fas fa-pen me-2"></i>
                            {{ __('Add Note') }}
                        </label>
                        <div class="input-group">
                            <textarea wire:model.live="newTempComment" id="newTempComment" class="form-control" rows="2"
                                placeholder="{{ __('Write your notes here...') }}"></textarea>
                            <button type="button" wire:click="addTempComment" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('Add') }}
                            </button>
                        </div>
                        @error('newTempComment')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <!-- عرض التعليقات بأمان -->
                @php
                    $allComments = collect()
                        ->merge($existingComments ?? [])
                        ->merge($tempComments ?? [])
                        ->filter();
                @endphp

                <div class="comments-list mt-3">
                    @forelse ($allComments as $comment)
                        @php
                            $isExisting = isset($comment['id']);
                            $indexInTemp = $isExisting ? null : array_search($comment, $tempComments ?? [], true);
                        @endphp

                        <div
                            class="alert {{ $isExisting ? 'alert-light border' : 'alert-info' }} d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="mb-1">
                                    <strong><i class="fas fa-user me-1"></i>
                                        {{ $comment['user_name'] ?? 'Unknown' }}</strong>
                                    <small class="text-muted ms-2">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                                <p class="mb-0">{{ $comment['comment'] }}</p>
                            </div>

                            <!-- حذف فقط للتعليقات المؤقتة -->
                            @if (!$isExisting && $indexInTemp !== false && method_exists($this, 'removeTempComment'))
                                <button type="button" wire:click="removeTempComment({{ $indexInTemp }})"
                                    class="btn btn-sm btn-outline-danger ms-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('No notes yet. Add your first note above.') }}
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>
