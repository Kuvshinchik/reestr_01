<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreparationData extends Model
{
    use HasFactory;

    protected $table = 'preparation_data';

    protected $fillable = [
        'station_id',
        'object_category_id',
        'report_date',
        'plan_value',
        'fact_value',
    ];

    protected $dates = [
        'report_date',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function objectCategory()
    {
        return $this->belongsTo(ObjectCategory::class, 'object_category_id');
    }
}
