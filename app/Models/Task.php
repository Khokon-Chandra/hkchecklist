<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = ['property_id', 'room_id', 'name', 'is_default', 'type', 'instructions']; // type: 'room'|'inventory'


    protected $casts = [
        'is_default' => 'boolean',
        'type' => 'string', // 'room' or 'inventory'
    ];



    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_task')
            ->withTimestamps()
            ->withPivot(['sort_order', 'instructions', 'visible_to_owner', 'visible_to_housekeeper']);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TaskMedia::class);
    }
}
