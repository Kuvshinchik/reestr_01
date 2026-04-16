<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinterPreparation extends Model
{
    protected $fillable = [
        'station_id','work_item_id','plan','fact','is_completed','comment'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }
}
