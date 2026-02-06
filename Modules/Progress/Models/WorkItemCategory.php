<?php
namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkItemCategory extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];

    public function workItems()
    {
        return $this->hasMany(WorkItem::class, 'category_id');
    }
}
