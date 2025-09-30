<!-- Temporary Comments Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-comments me-2"></i>
                    التعليقات والملاحظات
                </h6>
                <small class="d-block mt-1">سيتم حفظ التعليقات مع الاستفسار</small>
            </div>
            <div class="card-body">
                <!-- Form لإضافة تعليق -->
                <div class="mb-3">
                    <label for="newTempComment" class="form-label fw-bold">
                        <i class="fas fa-pen me-2"></i>
                        أضف ملاحظة
                    </label>
                    <div class="input-group">
                        <textarea wire:model="newTempComment" id="newTempComment" class="form-control" rows="2"
                            placeholder="اكتب ملاحظاتك هنا..."></textarea>
                        <button type="button" wire:click="addTempComment" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            إضافة
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
                        لا توجد ملاحظات. يمكنك إضافة ملاحظاتك قبل حفظ الاستفسار.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
