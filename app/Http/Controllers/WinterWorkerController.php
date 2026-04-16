<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WinterWorker;
use App\Models\Station;

class WinterWorkerController extends Controller
{
    // Метод для показа формы (GET)
    public function create()
    {
        // Показываем view, который создадим на следующем шаге
        return view('worker.winter_form');
    }

    // Метод для сохранения данных (POST)
    public function store(Request $request)
    {
        // 1. Проверяем, что данные корректны (Валидация)
        $request->validate([
    //        'personnel_number' => 'required|string|max:50', // Табельный номер вместо ФИО
            'hired_at' => 'required|date',
            'trained_at' => 'nullable|date', // Может быть пустым, если еще не обучен
        ]);

        // 2. Определяем вокзал текущего пользователя
        // Мы берем название вокзала из поля workLocation пользователя
        $userLocationName = Auth::user()->workLocation;
		
		
		$personnel_number = Auth::user()->id;
		$personnel_number = (int)$personnel_number; 
        //dd($personnel_number);
        
		
		// Ищем ID станции по названию
        $station = Station::where('name', $userLocationName)->first();

        if (!$station) {
            return back()->withErrors(['msg' => 'Ваша станция не найдена в справочнике вокзалов. Обратитесь к администратору.']);
        }

        // 3. Создаем запись в базе
        WinterWorker::create([
            'station_id' => $station->id,
//          'personnel_number' => $personnel_number->personnel_number, // В модели WinterWorker замени full_name на personnel_number
			'personnel_number' => $personnel_number, // В модели WinterWorker замени full_name на personnel_number
            'hired_at' => $request->hired_at,
            'trained_at' => $request->trained_at,
            'season' => '2025-2026', // Можно сделать динамическим, но пока жестко зададим текущий сезон
        ]);

        // 4. Возвращаем пользователя назад с сообщением об успехе
        return redirect()->route('worker.dashboard')->with('success', 'Сотрудник успешно добавлен!');
    }
}