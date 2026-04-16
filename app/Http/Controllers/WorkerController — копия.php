<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
//use Maatwebsite\Excel\Facades\Excel;
//use App\Exports\VaccinationExport;

class WorkerController extends Controller
{
    /**
     * Дашборд со статистикой и графиком
     */
  public function dashboard()
{
    // Общая статистика
    $totalWorkers = Worker::count();
    $vaccinatedCount = Worker::where('vakcina', 1)->count();

    // ========== НОВАЯ ЛОГИКА: группировка по РДЖВ + statusVokzal ==========
    
    // 1. Получаем данные по каждой РДЖВ с разбивкой по категориям
    $detailedData = Worker::select(
            'rdzv',
            'statusVokzal',
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(vakcina) as vaccinated")
        )
        ->groupBy('rdzv', 'statusVokzal')
        ->orderByRaw("CASE WHEN rdzv IS NULL THEN 0 ELSE 1 END, rdzv ASC")
        ->orderBy('statusVokzal')
        ->get();

    // 2. Преобразуем данные в удобную структуру для отображения
    $tableData = [];
    
    foreach ($detailedData as $item) {
        $rdzvName = $item->rdzv ?? 'ОУ ДЖВ';
        $category = $item->statusVokzal ?? 'остальные';
        
        // Инициализируем РДЖВ, если её ещё нет
        if (!isset($tableData[$rdzvName])) {
            $tableData[$rdzvName] = [
                'rdzv' => $rdzvName,
                'categories' => [],
                'total_workers' => 0,
                'total_vaccinated' => 0
            ];
        }
        
        // Процент вакцинированных в этой категории
        $vaccinatedPercent = $item->total > 0 
            ? round(($item->vaccinated / $item->total) * 100, 1) 
            : 0;
        
        // Уровень достижения целевого значения 75%
        $targetLevel = round($vaccinatedPercent / 75, 2);
        
        // Добавляем категорию
        $tableData[$rdzvName]['categories'][$category] = [
            'category' => $category,
            'total' => (int) $item->total,
            'vaccinated' => (int) $item->vaccinated,
            'vaccinated_percent' => $vaccinatedPercent,
            'target_level' => $targetLevel
        ];
        
        // Суммируем итоги по РДЖВ
        $tableData[$rdzvName]['total_workers'] += (int) $item->total;
        $tableData[$rdzvName]['total_vaccinated'] += (int) $item->vaccinated;
    }
    
    // 3. Считаем итоги по каждой РДЖВ
    foreach ($tableData as $rdzvName => &$rdzvData) {
        if ($rdzvData['total_workers'] > 0) {
            $rdzvData['vaccinated_percent'] = round(
                ($rdzvData['total_vaccinated'] / $rdzvData['total_workers']) * 100, 1
            );
            $rdzvData['target_level'] = round($rdzvData['vaccinated_percent'] / 75, 2);
        } else {
            $rdzvData['vaccinated_percent'] = 0;
            $rdzvData['target_level'] = 0;
        }
    }

    // 4. Данные для графика (оставляем как было, но используем новые данные)
    $chartData = collect($tableData)->map(function ($rdzvData) use ($totalWorkers) {
        $percent = $totalWorkers > 0 
            ? round(($rdzvData['total_vaccinated'] / $totalWorkers) * 100, 2) 
            : 0;
        
        return [
            'rdzv' => $rdzvData['rdzv'],
            'vaccinated' => $rdzvData['total_vaccinated'],
            'total' => $rdzvData['total_workers'],
            'percent' => $percent,
            'vaccinated_percent' => $rdzvData['vaccinated_percent'],
            'target_level' => $rdzvData['target_level']
        ];
    })->values();

    // Общий процент вакцинации
    $totalVaccinatedPercent = $totalWorkers > 0 
        ? round(($vaccinatedCount / $totalWorkers) * 100, 1) 
        : 0;

    return view('worker.dashboard', compact(
        'totalWorkers', 
        'vaccinatedCount', 
        'chartData',
        'totalVaccinatedPercent',
        'tableData'  // <-- НОВАЯ ПЕРЕМЕННАЯ
    ));
}
    /**
     * Отображение таблицы с фильтрами
     */


public function table(Request $request)
{
    // 1. Список РДЖВ (для селекта)
    $rdzvList = Worker::whereNotNull('rdzv')
        ->distinct()
        ->orderBy('rdzv')
        ->pluck('rdzv');

    // 2. Фильтры
    $selectedRdzv = $request->input('rdzv', 'all');
    $selectedVokzal = $request->input('vokzal', '');

    // 3. JOIN workers + users
    $query = Worker::query()
        ->join('users', 'users.id', '=', 'workers.tabelNumber')
      /*  ->select([
            'workers.*',
            'users.workLocation',
        ]);*/
		->select([
    'workers.*',
    'users.workLocation',
    'users.status as status',
]);


    // 4. Фильтрация по workLocation
    if ($selectedRdzv === 'ou_dzhv') {

        // ОУ ДЖВ
        $query->where('users.workLocation', 'ДЖВ');

    } elseif ($selectedRdzv !== '' && $selectedRdzv !== 'all') {

        // Вокзалы выбранной РДЖВ
        $vokzalsOfRdzv = Worker::where('rdzv', $selectedRdzv)
            ->whereNotNull('vokzal')
            ->distinct()
            ->pluck('vokzal')
            ->toArray();

        if ($selectedVokzal === 'ou_rdzv') {

            // ОУ РДЖВ
            $query->where('users.workLocation', $selectedRdzv);

        } elseif ($selectedVokzal !== '' && $selectedVokzal !== 'all') {

            // Конкретный вокзал
            $query->where('users.workLocation', $selectedVokzal);

        } else {

            // Все в выбранной РДЖВ
            $query->where(function ($q) use ($selectedRdzv, $vokzalsOfRdzv) {
                $q->where('users.workLocation', $selectedRdzv);

                if (!empty($vokzalsOfRdzv)) {
                    $q->orWhereIn('users.workLocation', $vokzalsOfRdzv);
                }
            });
        }
    }

    // 5. Пагинация
    $workers = $query
        ->paginate(50)
        ->appends($request->query());

    // 6. AJAX
    if ($request->ajax()) {
        return view('worker.table_body', compact('workers'));
    }

    return view('worker.table', compact(
        'workers',
        'rdzvList',
        'selectedRdzv',
        'selectedVokzal'
    ));
}


