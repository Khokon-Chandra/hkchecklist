<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'name', 'is_default'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Property::class);
    }
    public function tasks(): HasMany
    {
        return $this->hasMany(\App\Models\Task::class);
    }
}
