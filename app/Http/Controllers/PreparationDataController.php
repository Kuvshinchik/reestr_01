<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WorkItem;
use App\Models\WinterPreparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PreparationDataController extends Controller
{
    public function create(Request $request)
{
    $selectedRegionId  = (int) $request->query('region_id', 0);
    $selectedStationId = (int) $request->query('station_id', 0);

    $regions = Region::orderBy('name')->get();

    // 1) Берём все work_items и группируем по parent_id
    $items    = WorkItem::orderBy('id')->get();
    $byParent = $items->groupBy('parent_id');

    // 2) "лист" = у элемента НЕТ детей
    $isLeaf = function ($item) use ($byParent) {
        return !$byParent->has($item->id);
    };

    // 3) Рекурсивно получить все листовые потомки элемента
    $leafDescendants = function ($id) use (&$leafDescendants, $byParent, $isLeaf) {
        $children = $byParent->get($id, collect());
        if ($children->isEmpty()) return collect();

        $result = collect();
        foreach ($children as $ch) {
            if ($isLeaf($ch)) {
                $result->push($ch);
            } else {
                $result = $result->merge($leafDescendants($ch->id));
            }
        }
        return $result;
    };

    // 4) Строим структуру: level1 -> subsections -> rows(только листы)
    $level1 = $items->where('level', '1')->values();

    $workStructure = [];
    foreach ($level1 as $l1) {
        $subsections = [];

        $childrenL1 = $byParent->get($l1->id, collect()); // level2 и/или level3
        foreach ($childrenL1 as $child) {
            if ($isLeaf($child)) {
                // подзаголовок сам является работой (лист)
                $rows = collect([$child]);
            } else {
                // подзаголовок — раздел, берём только листовые работы внутри
                $rows = $leafDescendants($child->id);
            }

            // если вдруг внутри ничего нет — не показываем пустой раздел
            if ($rows->isEmpty()) continue;

            $subsections[] = [
                'key'   => "wi-{$child->id}",
                'title' => $child->name,   // это подзаголовок (кликабельный)
                'rows'  => $rows,          // только то, что реально заполняем
            ];
        }

        // если у заголовка вообще нет подразделов с работами — можно скрыть заголовок
        if (empty($subsections)) continue;

        $workStructure[] = [
            'item'        => $l1,
            'subsections' => $subsections,
        ];
    }

    // 5) Подтягиваем уже сохранённые значения по вокзалу
    $saved = collect();
    if ($selectedStationId) {
        $saved = WinterPreparation::where('station_id', $selectedStationId)
            ->get()
            ->keyBy('work_item_id');
    }

    return view('preparation-data.create', compact(
        'regions',
        'selectedRegionId',
        'selectedStationId',
        'workStructure',
        'saved'
    ));
}

    // AJAX: вокзалы по РДЖВ
    public function stations($regionId)
    {
        return Station::where('region_id', $regionId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'region_id' => ['required', 'integer', 'exists:regions,id'],
        'station_id' => [
            'required',
            'integer',
            Rule::exists('stations', 'id')->where(fn ($q) => $q->where('region_id', $request->region_id)),
        ],
        'plan' => ['array'],
        'fact' => ['array'],
        'comment' => ['array'],
        'is_completed' => ['array'],
    ]);

    $stationId = (int) $validated['station_id'];

    // Берём только листовые работы (у которых нет детей)
    $leafIds = WorkItem::whereDoesntHave('children')->pluck('id');

    DB::transaction(function () use ($leafIds, $stationId, $request) {
        foreach ($leafIds as $workItemId) {
            $plan = (int) $request->input("plan.$workItemId", 0);
            $fact = (int) $request->input("fact.$workItemId", 0);
            $isCompleted = $request->has("is_completed.$workItemId") ? 1 : 0;

            $comment = trim((string) $request->input("comment.$workItemId", ''));
            $comment = $comment === '' ? null : $comment;

            WinterPreparation::updateOrCreate(
                ['station_id' => $stationId, 'work_item_id' => (int)$workItemId],
                ['plan' => $plan, 'fact' => $fact, 'is_completed' => $isCompleted, 'comment' => $comment]
            );
        }
    });

    return redirect()->route('preparation-data.create', [
        'region_id' => (int) $validated['region_id'],
        'station_id' => $stationId,
    ])->with('status', 'Данные сохранены.');
}



}