    /**
     * AJAX: Получить список вокзалов по выбранной РДЖВ
     */
    public function getVokzals(Request $request)
    {
        $rdzv = $request->input('rdzv');

        if (!$rdzv) {
            return response()->json([]);
        }

        // Получаем список вокзалов для этой РДЖВ (по алфавиту)
        $vokzalList = Worker::where('rdzv', $rdzv)
            ->whereNotNull('vokzal')
            ->distinct()
            ->orderBy('vokzal')
            ->pluck('vokzal');

        return response()->json($vokzalList);
    }

    public function analytics()
    {
        $statusSiteStats = User::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statusVokzalStats = Worker::selectRaw('statusVokzal, COUNT(*) as count')
            ->groupBy('statusVokzal')
            ->get();

        return view('worker.analytics', compact('statusSiteStats', 'statusVokzalStats'));
    }
	
	/**
 * Экспорт таблицы вакцинации в Excel
 */
/**
 * Экспорт таблицы вакцинации в Excel
 */
public function exportVaccination()
{
    // Получаем данные
    $totalWorkers = Worker::count();
    $vaccinatedCount = Worker::where('vakcina', 1)->count();

    $detailedData = Worker::select(
            'rdzv',
            'statusVokzal',
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(vakcina) as vaccinated")
        )
        ->groupBy('rdzv', 'statusVokzal')
        ->orderByRaw("CASE WHEN rdzv IS NULL THEN 0 ELSE 1 END, rdzv ASC")
        ->orderBy('statusVokzal')
        ->get();

    $tableData = [];
    
    foreach ($detailedData as $item) {
        $rdzvName = $item->rdzv ?? 'ОУ ДЖВ';
        $category = $item->statusVokzal ?? 'остальные';
        
        if (!isset($tableData[$rdzvName])) {
            $tableData[$rdzvName] = [
                'rdzv' => $rdzvName,
                'categories' => [],
                'total_workers' => 0,
                'total_vaccinated' => 0
            ];
        }
        
        $vaccinatedPercent = $item->total > 0 
            ? round(($item->vaccinated / $item->total) * 100, 1) 
            : 0;
        
        $targetLevel = round($vaccinatedPercent / 75, 2);
        
        $tableData[$rdzvName]['categories'][$category] = [
            'category' => $category,
            'total' => (int) $item->total,
            'vaccinated' => (int) $item->vaccinated,
            'vaccinated_percent' => $vaccinatedPercent,
            'target_level' => $targetLevel
        ];
        
        $tableData[$rdzvName]['total_workers'] += (int) $item->total;
        $tableData[$rdzvName]['total_vaccinated'] += (int) $item->vaccinated;
    }
    
    foreach ($tableData as $rdzvName => &$rdzvData) {
        if ($rdzvData['total_workers'] > 0) {
            $rdzvData['vaccinated_percent'] = round(
                ($rdzvData['total_vaccinated'] / $rdzvData['total_workers']) * 100, 1
            );
            $rdzvData['target_level'] = round($rdzvData['vaccinated_percent'] / 75, 2);
        } else {
            $rdzvData['vaccinated_percent'] = 0;
            $rdzvData['target_level'] = 0;
        }
    }

    $totalVaccinatedPercent = $totalWorkers > 0 
        ? round(($vaccinatedCount / $totalWorkers) * 100, 1) 
        : 0;

    // Создаём Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Заголовки
    $sheet->setCellValue('A1', '№ п/п');
    $sheet->setCellValue('B1', 'Наименование РДЖВ');
    $sheet->setCellValue('C1', 'Категория персонала');
    $sheet->setCellValue('D1', 'Численность работников (чел.)');
    $sheet->setCellValue('E1', 'Прошли вакцинацию (чел.)');
    $sheet->setCellValue('F1', '% вакцинированных');
    $sheet->setCellValue('G1', 'Уровень достижения целевого значения 75%');
    
    // Стиль заголовков
    $sheet->getStyle('A1:G1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 11],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D3D3D3']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);
    
