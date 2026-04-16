<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreparationDataController extends Controller
{
    public function create()
    {
        $regions = Region::orderBy('name')->get();

        // Дерево для аккордеона: Заголовок -> Подзаголовок -> Работы
        $workTree = $this->buildWorkTreeFromUnicum();
//dd($workTree);
        return view('preparation-data.create', compact('regions', 'workTree'));
    }

    /**
     * AJAX: вернуть вокзалы по region_id
     */
    public function stations(Region $region)
    {
        // У “ОУ ДЖВ” просто не будет записей в stations → вернём пустой массив
        return $region->stations()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'region_id'  => ['required', 'integer', 'exists:regions,id'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'plan'       => ['array'],
            'fact'       => ['array'],
        ]);

        $region  = Region::findOrFail($request->integer('region_id'));
        $station = Station::findOrFail($request->integer('station_id'));

        // защита от “не того вокзала”
        if ((int)$station->region_id !== (int)$region->id) {
            return back()->withErrors(['station_id' => 'Выбранный вокзал не относится к выбранной РДЖВ'])->withInput();
        }

        // Плоский список работ (key -> name/unit)
        $workMap = $this->flattenWorkTree($this->buildWorkTreeFromUnicum());

        $plan = $request->input('plan', []);
        $fact = $request->input('fact', []);

        $rdzv   = trim($region->name);
        $vokzal = trim($station->name);

        $now = now();
        $rows = [];

        foreach ($workMap as $key => $work) {
            $rows[] = [
                'rdzv'       => $rdzv,
                'vokzal'     => $vokzal,
                'name_work'  => $work['name'],
                'plan_value' => (int)($plan[$key] ?? 0),
                'fact_value' => (int)($fact[$key] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rdzv, $vokzal, $rows) {
            // Самый простой способ “без дублей”:
            // удалили старые строки по вокзалу+РДЖВ → вставили новые
            DB::table('preparationdatazima')
                ->where('rdzv', $rdzv)
                ->where('vokzal', $vokzal)
                ->delete();

            DB::table('preparationdatazima')->insert($rows);
        });

        return redirect()
            ->route('preparation-data.create')
            ->with('status', 'Данные сохранены');
    }

    /**
     * Берём работы из unicum_works.txt и делаем группы для аккордеона.
     * Файл положите, например, сюда:
     * storage/app/works/unicum_works.txt
     */
    private function buildWorkTreeFromUnicum(): array
    {
        $works = $this->loadUnicumWorks();

        $tree = [];

        foreach ($works as $workName) {
            $heading = $this->detectHeading($workName);
            $sub     = $this->detectSubheading($workName, $heading);

            $tree[$heading][$sub][] = [
                'key'  => md5($workName),          // стабильный ключ для input name
                'name' => $workName,
                'unit' => $this->extractUnit($workName), // просто для отображения (в БД не пишем)
            ];
        }

        // чтобы вывод был стабильный
        ksort($tree);
        foreach ($tree as &$subs) {
            ksort($subs);
        }

        return $tree;
    }

    private function loadUnicumWorks(): array
    {
        $path = storage_path('app/works/unicum_works.txt');

        if (!is_file($path)) {
            // Чтобы у новичка не “молчало”:
            abort(500, "Не найден файл работ: {$path}");
        }

        $lines = preg_split("/\R/u", file_get_contents($path));
        $works = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // пропускаем служебные строки из вашего файла (там есть SQL + заголовок name_work):contentReference[oaicite:9]{index=9}
            if (stripos($line, 'select distinct') !== false) continue;
            if (mb_strtolower($line) === 'name_work') continue;

            $works[] = $line;
        }

        return $works;
    }

    private function detectHeading(string $workName): string
    {
        $w = mb_strtolower($workName);

        if (str_contains($w, 'пассажирские платформы')) return 'Пассажирские платформы';
        if (str_contains($w, 'пешеходные тоннели'))     return 'Пешеходные тоннели';
        if (str_contains($w, 'пешеходные мосты'))       return 'Пешеходные мосты';

        if (str_contains($w, 'котельные') || str_contains($w, 'котлы') || str_contains($w, 'теплов')) {
            return 'Котельные и теплоснабжение';
        }

        if (str_contains($w, 'освещ')) return 'Освещение';

        return 'Здания вокзалов';
    }

    private function detectSubheading(string $workName, string $heading): string
    {
        // Подзаголовки “по смыслу”, чтобы аккордеон был удобный
        if (str_starts_with($workName, 'На балансе ДЖВ')) return 'На балансе ДЖВ';
        if (str_starts_with($workName, 'На балансе НТЭ')) return 'На балансе НТЭ';

        if (mb_stripos($workName, 'подготовка освещения') !== false) return 'Подготовка освещения';
        if (str_starts_with($workName, 'В том числе')) return 'В том числе';

        // Для “Здания вокзалов” часто удобнее, чтобы каждый пункт был отдельным подзаголовком:
        if ($heading === 'Здания вокзалов') return $workName;

        return 'Прочее';
    }

    private function extractUnit(string $workName): ?string
    {
        // Пример: "... (пешеходные тоннели, системы)" -> "системы"
        // Пример: "В том числе ... (точки)" -> "точки"
        if (preg_match('/\(([^)]*),\s*([^)]+)\)\s*$/u', $workName, $m)) {
            return trim($m[2]);
        }
        if (preg_match('/\(([^)]+)\)\s*$/u', $workName, $m)) {
            $inside = trim($m[1]);
            // если внутри одно слово, считаем его “ед.изм.”
            if (!str_contains($inside, ',')) return $inside;
        }
        return null;
    }

    private function flattenWorkTree(array $tree): array
    {
        $map = [];
        foreach ($tree as $subs) {
            foreach ($subs as $works) {
                foreach ($works as $w) {
                    $map[$w['key']] = ['name' => $w['name'], 'unit' => $w['unit']];
                }
            }
        }
        return $map;
    }
	
	public function values(Request $request)
{
    $request->validate([
        'region_id'  => ['required', 'integer', 'exists:regions,id'],
        'station_id' => ['required', 'integer', 'exists:stations,id'],
    ]);

    $region  = \App\Models\Region::findOrFail($request->integer('region_id'));
    $station = \App\Models\Station::findOrFail($request->integer('station_id'));

    // защита от “не того вокзала”
    if ((int)$station->region_id !== (int)$region->id) {
        return response()->json(['message' => 'Вокзал не относится к выбранной РДЖВ'], 422);
    }

    // 1) Все работы (всегда нужны все, даже нули)
    $tree    = $this->buildWorkTreeFromUnicum();
    $workMap = $this->flattenWorkTree($tree);

    // 2) Заготовки нулей
    $plan = [];
    $fact = [];
    foreach ($workMap as $key => $w) {
        $plan[$key] = 0;
        $fact[$key] = 0;
    }

    // 3) Подтягиваем сохранённые (если есть)
    $rdzv   = trim($region->name);
    $vokzal = trim($station->name);

    // ⚠️ Таблица/поля — подставьте ваши реальные названия
    $rows = \Illuminate\Support\Facades\DB::table('preparationdatazima')
        ->where('rdzv', $rdzv)
        ->where('vokzal', $vokzal)
        ->get(['name_work', 'plan_value', 'fact_value']);

    foreach ($rows as $row) {
        $key = md5($row->name_work);
        if (array_key_exists($key, $plan)) {
            $plan[$key] = (int)$row->plan_value;
            $fact[$key] = (int)$row->fact_value;
        }
    }

    return response()->json([
        'plan' => $plan,
        'fact' => $fact,
    ]);
}

}
