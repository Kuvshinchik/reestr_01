<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = ['region_id', 'name', 'code'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function winterPreparations()
    {
        return $this->hasMany(WinterPreparation::class);
    }
	
	// На вокзале много первозимников
    public function winterWorkers() {
        return $this->hasMany(WinterWorker::class);
    }
}
