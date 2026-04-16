<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\Region; 
use App\Models\Station; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WorkerController extends Controller
{
    /**
     * Вспомогательный метод для фильтрации запроса (чтобы не дублировать код)
     */
    private function applyFilters($query, Request $request)
    {
        $selectedRdzv = $request->input('rdzv', 'all');
        $selectedVokzal = $request->input('vokzal', '');

        if ($selectedRdzv === 'ou_dzhv') {
            $query->where('users.workLocation', 'ДЖВ');
        } elseif ($selectedRdzv !== '' && $selectedRdzv !== 'all') {
            $region = Region::where('name', $selectedRdzv)->whereNotIn('id', [16, 17])->first();
            if ($region) {
                $vokzalsOfRdzv = $region->stations()->pluck('name')->toArray();
                if ($selectedVokzal === 'ou_rdzv') {
                    $query->where('users.workLocation', $selectedRdzv);
                } elseif ($selectedVokzal !== '' && $selectedVokzal !== 'all') {
                    $query->where('users.workLocation', $selectedVokzal);
                } else {
                    $query->where(function ($q) use ($selectedRdzv, $vokzalsOfRdzv) {
                        $q->where('users.workLocation', $selectedRdzv);
                        if (!empty($vokzalsOfRdzv)) {
                            $q->orWhereIn('users.workLocation', $vokzalsOfRdzv);
                        }
                    });
                }
            }
        }
        return $query;
    }
public function dashboard(Request $request)
{
    $selectedRdzv = $request->input('rdzv', 'all');
    $selectedVokzal = $request->input('vokzal', '');

    // 1. Получаем отфильтрованных сотрудников
    $query = Worker::join('users', 'users.id', '=', 'workers.tabelNumber')
        ->select('workers.*', 'users.workLocation');
    
    $workers = $this->applyFilters($query, $request)->get();

    // Статистика
    $totalWorkers = $workers->count();
    $vaccinatedCount = $workers->where('vakcina', 1)->count();
    $totalVaccinatedPercent = $totalWorkers > 0 ? round(($vaccinatedCount / $totalWorkers) * 100, 1) : 0;

    // Справочники
    $stationsMap = Station::whereHas('region', function($q) { $q->whereNotIn('id', [16, 17]); })
        ->with('region')->get()->mapWithKeys(fn($s) => [$s->name => $s->region->name]);
    $regionsNames = Region::whereNotIn('id', [16, 17])->pluck('name')->toArray();

    // 2. Формируем данные для таблицы (оставляем как было)
    $tableData = [];
    foreach ($workers as $worker) {
        $location = $worker->workLocation;
        $category = $worker->statusVokzal ?? 'остальные';
        $rdzvName = ($location === 'ДЖВ') ? 'ОУ ДЖВ' : (in_array($location, $regionsNames) ? $location : ($stationsMap[$location] ?? 'ОУ ДЖВ'));

        if (!isset($tableData[$rdzvName])) {
            $tableData[$rdzvName] = ['rdzv' => $rdzvName, 'categories' => [], 'total_workers' => 0, 'total_vaccinated' => 0];
        }
        if (!isset($tableData[$rdzvName]['categories'][$category])) {
            $tableData[$rdzvName]['categories'][$category] = ['category' => $category, 'total' => 0, 'vaccinated' => 0];
        }
        $tableData[$rdzvName]['total_workers']++;
        $tableData[$rdzvName]['categories'][$category]['total']++;
        if ($worker->vakcina == 1) {
            $tableData[$rdzvName]['total_vaccinated']++;
            $tableData[$rdzvName]['categories'][$category]['vaccinated']++;
        }
    }

    // Расчет процентов для таблицы
    foreach ($tableData as &$rdzv) {
        foreach ($rdzv['categories'] as &$cat) {
            $cat['vaccinated_percent'] = $cat['total'] > 0 ? round(($cat['vaccinated'] / $cat['total']) * 100, 1) : 0;
            $cat['target_level'] = round($cat['vaccinated_percent'] / 75, 2);
        }
        $rdzv['vaccinated_percent'] = $rdzv['total_workers'] > 0 ? round(($rdzv['total_vaccinated'] / $rdzv['total_workers']) * 100, 1) : 0;
        $rdzv['target_level'] = round($rdzv['vaccinated_percent'] / 75, 2);
    }
    unset($rdzv);

    // 3. ЛОГИКА ГРАФИКА (ДРИЛДАУН)
    $chartData = [];

    if ($selectedRdzv === 'all') {
        // Уровень 1: По всем РДЖВ
        $chartData = collect($tableData)->map(fn($r) => [
            'label' => $r['rdzv'], 
            'value' => $r['vaccinated_percent'],
            'count' => $r['total_workers']
        ])->values();
    } 
    elseif ($selectedRdzv !== 'ou_dzhv' && ($selectedVokzal === 'all' || $selectedVokzal === '')) {
        // Уровень 2: По вокзалам конкретной РДЖВ
        $tempChart = [];
        foreach ($workers as $worker) {
            $loc = $worker->workLocation;
            // "Аппарат" региона называем красиво
            $label = ($loc === $selectedRdzv) ? "Аппарат $loc" : $loc;

            if (!isset($tempChart[$label])) {
                $tempChart[$label] = ['total' => 0, 'vacc' => 0];
            }
            $tempChart[$label]['total']++;
            if ($worker->vakcina) $tempChart[$label]['vacc']++;
        }

        foreach ($tempChart as $label => $data) {
            $chartData[] = [
                'label' => $label,
                'value' => round(($data['vacc'] / $data['total']) * 100, 1),
                'count' => $data['total']
            ];
        }
    }
    // Если выбран конкретный вокзал или ОУ ДЖВ — chartData останется пустым

    $rdzvList = Region::whereNotIn('id', [16, 17])->orderBy('name')->pluck('name');

    return view('worker.dashboard', compact(
        'totalWorkers', 'vaccinatedCount', 'chartData', 
        'totalVaccinatedPercent', 'tableData', 'rdzvList', 
        'selectedRdzv', 'selectedVokzal'
    ));
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

    public function table(Request $request)
    {
        $rdzvList = Region::whereNotIn('id', [16, 17])->orderBy('name')->pluck('name');
        $query = Worker::join('users', 'users.id', '=', 'workers.tabelNumber')->select(['workers.*', 'users.workLocation']);
        $workers = $this->applyFilters($query, $request)->paginate(50)->appends($request->query());

        if ($request->ajax()) return view('worker.table_body', compact('workers'));
        return view('worker.table', compact('workers', 'rdzvList'));
    }

    public function analytics()
    {
        $statusSiteStats = User::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
        $statusVokzalStats = Worker::selectRaw('statusVokzal, COUNT(*) as count')->groupBy('statusVokzal')->get();
        return view('worker.analytics', compact('statusSiteStats', 'statusVokzalStats'));
    }

    public function getVokzals(Request $request)
    {
        $region = Region::where('name', $request->rdzv)->whereNotIn('id', [16, 17])->first();
        return response()->json($region ? $region->stations()->pluck('name') : []);
    }
}