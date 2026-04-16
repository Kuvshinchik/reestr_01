<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\ObjectCategory;
use App\Models\PreparationData;
use Illuminate\Http\Request;
use App\Models\ObjectCategoryLeto;
use App\Models\PreparationDataLeto;

class PreparationDataController extends Controller
{
    /**
     * Форма ввода данных по подготовке вокзалов
     
    public function create()
    {
        $regions = Region::orderBy('name')->get();

        // Берём только "дочерние" категории как отдельные виды работ,
        // чтобы не выводить крупные заголовки вроде "1. Здания вокзалов"
        $categories = ObjectCategory::whereNotNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('preparation.create', compact('regions', 'categories'));
    }
*/

public function create()
{
    $regions = Region::orderBy('name')->get();

    $winterCategories = ObjectCategory::whereNotNull('parent_id')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $summerCategories = ObjectCategoryLeto::whereNotNull('parent_id')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return view('preparation.create', compact('regions', 'winterCategories', 'summerCategories'));
}

    /**
     * AJAX: список вокзалов по выбранному РДЖВ
     */
    public function stations(Region $region)
    {
        // Вернём только id и name
        $stations = $region->stations()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($stations);
    }

    /**
     * Сохранение данных формы в таблицу preparation_data
     */
    public function store(Request $request)
    {
       $request->validate([
        'region_id'  => ['required', 'exists:regions,id'],
        'station_id' => ['required', 'exists:stations,id'],
        'season'     => ['required', 'in:winter,summer'],

        'plan'       => ['array'],
        'fact'       => ['array'],
        'plan.*'     => ['nullable', 'integer', 'min:0'],
        'fact.*'     => ['nullable', 'integer', 'min:0'],
    ]);

    $regionId  = (int) $request->input('region_id');
    $stationId = (int) $request->input('station_id');
    $season    = $request->input('season');

    // тех.дата, пока поле report_date есть в таблицах
    $reportDate = $season === 'winter' ? '2025-12-01' : '2025-06-01';

    // проверяем соответствие вокзал–РДЖВ
    $station = Station::where('id', $stationId)
        ->where('region_id', $regionId)
        ->first();

    if (! $station) {
        return back()
            ->withErrors(['station_id' => 'Выбранный вокзал не относится к указанному РДЖВ'])
            ->withInput();
    }

    $plans = $request->input('plan', []);
    $facts = $request->input('fact', []);

    if ($season === 'winter') {
        // ПИШЕМ В ЗИМНЮЮ ТАБЛИЦУ preparation_data
        foreach ($plans as $categoryId => $planValue) {
            $planValue = $planValue !== null && $planValue !== '' ? (int) $planValue : null;
            $factValue = isset($facts[$categoryId]) && $facts[$categoryId] !== ''
                ? (int) $facts[$categoryId]
                : null;

            if ($planValue === null && $factValue === null) {
                continue;
            }

            PreparationData::updateOrCreate(
                [
                    'station_id'         => $stationId,
                    'object_category_id' => (int) $categoryId,
                    'report_date'        => $reportDate,
                ],
                [
                    'plan_value' => $planValue ?? 0,
                    'fact_value' => $factValue ?? 0,
                ]
            );
        }
    } else {
        // ПИШЕМ В ЛЕТНЮЮ ТАБЛИЦУ preparation_data_leto
        foreach ($plans as $categoryId => $planValue) {
            $planValue = $planValue !== null && $planValue !== '' ? (int) $planValue : null;
            $factValue = isset($facts[$categoryId]) && $facts[$categoryId] !== ''
                ? (int) $facts[$categoryId]
                : null;

            if ($planValue === null && $factValue === null) {
                continue;
            }

            PreparationDataLeto::updateOrCreate(
                [
                    'station_id'              => $stationId,
                    'object_category_leto_id' => (int) $categoryId,
                    'report_date'             => $reportDate,
                ],
                [
                    'plan_value' => $planValue ?? 0,
                    'fact_value' => $factValue ?? 0,
                ]
            );
        }
    }

    return redirect()
        ->route('preparation-data.create')
        ->with('status', 'Данные успешно сохранены');
}
}