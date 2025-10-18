<?php

namespace App\Domain\Rooms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['property_id', 'name', 'is_default'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Properties\Models\Property::class);
    }
    public function tasks(): HasMany
    {
        return $this->hasMany(\App\Domain\Tasks\Models\Task::class);
    }
}
