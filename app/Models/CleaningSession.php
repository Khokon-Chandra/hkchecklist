<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'owner_id',
        'housekeeper_id',
        'scheduled_date',
        'status',
        'started_at',
        'ended_at',
        'gps_confirmed_at',
        'start_latitude',
        'start_longitude'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'gps_confirmed_at' => 'datetime'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Property::class);
    }
    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'session_id');
    }
    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class, 'session_id');
    }
}
