<?php

namespace App\Models\ProductionDevelopment\Clock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Station;

class Clock extends Model
{
    protected $table = 'clocks';

    protected $fillable = [
        'station_id',
        'station_name',
        'region_name',
        'type',
        'description',
        'supply_year',
    ];

    protected $casts = [
        'supply_year' => 'integer',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
