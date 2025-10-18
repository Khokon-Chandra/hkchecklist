<?php

namespace App\Domain\Sessions\Models;

use Illuminate\Database\Eloquent\Model;

class RoomPhoto extends Model
{
    protected $fillable = [
        'session_id',
        'room_id',
        'path',
        'captured_at',
        'has_timestamp_overlay'
    ];
    protected $casts = ['captured_at' => 'datetime', 'has_timestamp_overlay' => 'bool'];
}
