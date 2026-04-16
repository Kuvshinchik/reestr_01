<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WorkItem;
use App\Models\WinterPreparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WinterChartController extends Controller
{
    /**
     * Главная страница с диаграммой
     */
    public function index()
    {
        // Получаем регионы для Select (исключаем КЛНГ и ДЖВ)
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();

        // Получаем категории работ (level = '1' - это заголовки)
        // По условию нужны: Здания вокзалов (id=1), Котельные (id=16), Освещение (id=18)
        $categories = WorkItem::where('level', '1')
            ->whereIn('id', [1, 16, 18])
            ->orderBy('id')
            ->get();

        return view('winter-chart.index', compact('regions', 'categories'));
    }

    /**
     * AJAX-метод для получения данных диаграммы
     * 
     * Параметры:
     * - region_id: 'all' или ID региона
     * - category_id: 'all' или ID категории работ (work_item с level='1')
     */
    public function chartData(Request $request)
    {
        $regionId = $request->input('region_id', 'all');
        $categoryId = $request->input('category_id', 'all');

        // Получаем ID всех work_items которые нужно учитывать
        $workItemIds = $this->getWorkItemIds($categoryId);

        if ($regionId === 'all') {
            $data = $this->getDataByRegions($workItemIds);
            $label = 'Данные по РДЖВ (вся ДЖВ)';
        } else {
            $data = $this->getDataByStations((int)$regionId, $workItemIds);
            $region = Region::find($regionId);
            $label = 'Данные по вокзалам: ' . ($region ? $region->name : '');
        }

        return response()->json([
            'data' => $data,
            'label' => $label,
        ]);
    }

    /**
     * Получить ID всех work_items для фильтрации
     * 
     * Логика:
     * - Если 'all' - возвращаем все листовые work_items из категорий 1, 16, 18
     * - Если конкретная категория - возвращаем только листовые work_items этой категории
     * 
     * "Листовые" = те, у которых нет детей (это конечные работы)
     */
    private function getWorkItemIds($categoryId): array
    {
        // Определяем какие заголовки (level='1') нам нужны
        if ($categoryId === 'all') {
            // Все три категории
            $parentIds = [1, 16, 18];
        } else {
            // Только выбранная категория
            $parentIds = [(int)$categoryId];
        }

        // Получаем ВСЕ work_items
        $allItems = WorkItem::all();
        
        // Группируем по parent_id для быстрого поиска детей
        $byParent = $allItems->groupBy('parent_id');

        // Функция проверки - является ли элемент листом (нет детей)
        $isLeaf = function ($item) use ($byParent) {
            return !$byParent->has($item->id);
        };

        // Рекурсивная функция для получения всех листовых потомков
        $getLeafDescendants = function ($id) use (&$getLeafDescendants, $byParent, $isLeaf) {
            $children = $byParent->get($id, collect());
            if ($children->isEmpty()) {
                return collect();
            }

            $result = collect();
            foreach ($children as $child) {
                if ($isLeaf($child)) {
                    $result->push($child->id);
                } else {
                    $result = $result->merge($getLeafDescendants($child->id));
                }
            }
            return $result;
        };

        // Собираем все листовые ID из нужных категорий
        $leafIds = collect();
        foreach ($parentIds as $parentId) {
            $leafIds = $leafIds->merge($getLeafDescendants($parentId));
        }

        return $leafIds->unique()->values()->toArray();
    }

    /**
     * Получить данные по всем РДЖВ с учетом фильтра по категории
     */
    private function getDataByRegions(array $workItemIds): array
    {
        $query = DB::table('regions')
            ->select([
                'regions.id',
                'regions.name as label',
                DB::raw('COALESCE(SUM(wp.plan), 0) as plan'),
                DB::raw('COALESCE(SUM(wp.fact), 0) as fact'),
            ])
            ->leftJoin('stations', 'stations.region_id', '=', 'regions.id')
            ->leftJoin('winter_preparations as wp', function ($join) use ($workItemIds) {
                $join->on('wp.station_id', '=', 'stations.id');
                // Фильтруем только по нужным work_items
                if (!empty($workItemIds)) {
                    $join->whereIn('wp.work_item_id', $workItemIds);
                }
            })
            ->whereNotIn('regions.id', [16, 17])
            ->groupBy('regions.id', 'regions.name')
            ->orderBy('regions.name');

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'label' => $item->label,
                'plan' => (int)$item->plan,
                'fact' => (int)$item->fact,
            ];
        })->toArray();
    }

    /**
     * Получить данные по вокзалам конкретной РДЖВ с учетом фильтра по категории
     */
    private function getDataByStations(int $regionId, array $workItemIds): array
    {
        $query = DB::table('stations')
            ->select([
                'stations.id',
                'stations.name as label',
                DB::raw('COALESCE(SUM(wp.plan), 0) as plan'),
                DB::raw('COALESCE(SUM(wp.fact), 0) as fact'),
            ])
            ->leftJoin('winter_preparations as wp', function ($join) use ($workItemIds) {
                $join->on('wp.station_id', '=', 'stations.id');
                if (!empty($workItemIds)) {
                    $join->whereIn('wp.work_item_id', $workItemIds);
                }
            })
            ->where('stations.region_id', $regionId)
            ->groupBy('stations.id', 'stations.name')
            ->orderBy('stations.name');

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'label' => $item->label,
                'plan' => (int)$item->plan,
                'fact' => (int)$item->fact,
            ];
        })->toArray();
    }
}
