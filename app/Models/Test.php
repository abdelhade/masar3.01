<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;
    protected $table = 'test'; // ← هنا نحدد اسم الجدول الصحيح
    protected $fillable = ['int1', 'int2', 'var1', 'var2'];
}
