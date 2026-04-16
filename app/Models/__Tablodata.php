<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tablodata extends Model
{
    use HasFactory;
    
    /**
     * Имя таблицы в БД
     *
     * @var string
     */
    protected $table = 'tablodata';
    
    /**
     * Поля, которые можно массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'height',
        'width',
        'type',
        'yearbirthday',
        'yearbeginworking',
        'foto',
        'qrcode'
    ];
    
    /**
     * (Опционально) Типы полей для автоматического приведения
     *
     * @var array
     */
    protected $casts = [
        'height' => 'integer',
        'width' => 'integer',
        'yearbirthday' => 'integer',
        'yearbeginworking' => 'integer',
    ];
}