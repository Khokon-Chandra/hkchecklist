<?php

namespace App\Domain\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'beds',
        'baths',
        'latitude',
        'longitude',
        'geo_radius_m'
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(\App\Domain\Rooms\Models\Room::class);
    }
}