    $sheet->getRowDimension(1)->setRowHeight(40);
    
    // Ширина столбцов
    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(50);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(25);
    
    // Заполняем данными
    $row = 2;
    
    // ИТОГО - категория 1
    $itogo_cat1_total = 0;
    $itogo_cat1_vacc = 0;
    foreach ($tableData as $rdzvData) {
        if (isset($rdzvData['categories']['кадры массовых профессий'])) {
            $itogo_cat1_total += $rdzvData['categories']['кадры массовых профессий']['total'];
            $itogo_cat1_vacc += $rdzvData['categories']['кадры массовых профессий']['vaccinated'];
        }
    }
    $itogo_cat1_percent = $itogo_cat1_total > 0 ? round(($itogo_cat1_vacc / $itogo_cat1_total) * 100, 1) : 0;
    $itogo_cat1_level = round($itogo_cat1_percent / 75, 2);
    
    $sheet->setCellValue('A' . $row, '—');
    $sheet->setCellValue('B' . $row, 'ИТОГО');
    $sheet->setCellValue('C' . $row, 'кадры массовых профессий');
    $sheet->setCellValue('D' . $row, $itogo_cat1_total);
    $sheet->setCellValue('E' . $row, $itogo_cat1_vacc);
    $sheet->setCellValue('F' . $row, $itogo_cat1_percent);
    $sheet->setCellValue('G' . $row, $itogo_cat1_level);
    $row++;
    
