<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kpi extends Model
{
    protected $table = 'kpis';
    protected $fillable = ['name', 'description'];

    public function evaluations(): BelongsToMany
    {
        return $this->belongsToMany(Employee_Evaluation::class, 'employeeEvaluation_kpis', 'kpi_id', 'employee_evaluation_id')->withPivot('score', 'notes');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_kpis', 'kpi_id', 'employee_id')->withPivot('weight_percentage');
    }
}
