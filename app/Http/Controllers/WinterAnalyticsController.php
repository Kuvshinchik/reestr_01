<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WorkItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WinterAnalyticsController extends Controller
{
    /**
     * Главная страница с аналитической запиской
     */
    public function index(Request $request)
    {
        $selectedRegionId = $request->query('region_id', 'all');
        $selectedCategoryId = $request->query('category_id', 'all');

        // Список регионов для Select
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();

        // Категории работ (level = '1')
        $categories = WorkItem::where('level', '1')
            ->whereIn('id', [1, 16, 18])
            ->orderBy('id')
            ->get();

        // Генерируем аналитическую записку
        $analytics = $this->generateAnalytics($selectedRegionId, $selectedCategoryId);

        return view('winter-analytics.index', compact(
            'regions',
            'categories',
            'selectedRegionId',
            'selectedCategoryId',
            'analytics'
        ));
    }

    /**
     * Генерация аналитической записки
     */
    private function generateAnalytics(string $regionId, string $categoryId): array
    {
        // Получаем work_item_ids для выбранной категории
        $workItemIds = $this->getWorkItemIds($categoryId);
        
        // Получаем данные
        $data = $this->getData($regionId, $workItemIds);
        
        // Генерируем разделы записки
        $analytics = [
            'title' => $this->generateTitle($regionId, $categoryId),
            'date' => now()->format('d.m.Y'),
            'summary' => $this->generateSummary($data, $regionId, $categoryId),
            'details' => $this->generateDetails($data, $regionId, $categoryId),
            'problems' => $this->generateProblems($data, $regionId),
            'leaders' => $this->generateLeaders($data, $regionId),
            'recommendations' => $this->generateRecommendations($data, $regionId, $categoryId),
            'conclusion' => $this->generateConclusion($data),
            'raw_data' => $data, // Для отладки и дополнительного отображения
        ];

        return $analytics;
    }

    /**
     * Получить ID работ для категории
     */
    private function getWorkItemIds(string $categoryId): array
    {
        if ($categoryId === 'all') {
            $parentIds = [1, 16, 18];
        } else {
            $parentIds = [(int)$categoryId];
        }

        $allItems = WorkItem::all();
        $byParent = $allItems->groupBy('parent_id');

        $isLeaf = fn($item) => !$byParent->has($item->id);

        $getLeafDescendants = function ($id) use (&$getLeafDescendants, $byParent, $isLeaf) {
            $children = $byParent->get($id, collect());
            if ($children->isEmpty()) return collect();

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

        $leafIds = collect();
        foreach ($parentIds as $parentId) {
            $leafIds = $leafIds->merge($getLeafDescendants($parentId));
        }

        return $leafIds->unique()->values()->toArray();
    }

    /**
     * Получить данные для анализа
     */
    private function getData(string $regionId, array $workItemIds): array
    {
        if (empty($workItemIds)) {
            return ['total' => ['plan' => 0, 'fact' => 0], 'items' => [], 'by_category' => []];
        }

        // Общие данные
        if ($regionId === 'all') {
            // По всем РДЖВ
            $items = DB::table('winter_preparations as wp')
                ->select([
                    'r.id',
                    'r.name as label',
                    DB::raw('SUM(wp.plan) as plan'),
                    DB::raw('SUM(wp.fact) as fact'),
                ])
                ->join('stations as s', 's.id', '=', 'wp.station_id')
                ->join('regions as r', 'r.id', '=', 's.region_id')
                ->whereIn('wp.work_item_id', $workItemIds)
                ->whereNotIn('r.id', [16, 17])
                ->groupBy('r.id', 'r.name')
                ->orderBy('r.name')
                ->get();
        } else {
            // По вокзалам региона
            $items = DB::table('winter_preparations as wp')
                ->select([
                    's.id',
                    's.name as label',
                    DB::raw('SUM(wp.plan) as plan'),
                    DB::raw('SUM(wp.fact) as fact'),
                ])
                ->join('stations as s', 's.id', '=', 'wp.station_id')
                ->where('s.region_id', $regionId)
                ->whereIn('wp.work_item_id', $workItemIds)
                ->groupBy('s.id', 's.name')
                ->orderBy('s.name')
                ->get();
        }

        // Данные по категориям (упрощенный запрос)
        $categories = WorkItem::where('level', '1')->whereIn('id', [1, 16, 18])->get();
        $byCategoryArray = [];
        
        foreach ($categories as $cat) {
            // Получаем все листовые work_items для этой категории
            $catWorkIds = $this->getWorkItemIds((string)$cat->id);
            
            if (empty($catWorkIds)) continue;
            
            $catData = DB::table('winter_preparations as wp')
                ->select([
                    DB::raw('SUM(wp.plan) as plan'),
                    DB::raw('SUM(wp.fact) as fact'),
                ])
                ->join('stations as s', 's.id', '=', 'wp.station_id')
                ->whereIn('wp.work_item_id', $catWorkIds)
                ->when($regionId !== 'all', function ($q) use ($regionId) {
                    $q->where('s.region_id', $regionId);
                })
                ->when($regionId === 'all', function ($q) {
                    $q->whereNotIn('s.region_id', [16, 17]);
                })
                ->first();
            
            if ($catData && ($catData->plan > 0 || $catData->fact > 0)) {
                $percent = $catData->plan > 0 ? round(($catData->fact / $catData->plan) * 100, 1) : 0;
                $byCategoryArray[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'plan' => (int)$catData->plan,
                    'fact' => (int)$catData->fact,
                    'percent' => $percent,
                ];
            }
        }

        // Рассчитываем итоги
        $totalPlan = $items->sum('plan');
        $totalFact = $items->sum('fact');

        // Преобразуем в массив с процентами
        $itemsArray = $items->map(function ($item) {
            $percent = $item->plan > 0 ? round(($item->fact / $item->plan) * 100, 1) : 0;
            return [
                'id' => $item->id,
                'label' => $item->label,
                'plan' => (int)$item->plan,
                'fact' => (int)$item->fact,
                'percent' => $percent,
                'diff' => (int)$item->plan - (int)$item->fact,
            ];
        })->toArray();

        return [
            'total' => [
                'plan' => $totalPlan,
                'fact' => $totalFact,
                'percent' => $totalPlan > 0 ? round(($totalFact / $totalPlan) * 100, 1) : 0,
                'diff' => $totalPlan - $totalFact,
            ],
            'items' => $itemsArray,
            'by_category' => $byCategoryArray,
        ];
    }

    /**
     * Генерация заголовка
     */
    private function generateTitle(string $regionId, string $categoryId): string
    {
        $regionName = $regionId === 'all' 
            ? 'ДЖВ' 
            : Region::find($regionId)?->name ?? 'РДЖВ';

        $categoryName = $categoryId === 'all'
            ? 'всем категориям работ'
            : WorkItem::find($categoryId)?->name ?? 'выбранной категории';

        return "Аналитическая записка по подготовке к зиме: {$regionName}";
    }

    /**
     * Генерация резюме
     */
    private function generateSummary(array $data, string $regionId, string $categoryId): string
    {
        $total = $data['total'];
        $percent = $total['percent'];
        $plan = number_format($total['plan'], 0, ',', ' ');
        $fact = number_format($total['fact'], 0, ',', ' ');
        $diff = number_format($total['diff'], 0, ',', ' ');

        $regionName = $regionId === 'all' ? 'ДЖВ' : Region::find($regionId)?->name;
        $entityType = $regionId === 'all' ? 'РДЖВ' : 'вокзалам';

        // Определяем оценку выполнения
        $assessment = $this->getAssessment($percent);

        $categoryText = '';
        if ($categoryId !== 'all') {
            $catName = WorkItem::find($categoryId)?->name;
            $categoryText = " по категории «{$catName}»";
        }

        $text = "По состоянию на текущую дату общий уровень выполнения плана подготовки к зиме ";
        $text .= "по {$regionName}{$categoryText} составляет <strong>{$percent}%</strong>. ";
        $text .= "Из запланированных <strong>{$plan}</strong> единиц работ выполнено <strong>{$fact}</strong>. ";
        
        if ($total['diff'] > 0) {
            $text .= "Отставание от плана составляет <strong>{$diff}</strong> единиц. ";
        } else {
            $text .= "План выполнен полностью. ";
        }

        $text .= $assessment['text'];

        return $text;
    }

    /**
     * Генерация детального анализа
     */
    private function generateDetails(array $data, string $regionId, string $categoryId): array
    {
        $details = [];

        // Анализ по категориям (если выбраны все)
        if ($categoryId === 'all' && !empty($data['by_category'])) {
            $categoryDetails = "Анализ выполнения по категориям работ:\n\n";
            
            foreach ($data['by_category'] as $cat) {
                $assessment = $this->getAssessment($cat['percent']);
                $plan = number_format($cat['plan'], 0, ',', ' ');
                $fact = number_format($cat['fact'], 0, ',', ' ');
                
                $categoryDetails .= "<strong>{$cat['name']}</strong>: выполнено {$cat['percent']}% ";
                $categoryDetails .= "(план: {$plan}, факт: {$fact}). ";
                $categoryDetails .= $assessment['short'] . "\n\n";
            }

            $details[] = [
                'title' => 'Выполнение по категориям работ',
                'content' => $categoryDetails,
            ];
        }

        // Анализ распределения
        $items = $data['items'];
        if (!empty($items)) {
            $completed = array_filter($items, fn($i) => $i['percent'] >= 100);
            $inProgress = array_filter($items, fn($i) => $i['percent'] >= 80 && $i['percent'] < 100);
            $lagging = array_filter($items, fn($i) => $i['percent'] < 80);

            $entityType = $regionId === 'all' ? 'РДЖВ' : 'вокзалов';
            $total = count($items);
            
            $distText = "Из {$total} {$entityType}:\n";
            $distText .= "• <span class='text-success'>Выполнили план (100%)</span>: " . count($completed) . "\n";
            $distText .= "• <span class='text-warning'>Близки к выполнению (80-99%)</span>: " . count($inProgress) . "\n";
            $distText .= "• <span class='text-danger'>Отстают (менее 80%)</span>: " . count($lagging) . "\n";

            $details[] = [
                'title' => 'Распределение по степени выполнения',
                'content' => $distText,
            ];
        }

        return $details;
    }

    /**
     * Генерация списка проблемных областей
     */
    private function generateProblems(array $data, string $regionId): array
    {
        $problems = [];
        $items = $data['items'];
        
        // Сортируем по проценту выполнения (от меньшего к большему)
        usort($items, fn($a, $b) => $a['percent'] <=> $b['percent']);
        
        // Берем топ-5 отстающих с процентом < 90%
        $lagging = array_filter($items, fn($i) => $i['percent'] < 90);
        $lagging = array_slice($lagging, 0, 5);

        $entityType = $regionId === 'all' ? 'РДЖВ' : 'Вокзал';

        foreach ($lagging as $item) {
            $severity = $this->getSeverity($item['percent']);
            $diff = number_format($item['diff'], 0, ',', ' ');
            
            $problems[] = [
                'entity' => $item['label'],
                'percent' => $item['percent'],
                'diff' => $item['diff'],
                'severity' => $severity,
                'text' => "{$entityType} <strong>{$item['label']}</strong>: выполнено {$item['percent']}%, " .
                         "отставание {$diff} ед. {$severity['recommendation']}",
            ];
        }

        return $problems;
    }

    /**
     * Генерация списка лидеров
     */
    private function generateLeaders(array $data, string $regionId): array
    {
        $leaders = [];
        $items = $data['items'];
        
        // Сортируем по проценту выполнения (от большего к меньшему)
        usort($items, fn($a, $b) => $b['percent'] <=> $a['percent']);
        
        // Берем топ-5 лидеров с процентом >= 95%
        $top = array_filter($items, fn($i) => $i['percent'] >= 95);
        $top = array_slice($top, 0, 5);

        $entityType = $regionId === 'all' ? 'РДЖВ' : 'Вокзал';

        foreach ($top as $item) {
            $fact = number_format($item['fact'], 0, ',', ' ');
            $plan = number_format($item['plan'], 0, ',', ' ');
            
            $status = $item['percent'] >= 100 ? 'план выполнен полностью' : 'близок к завершению';
            
            $leaders[] = [
                'entity' => $item['label'],
                'percent' => $item['percent'],
                'text' => "{$entityType} <strong>{$item['label']}</strong>: {$item['percent']}% ({$status})",
            ];
        }

        return $leaders;
    }

    /**
     * Генерация рекомендаций
     */
    private function generateRecommendations(array $data, string $regionId, string $categoryId): array
    {
        $recommendations = [];
        $percent = $data['total']['percent'];
        $items = $data['items'];

        // Общая рекомендация по уровню выполнения
        if ($percent < 70) {
            $recommendations[] = [
                'priority' => 'high',
                'text' => 'Необходимо срочное усиление контроля за выполнением работ. ' .
                         'Рекомендуется провести совещание с ответственными лицами для выявления причин отставания.',
            ];
        } elseif ($percent < 90) {
            $recommendations[] = [
                'priority' => 'medium',
                'text' => 'Требуется активизация работ для достижения плановых показателей. ' .
                         'Рекомендуется еженедельный мониторинг выполнения.',
            ];
        } else {
            $recommendations[] = [
                'priority' => 'low',
                'text' => 'Работы выполняются в соответствии с планом. ' .
                         'Рекомендуется продолжить текущий темп выполнения.',
            ];
        }

        // Рекомендации по отстающим
        $lagging = array_filter($items, fn($i) => $i['percent'] < 80);
        if (count($lagging) > 0) {
            $entityType = $regionId === 'all' ? 'РДЖВ' : 'вокзалам';
            $names = array_map(fn($i) => $i['label'], array_slice($lagging, 0, 3));
            
            $recommendations[] = [
                'priority' => 'high',
                'text' => 'Обратить особое внимание на ' . $entityType . ': ' . implode(', ', $names) . '. ' .
                         'Рекомендуется запросить объяснительные записки о причинах отставания.',
            ];
        }

        // Рекомендации по категориям
        if ($categoryId === 'all' && !empty($data['by_category'])) {
            $laggingCats = array_filter($data['by_category'], fn($c) => $c['percent'] < 85);
            foreach ($laggingCats as $cat) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'text' => "По категории «{$cat['name']}» выполнение составляет {$cat['percent']}%. " .
                             "Рекомендуется усилить контроль данного направления.",
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Генерация заключения
     */
    private function generateConclusion(array $data): string
    {
        $percent = $data['total']['percent'];
        $assessment = $this->getAssessment($percent);

        $conclusion = "На основании проведённого анализа общая оценка готовности к зимнему периоду: ";
        $conclusion .= "<strong class='{$assessment['class']}'>{$assessment['grade']}</strong>. ";
        
        if ($percent >= 95) {
            $conclusion .= "Подготовка к зиме находится на завершающей стадии. ";
            $conclusion .= "При сохранении текущего темпа все работы будут выполнены в срок.";
        } elseif ($percent >= 80) {
            $conclusion .= "Необходимо завершить оставшиеся работы в кратчайшие сроки. ";
            $conclusion .= "Рекомендуется усилить контроль за выполнением плана.";
        } elseif ($percent >= 60) {
            $conclusion .= "Требуется существенная активизация работ. ";
            $conclusion .= "Рекомендуется принять меры для ускорения выполнения плана.";
        } else {
            $conclusion .= "Ситуация требует немедленного вмешательства. ";
            $conclusion .= "Рекомендуется провести экстренное совещание для выработки мер по исправлению ситуации.";
        }

        return $conclusion;
    }

    /**
     * Получить оценку по проценту выполнения
     */
    private function getAssessment(float $percent): array
    {
        if ($percent >= 95) {
            return [
                'grade' => 'Отлично',
                'short' => 'Выполнение на высоком уровне.',
                'text' => 'Общая оценка выполнения: <span class="text-success"><strong>отлично</strong></span>.',
                'class' => 'text-success',
            ];
        } elseif ($percent >= 80) {
            return [
                'grade' => 'Хорошо',
                'short' => 'Выполнение на хорошем уровне.',
                'text' => 'Общая оценка выполнения: <span class="text-primary"><strong>хорошо</strong></span>.',
                'class' => 'text-primary',
            ];
        } elseif ($percent >= 60) {
            return [
                'grade' => 'Удовлетворительно',
                'short' => 'Требуется усиление работ.',
                'text' => 'Общая оценка выполнения: <span class="text-warning"><strong>удовлетворительно</strong></span>.',
                'class' => 'text-warning',
            ];
        } else {
            return [
                'grade' => 'Неудовлетворительно',
                'short' => 'Критическое отставание!',
                'text' => 'Общая оценка выполнения: <span class="text-danger"><strong>неудовлетворительно</strong></span>.',
                'class' => 'text-danger',
            ];
        }
    }

    /**
     * Получить степень серьезности проблемы
     */
    private function getSeverity(float $percent): array
    {
        if ($percent < 50) {
            return [
                'level' => 'critical',
                'class' => 'text-danger',
                'badge' => 'danger',
                'recommendation' => 'Требуется немедленное вмешательство!',
            ];
        } elseif ($percent < 70) {
            return [
                'level' => 'high',
                'class' => 'text-danger',
                'badge' => 'warning',
                'recommendation' => 'Требуется срочное усиление работ.',
            ];
        } else {
            return [
                'level' => 'medium',
                'class' => 'text-warning',
                'badge' => 'info',
                'recommendation' => 'Необходим дополнительный контроль.',
            ];
        }
    }
}
