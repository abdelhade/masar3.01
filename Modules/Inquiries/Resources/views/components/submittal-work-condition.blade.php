<!-- Required Submittal Checklist Section -->
<div class="col-6">
    <div class="card border-success">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-check-square me-2"></i>
                قائمة التقديمات المطلوبة
            </h6>
            <small class="d-block mt-1">اختر التقديمات المطلوبة (مع حساب
                السكور)</small>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($submittalChecklist as $index => $item)
                    @if (isset($item['checked']))
                        <div class="col-md-3 mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model.live="submittalChecklist.{{ $index }}.checked"
                                    id="submittal_{{ $index }}" class="form-check-input">
                                <label for="submittal_{{ $index }}" class="form-check-label">
                                    {{ $item['name'] }} ({{ $item['value'] }})
                                </label>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Working Conditions Checklist Section -->
<div class="col-12">
    <div class="card border-danger">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                قائمة شروط العمل
            </h6>
            <small class="d-block mt-1">اختر الشروط (مع حساب السكور)</small>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($workingConditions as $index => $condition)
                    <div class="col-md-3 mb-3">
                        <div class="form-check">
                            <input type="checkbox" wire:model.live="workingConditions.{{ $index }}.checked"
                                id="condition_{{ $index }}" class="form-check-input">
                            <label for="condition_{{ $index }}" class="form-check-label">
                                {{ $condition['name'] }}
                            </label>
                        </div>
                        @if (isset($condition['options']) && $workingConditions[$index]['checked'])
                            <select wire:model.live="workingConditions.{{ $index }}.selectedOption"
                                class="form-select mt-2">
                                <option value="">اختر...</option>
                                @foreach ($condition['options'] as $option => $score)
                                    <option value="{{ $score }}">
                                        {{ $option }} ({{ $score }})
                                    </option>
                                @endforeach
                            </select>
                            @error('workingConditions.' . $index . '.selectedOption')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- عرض النتائج -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="row">
                            <!-- السكور الإجمالي -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-calculator fa-2x text-primary mb-2"></i>
                                    <h5>السكور الإجمالي</h5>
                                    <span class="badge bg-primary fs-4">{{ $totalScore }}</span>
                                </div>
                            </div>

                            <!-- النسبة المئوية -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-percent fa-2x text-success mb-2"></i>
                                    <h5>النسبة المئوية</h5>
                                    <span class="badge bg-success fs-4">{{ $difficultyPercentage }}%</span>
                                </div>
                            </div>

                            <!-- درجة الصعوبة -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                    <h5>درجة الصعوبة</h5>
                                    <span class="badge bg-warning fs-4">{{ $projectDifficulty }}</span>
                                </div>
                            </div>

                            <!-- تصنيف الصعوبة -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                    <h5>تصنيف الصعوبة</h5>
                                    <span
                                        class="badge
                                            @if ($projectDifficulty == 1) bg-success
                                            @elseif ($projectDifficulty == 2) bg-warning
                                            @elseif ($projectDifficulty == 3) bg-orange
                                            @else bg-danger @endif fs-5">
                                        @if ($projectDifficulty == 1)
                                            سهل (أقل من 25%)
                                        @elseif ($projectDifficulty == 2)
                                            متوسط (25% - 50%)
                                        @elseif ($projectDifficulty == 3)
                                            صعب (50% - 75%)
                                        @elseif ($projectDifficulty == 4)
                                            صعب جداً (أكثر من 75%)
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar للنسبة المئوية -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar
                            @if ($projectDifficulty == 1) bg-success
                            @elseif ($projectDifficulty == 2) bg-warning
                            @elseif ($projectDifficulty == 3) bg-orange
                            @else bg-danger @endif"
                                        role="progressbar" style="width: {{ $difficultyPercentage }}%"
                                        aria-valuenow="{{ $difficultyPercentage }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                        {{ $difficultyPercentage }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
