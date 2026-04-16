<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        // ВАЖНО: после рефакторинга местоположения
        // columns vokzal/rdzv/dzv удалены из workers, поэтому здесь их тоже убираем.
        'tabelNumber',
        'statusSite',
        'statusVokzal',
        'vakcina',
    ];
}
