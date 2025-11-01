<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'name', 'is_default'];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_room')
            ->withTimestamps()
            ->withPivot(['sort_order']);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'room_task')
            ->withTimestamps()
            ->withPivot(['sort_order', 'instructions', 'visible_to_owner', 'visible_to_housekeeper'])
            ->orderBy('room_task.sort_order');
    }
}
