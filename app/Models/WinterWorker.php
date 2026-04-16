<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinterWorker extends Model
{
    // Разрешаем массовое заполнение этих полей
    protected $fillable = ['station_id', 'personnel_number', 'season', 'hired_at', 'trained_at'];

    // Указываем, что эти поля — даты (чтобы Laravel удобно с ними работал)
    protected $casts = [
        'hired_at' => 'date',
        'trained_at' => 'date',
    ];

    // Работник прикреплен к вокзалу
    public function station() {
        return $this->belongsTo(Station::class);
    }
}