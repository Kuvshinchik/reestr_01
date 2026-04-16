<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name', 'full_name'];

    public function stations()
    {
        return $this->hasMany(Station::class);
    }
	
	// РДЖВ может получить всех работников через вокзалы (связь "HasManyThrough")
    public function winterWorkers() {
        return $this->hasManyThrough(WinterWorker::class, Station::class);
    }
}
