<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMedia extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'type', 'url', 'thumbnail', 'caption', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
