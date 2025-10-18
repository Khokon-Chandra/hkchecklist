<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['property_id', 'room_id', 'name', 'is_default', 'type']; // type: 'room'|'inventory'

    public function room(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Room::class);
    }
}
