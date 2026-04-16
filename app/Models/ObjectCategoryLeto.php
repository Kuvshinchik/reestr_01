<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectCategoryLeto extends Model
{
    use HasFactory;

    protected $table = 'object_categories_leto';

    protected $fillable = [
        'name',
        'parent_id',
        'unit',
        'sort_order',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
