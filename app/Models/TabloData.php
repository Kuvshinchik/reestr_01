<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabloData extends Model
{
    use HasFactory;

    protected $table = 'tablodata';

    protected $fillable = [
        'ip',
        'height',
        'width',
        'yearbirthday',
        'yearbeginworking',
        'foto',
        'qrcode',
    ];
}
