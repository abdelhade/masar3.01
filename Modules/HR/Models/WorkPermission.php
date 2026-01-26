<?php

declare(strict_types=1);

namespace Modules\HR\Models;

use Modules\HR\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WorkPermission extends Model
{
    protected $guarded = ['id'];
    protected $table = 'work_permissions';

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * تشغيل الـ Global Scope لضمان فصل بيانات الفروع
     * يتم التصفية من خلال علاقة employee لأن work_permissions لا يحتوي على branch_id مباشرة
     */
    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Auth::check()) {
                $userId = Auth::id();
                $cacheKey = "user_branches_{$userId}";

                $branchIds = Cache::remember($cacheKey, 3600, function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $user->branches()
                        ->where('is_active', 1)
                        ->pluck('branches.id')
                        ->toArray();
                });

                if (! empty($branchIds)) {
                    $builder->whereHas('employee', function ($query) use ($branchIds) {
                        $query->whereIn('employees.branch_id', $branchIds);
                    });
                } else {
                    // If no branches, return empty result set
                    $builder->whereRaw('1 = 0');
                }
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approved_by()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
