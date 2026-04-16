<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WinterWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * =====================================================================
 * КОНТРОЛЛЕР АНАЛИТИЧЕСКОЙ ЗАПИСКИ ПО ПЕРВОЗИМНИКАМ
 * =====================================================================
 * 
 * Генерирует аналитическую записку с динамическим текстом,
 * который меняется в зависимости от данных и выбранного РДЖВ.
 * 
 * Разделы записки:
 * 1. Краткое резюме - общая статистика
 * 2. Ключевые показатели - числовые метрики
 * 3. Детальный анализ - разбивка по РДЖВ/вокзалам
 * 4. Проблемные области - где мало обученных
 * 5. Лидеры - где высокий % обучения
 * 6. Рекомендации - что делать
 * 7. Заключение - итоговая оценка
 */
class WinterWorkerAnalyticsController extends Controller
{
    /**
     * -----------------------------------------------------------------
     * Главная страница с аналитической запиской
     * -----------------------------------------------------------------
     */
    public function index(Request $request)
    {
        // Получаем выбранный регион из URL (по умолчанию 'all' = ДЖВ)
        $selectedRegionId = $request->query('region_id', 'all');

        // Список регионов для Select (без ДЖВ и КЛНГ)
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();

        // Генерируем аналитическую записку
        $analytics = $this->generateAnalytics($selectedRegionId);

        return view('winter-worker-analytics.index', compact(
            'regions',
            'selectedRegionId',
            'analytics'
        ));
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ АНАЛИТИЧЕСКОЙ ЗАПИСКИ
     * -----------------------------------------------------------------
     */
    private function generateAnalytics(string $regionId): array
    {
        // Получаем данные из БД
        $data = $this->getData($regionId);
        
        // Генерируем каждый раздел записки
        return [
            'title' => $this->generateTitle($regionId),
            'date' => now()->format('d.m.Y'),
            'summary' => $this->generateSummary($data, $regionId),
            'details' => $this->generateDetails($data, $regionId),
            'problems' => $this->generateProblems($data, $regionId),
            'leaders' => $this->generateLeaders($data, $regionId),
            'recommendations' => $this->generateRecommendations($data, $regionId),
            'conclusion' => $this->generateConclusion($data),
            'raw_data' => $data,
        ];
    }

    /**
     * -----------------------------------------------------------------
     * ПОЛУЧЕНИЕ ДАННЫХ ИЗ БД
     * -----------------------------------------------------------------
     */
    private function getData(string $regionId): array
    {
        if ($regionId === 'all') {
            // Данные по всем РДЖВ
            $items = DB::table('regions')
                ->leftJoin('stations', 'stations.region_id', '=', 'regions.id')
                ->leftJoin('winter_workers', 'winter_workers.station_id', '=', 'stations.id')
                ->whereNotIn('regions.id', [16, 17])
                ->groupBy('regions.id', 'regions.name')
                ->select(
                    'regions.id',
                    'regions.name as label',
                    DB::raw('COUNT(winter_workers.id) as hired'),
                    DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
                )
                ->orderBy('regions.name')
                ->get();
        } else {
            // Данные по вокзалам выбранного РДЖВ
            $items = DB::table('stations')
                ->leftJoin('winter_workers', 'winter_workers.station_id', '=', 'stations.id')
                ->where('stations.region_id', $regionId)
                ->groupBy('stations.id', 'stations.name')
                ->select(
                    'stations.id',
                    'stations.name as label',
                    DB::raw('COUNT(winter_workers.id) as hired'),
                    DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
                )
                ->orderBy('stations.name')
                ->get();
        }

        // Рассчитываем итоги
        $totalHired = $items->sum('hired');
        $totalTrained = $items->sum('trained');
        $totalNotTrained = $totalHired - $totalTrained;

        // Преобразуем в массив с процентами
        $itemsArray = $items->map(function ($item) {
            $percent = $item->hired > 0 
                ? round(($item->trained / $item->hired) * 100, 1) 
                : 0;
            
            return [
                'id' => $item->id,
                'label' => $item->label,
                'hired' => (int) $item->hired,
                'trained' => (int) $item->trained,
                'not_trained' => (int) $item->hired - (int) $item->trained,
                'percent' => $percent,
            ];
        })->toArray();

        return [
            'total' => [
                'hired' => $totalHired,
                'trained' => $totalTrained,
                'not_trained' => $totalNotTrained,
                'percent' => $totalHired > 0 
                    ? round(($totalTrained / $totalHired) * 100, 1) 
                    : 0,
            ],
            'items' => $itemsArray,
        ];
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ ЗАГОЛОВКА
     * -----------------------------------------------------------------
     */
    private function generateTitle(string $regionId): string
    {
        if ($regionId === 'all') {
            return 'Аналитическая записка по обучению первозимников (ДЖВ)';
        }
        
        $region = Region::find($regionId);
        $regionName = $region ? $region->name : 'РДЖВ';
        
        return "Аналитическая записка по обучению первозимников ({$regionName})";
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ КРАТКОГО РЕЗЮМЕ
     * -----------------------------------------------------------------
     */
    private function generateSummary(array $data, string $regionId): string
    {
        $total = $data['total'];
        $percent = $total['percent'];
        $hired = number_format($total['hired'], 0, ',', ' ');
        $trained = number_format($total['trained'], 0, ',', ' ');
        $notTrained = number_format($total['not_trained'], 0, ',', ' ');

        $entityType = $regionId === 'all' ? 'по ДЖВ' : 'по выбранному РДЖВ';
        
        // Базовая статистика
        $summary = "По состоянию на текущую дату {$entityType} зарегистрировано ";
        $summary .= "<strong>{$hired}</strong> первозимников. ";
        $summary .= "Из них прошли обучение <strong>{$trained}</strong> человек ";
        $summary .= "(<strong>{$percent}%</strong>). ";
        $summary .= "Не прошли обучение: <strong>{$notTrained}</strong> человек.";

        // Оценка ситуации
        $summary .= "<br><br>";
        
        if ($percent >= 90) {
            $summary .= "<span class='text-success'><strong>Ситуация благоприятная.</strong></span> ";
            $summary .= "Подавляющее большинство первозимников прошли необходимое обучение. ";
            $summary .= "Работа по подготовке кадров к зимнему периоду выполняется на высоком уровне.";
        } elseif ($percent >= 70) {
            $summary .= "<span class='text-primary'><strong>Ситуация удовлетворительная.</strong></span> ";
            $summary .= "Большая часть первозимников прошла обучение, однако остаётся значительное количество необученных сотрудников. ";
            $summary .= "Рекомендуется активизировать работу по обучению оставшихся.";
        } elseif ($percent >= 50) {
            $summary .= "<span class='text-warning'><strong>Ситуация требует внимания.</strong></span> ";
            $summary .= "Менее {$percent}% первозимников прошли обучение. ";
            $summary .= "Необходимо срочно усилить работу по организации обучения.";
        } else {
            $summary .= "<span class='text-danger'><strong>Критическая ситуация!</strong></span> ";
            $summary .= "Обучено менее половины первозимников. ";
            $summary .= "Требуются немедленные меры по организации массового обучения персонала.";
        }

        return $summary;
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ ДЕТАЛЬНОГО АНАЛИЗА
     * -----------------------------------------------------------------
     */
    private function generateDetails(array $data, string $regionId): array
    {
        $details = [];
        $items = $data['items'];
        $total = $data['total'];
        
        if (empty($items)) {
            return $details;
        }

        $entityType = $regionId === 'all' ? 'РДЖВ' : 'вокзалов';
        $entityCount = count($items);
        $withWorkers = count(array_filter($items, fn($i) => $i['hired'] > 0));

        // Блок 1: Общая структура
        $details[] = [
            'title' => 'Структура данных',
            'content' => "Анализ охватывает <strong>{$entityCount}</strong> {$entityType}. " .
                        "Первозимники зарегистрированы в <strong>{$withWorkers}</strong> из них. " .
                        "Средний показатель обучения составляет <strong>{$total['percent']}%</strong>."
        ];

        // Блок 2: Распределение по уровню обучения
        $high = count(array_filter($items, fn($i) => $i['percent'] >= 90 && $i['hired'] > 0));
        $medium = count(array_filter($items, fn($i) => $i['percent'] >= 70 && $i['percent'] < 90 && $i['hired'] > 0));
        $low = count(array_filter($items, fn($i) => $i['percent'] < 70 && $i['hired'] > 0));

        $entityTypeSingle = $regionId === 'all' ? 'РДЖВ' : 'вокзалов';
        
        $content = "Распределение по уровню обучения:<br>";
        $content .= "• Высокий уровень (≥90%): <strong class='text-success'>{$high}</strong> {$entityTypeSingle}<br>";
        $content .= "• Средний уровень (70-89%): <strong class='text-primary'>{$medium}</strong> {$entityTypeSingle}<br>";
        $content .= "• Низкий уровень (&lt;70%): <strong class='text-danger'>{$low}</strong> {$entityTypeSingle}";

        $details[] = [
            'title' => 'Распределение по уровню обучения',
            'content' => $content
        ];

        // Блок 3: Топ по количеству первозимников
        $topByHired = array_filter($items, fn($i) => $i['hired'] > 0);
        usort($topByHired, fn($a, $b) => $b['hired'] <=> $a['hired']);
        $topByHired = array_slice($topByHired, 0, 5);

        if (!empty($topByHired)) {
            $content = "Наибольшее количество первозимников:<br>";
            foreach ($topByHired as $item) {
                $content .= "• <strong>{$item['label']}</strong>: {$item['hired']} чел. (обучено {$item['percent']}%)<br>";
            }
            
            $details[] = [
                'title' => 'Концентрация первозимников',
                'content' => trim($content, '<br>')
            ];
        }

        return $details;
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ ПРОБЛЕМНЫХ ОБЛАСТЕЙ
     * -----------------------------------------------------------------
     */
    private function generateProblems(array $data, string $regionId): array
    {
        $problems = [];
        $items = array_filter($data['items'], fn($i) => $i['hired'] > 0);
        
        // Сортируем по проценту (от меньшего к большему)
        usort($items, fn($a, $b) => $a['percent'] <=> $b['percent']);
        
        // Берем топ-5 с процентом < 80%
        $lagging = array_filter($items, fn($i) => $i['percent'] < 80);
        $lagging = array_slice($lagging, 0, 5);

        $entityType = $regionId === 'all' ? 'РДЖВ' : 'Вокзал';

        foreach ($lagging as $item) {
            $severity = $this->getSeverity($item['percent']);
            $notTrained = $item['not_trained'];
            
            $problems[] = [
                'entity' => $item['label'],
                'percent' => $item['percent'],
                'not_trained' => $notTrained,
                'severity' => $severity,
                'text' => "{$entityType} <strong>{$item['label']}</strong>: " .
                         "обучено {$item['percent']}% ({$item['trained']} из {$item['hired']} чел.), " .
                         "не обучено <strong>{$notTrained}</strong> чел. " .
                         "{$severity['recommendation']}",
            ];
        }

        return $problems;
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ ЛИДЕРОВ
     * -----------------------------------------------------------------
     */
    private function generateLeaders(array $data, string $regionId): array
    {
        $leaders = [];
        $items = array_filter($data['items'], fn($i) => $i['hired'] > 0);
        
        // Сортируем по проценту (от большего к меньшему)
        usort($items, fn($a, $b) => $b['percent'] <=> $a['percent']);
        
        // Берем топ-5 с процентом >= 90%
        $top = array_filter($items, fn($i) => $i['percent'] >= 90);
        $top = array_slice($top, 0, 5);

        $entityType = $regionId === 'all' ? 'РДЖВ' : 'Вокзал';

        foreach ($top as $item) {
            $status = $item['percent'] >= 100 
                ? 'все первозимники обучены' 
                : 'высокий уровень обучения';
            
            $leaders[] = [
                'entity' => $item['label'],
                'percent' => $item['percent'],
                'text' => "{$entityType} <strong>{$item['label']}</strong>: " .
                         "{$item['percent']}% ({$item['trained']} из {$item['hired']} чел.) — {$status}",
            ];
        }

        return $leaders;
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ РЕКОМЕНДАЦИЙ
     * -----------------------------------------------------------------
     */
    private function generateRecommendations(array $data, string $regionId): array
    {
        $recommendations = [];
        $percent = $data['total']['percent'];
        $items = array_filter($data['items'], fn($i) => $i['hired'] > 0);

        // Рекомендация по общему уровню
        if ($percent < 50) {
            $recommendations[] = [
                'priority' => 'high',
                'text' => 'Необходимо НЕМЕДЛЕННО организовать массовое обучение первозимников. ' .
                         'Рекомендуется провести экстренное совещание с руководителями подразделений ' .
                         'для разработки плана ускоренного обучения.',
            ];
        } elseif ($percent < 70) {
            $recommendations[] = [
                'priority' => 'high',
                'text' => 'Требуется срочное усиление работы по обучению первозимников. ' .
                         'Рекомендуется составить график обучения с указанием конкретных сроков и ответственных.',
            ];
        } elseif ($percent < 90) {
            $recommendations[] = [
                'priority' => 'medium',
                'text' => 'Необходимо завершить обучение оставшихся первозимников в кратчайшие сроки. ' .
                         'Рекомендуется еженедельный контроль выполнения плана обучения.',
            ];
        } else {
            $recommendations[] = [
                'priority' => 'low',
                'text' => 'Работа по обучению первозимников выполняется на высоком уровне. ' .
                         'Рекомендуется завершить обучение оставшихся сотрудников и продолжить контроль.',
            ];
        }

        // Рекомендации по проблемным областям
        $lagging = array_filter($items, fn($i) => $i['percent'] < 70);
        if (count($lagging) > 0) {
            $entityType = $regionId === 'all' ? 'РДЖВ' : 'вокзалам';
            $names = array_map(fn($i) => $i['label'], array_slice($lagging, 0, 3));
            
            $recommendations[] = [
                'priority' => 'high',
                'text' => 'Обратить особое внимание на ' . $entityType . ' с критически низким уровнем обучения: ' .
                         '<strong>' . implode(', ', $names) . '</strong>. ' .
                         'Рекомендуется запросить объяснительные записки о причинах отставания.',
            ];
        }

        // Рекомендация по необученным
        $notTrained = $data['total']['not_trained'];
        if ($notTrained > 0) {
            $notTrainedFormatted = number_format($notTrained, 0, ',', ' ');
            $recommendations[] = [
                'priority' => 'medium',
                'text' => "Составить поимённый список из <strong>{$notTrainedFormatted}</strong> необученных первозимников " .
                         "и определить даты их обучения. Назначить ответственных за контроль явки.",
            ];
        }

        return $recommendations;
    }

    /**
     * -----------------------------------------------------------------
     * ГЕНЕРАЦИЯ ЗАКЛЮЧЕНИЯ
     * -----------------------------------------------------------------
     */
    private function generateConclusion(array $data): string
    {
        $percent = $data['total']['percent'];
        $assessment = $this->getAssessment($percent);

        $conclusion = "На основании проведённого анализа общая оценка работы по обучению первозимников: ";
        $conclusion .= "<strong class='{$assessment['class']}'>{$assessment['grade']}</strong>. ";
        
        if ($percent >= 95) {
            $conclusion .= "Практически все первозимники прошли необходимое обучение. ";
            $conclusion .= "Подразделения готовы к работе в зимний период.";
        } elseif ($percent >= 80) {
            $conclusion .= "Большинство первозимников обучены. ";
            $conclusion .= "Необходимо завершить обучение оставшихся сотрудников в кратчайшие сроки.";
        } elseif ($percent >= 60) {
            $conclusion .= "Требуется существенная активизация работы по обучению. ";
            $conclusion .= "Рекомендуется принять меры для ускорения процесса обучения.";
        } else {
            $conclusion .= "Ситуация требует немедленного вмешательства. ";
            $conclusion .= "Рекомендуется провести экстренное совещание для выработки мер по исправлению ситуации.";
        }

        return $conclusion;
    }

    /**
     * -----------------------------------------------------------------
     * ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
     * -----------------------------------------------------------------
     */
    
    private function getAssessment(float $percent): array
    {
        if ($percent >= 95) {
            return [
                'grade' => 'Отлично',
                'class' => 'text-success',
            ];
        } elseif ($percent >= 80) {
            return [
                'grade' => 'Хорошо',
                'class' => 'text-primary',
            ];
        } elseif ($percent >= 60) {
            return [
                'grade' => 'Удовлетворительно',
                'class' => 'text-warning',
            ];
        } else {
            return [
                'grade' => 'Неудовлетворительно',
                'class' => 'text-danger',
            ];
        }
    }

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
