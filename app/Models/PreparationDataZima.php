<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreparationDataZima extends Model
{
    protected $table = 'preparationdatazima';

    protected $fillable = [
        'vokzal',
        'rdzv',
        'name_work',
        'plan_value',
        'fact_value',
    ];

    public $timestamps = true; // в дампе есть created_at и updated_at
}