    // ИТОГО - категория 2
    $itogo_cat2_total = 0;
    $itogo_cat2_vacc = 0;
    foreach ($tableData as $rdzvData) {
        if (isset($rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров'])) {
            $itogo_cat2_total += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['total'];
            $itogo_cat2_vacc += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['vaccinated'];
        }
    }
    $itogo_cat2_percent = $itogo_cat2_total > 0 ? round(($itogo_cat2_vacc / $itogo_cat2_total) * 100, 1) : 0;
    $itogo_cat2_level = round($itogo_cat2_percent / 75, 2);
    
    $sheet->setCellValue('C' . $row, 'работники, непосредственно связанные с обслуживанием пассажиров');
    $sheet->setCellValue('D' . $row, $itogo_cat2_total);
    $sheet->setCellValue('E' . $row, $itogo_cat2_vacc);
    $sheet->setCellValue('F' . $row, $itogo_cat2_percent);
    $sheet->setCellValue('G' . $row, $itogo_cat2_level);
    $row++;
    
    // ИТОГО - категория 3
    $itogo_cat3_total = 0;
    $itogo_cat3_vacc = 0;
    foreach ($tableData as $rdzvData) {
        if (isset($rdzvData['categories']['остальные'])) {
            $itogo_cat3_total += $rdzvData['categories']['остальные']['total'];
            $itogo_cat3_vacc += $rdzvData['categories']['остальные']['vaccinated'];
        }
    }
    $itogo_cat3_percent = $itogo_cat3_total > 0 ? round(($itogo_cat3_vacc / $itogo_cat3_total) * 100, 1) : 0;
    $itogo_cat3_level = round($itogo_cat3_percent / 75, 2);
    
    $sheet->setCellValue('C' . $row, 'остальные');
    $sheet->setCellValue('D' . $row, $itogo_cat3_total);
    $sheet->setCellValue('E' . $row, $itogo_cat3_vacc);
    $sheet->setCellValue('F' . $row, $itogo_cat3_percent);
    $sheet->setCellValue('G' . $row, $itogo_cat3_level);
    $row++;
    
    // ИТОГО - ВСЕГО
    $totalTargetLevel = round($totalVaccinatedPercent / 75, 2);
    $sheet->setCellValue('C' . $row, 'ВСЕГО');
    $sheet->setCellValue('D' . $row, $totalWorkers);
    $sheet->setCellValue('E' . $row, $vaccinatedCount);
    $sheet->setCellValue('F' . $row, $totalVaccinatedPercent);
    $sheet->setCellValue('G' . $row, $totalTargetLevel);
    
    // Стиль для блока ИТОГО
    $sheet->getStyle('A2:G5')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E8F4F8']
        ]
    ]);
    
    $row++;
    
    // Данные по РДЖВ
    $rowNumber = 1;
    foreach ($tableData as $rdzvData) {
        $categories = [
            'кадры массовых профессий',
            'работники, непосредственно связанные с обслуживанием пассажиров',
            'остальные'
        ];
        
        foreach ($categories as $catIndex => $categoryName) {
            if ($catIndex === 0) {
                $sheet->setCellValue('A' . $row, $rowNumber);
                $sheet->setCellValue('B' . $row, $rdzvData['rdzv']);
            }
            
            $sheet->setCellValue('C' . $row, $categoryName);
            
            if (isset($rdzvData['categories'][$categoryName])) {
                $catData = $rdzvData['categories'][$categoryName];
                $sheet->setCellValue('D' . $row, $catData['total']);
                $sheet->setCellValue('E' . $row, $catData['vaccinated']);
                $sheet->setCellValue('F' . $row, $catData['vaccinated_percent']);
                $sheet->setCellValue('G' . $row, $catData['target_level']);
            } else {
                $sheet->setCellValue('D' . $row, 0);
                $sheet->setCellValue('E' . $row, 0);
                $sheet->setCellValue('F' . $row, 0);
                $sheet->setCellValue('G' . $row, 0);
            }
            
            $row++;
        }
        
        // ВСЕГО по РДЖВ
        $sheet->setCellValue('C' . $row, 'ВСЕГО');
        $sheet->setCellValue('D' . $row, $rdzvData['total_workers']);
        $sheet->setCellValue('E' . $row, $rdzvData['total_vaccinated']);
        $sheet->setCellValue('F' . $row, $rdzvData['vaccinated_percent']);
        $sheet->setCellValue('G' . $row, $rdzvData['target_level']);
        
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F0F0']
            ]
        ]);
        
        $row++;
        $rowNumber++;
    }
    
    // Границы
    $lastRow = $row - 1;
    $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ]);
    
    $sheet->getStyle('B2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('C2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    
    // Сохраняем
    $fileName = 'Vakcinaciya_DZhV_' . date('Y-m-d') . '.xlsx';
    
    $writer = new Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}
}