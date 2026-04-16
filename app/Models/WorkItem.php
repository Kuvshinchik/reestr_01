<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkItem extends Model
{
    protected $fillable = ['parent_id', 'name', 'level'];

    public function parent()
    {
        return $this->belongsTo(WorkItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WorkItem::class, 'parent_id');
    }
}
