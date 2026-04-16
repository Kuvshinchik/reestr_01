<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WinterWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * =====================================================================
 * КОНТРОЛЛЕР ДИАГРАММЫ И ТАБЛИЦЫ ПЕРВОЗИМНИКОВ
 * =====================================================================
 * 
 * Методы:
 * 1. index()     - страница с диаграммой и таблицей
 * 2. chartData() - AJAX данные для диаграммы (итоги)
 * 3. tableData() - AJAX данные для таблицы (по месяцам)
 * 4. export()    - экспорт таблицы в Excel
 */
class WinterWorkerChartController extends Controller
{
    /**
     * Названия месяцев на русском
     */
    private array $monthNames = [
        1 => 'январь', 2 => 'февраль', 3 => 'март',
        4 => 'апрель', 5 => 'май', 6 => 'июнь',
        7 => 'июль', 8 => 'август', 9 => 'сентябрь',
        10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь'
    ];

    /**
     * -----------------------------------------------------------------
     * Страница с диаграммой и таблицей
     * -----------------------------------------------------------------
     */
    public function index()
    {
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();
        
        // Генерируем список месяцев (последние 12 месяцев)
        $months = $this->getLast12Months();
        
        return view('winter-worker-chart.index', compact('regions', 'months'));
    }

    /**
     * -----------------------------------------------------------------
     * AJAX: Данные для диаграммы (итоговые значения)
     * -----------------------------------------------------------------
     */
    public function chartData(Request $request)
    {
        $regionId = $request->input('region_id', 'all');

        if ($regionId === 'all') {
            $data = $this->getChartDataByRegions();
            $label = 'ДЖВ (все РДЖВ)';
        } else {
            $data = $this->getChartDataByStations($regionId);
            $region = Region::find($regionId);
            $label = $region ? $region->name : 'РДЖВ';
        }

        return response()->json([
            'data' => $data,
            'label' => $label
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * AJAX: Данные для таблицы (по месяцам)
     * -----------------------------------------------------------------
     */
    public function tableData(Request $request)
    {
        $regionId = $request->input('region_id', 'all');
        $months = $this->getLast12Months();

        if ($regionId === 'all') {
            $data = $this->getTableDataByRegions($months);
            $label = 'ДЖВ (все РДЖВ)';
            $columnName = 'РДЖВ';
        } else {
            $data = $this->getTableDataByStations($regionId, $months);
            $region = Region::find($regionId);
            $label = $region ? $region->name : 'РДЖВ';
            $columnName = 'Вокзал';
        }

        return response()->json([
            'data' => $data,
            'months' => $months,
            'label' => $label,
            'columnName' => $columnName
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * Экспорт в Excel
     * -----------------------------------------------------------------
     */
    public function export(Request $request)
    {
        $regionId = $request->input('region_id', 'all');
        $months = $this->getLast12Months();

        if ($regionId === 'all') {
            $data = $this->getTableDataByRegions($months);
            $title = 'ДЖВ (все РДЖВ)';
            $columnName = 'РДЖВ';
        } else {
            $data = $this->getTableDataByStations($regionId, $months);
            $region = Region::find($regionId);
            $title = $region ? $region->name : 'РДЖВ';
            $columnName = 'Вокзал';
        }

        // Создаём Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Первозимники');

        // Заголовок
        $sheet->setCellValue('A1', 'Первозимники: ' . $title);
        $lastCol = 2 + (count($months) * 2) + 2; // № + Название + месяцы*2 + итоги
        $sheet->mergeCells('A1:' . $this->getColLetter($lastCol) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Шапка таблицы (строка 3)
        $headerRow = 3;
        $col = 1;
        
        $sheet->setCellValue($this->getColLetter($col++) . $headerRow, '№ п/п');
        $sheet->setCellValue($this->getColLetter($col++) . $headerRow, $columnName);
        
        foreach ($months as $month) {
            $monthName = $this->monthNames[$month['month']] . ' ' . $month['year'];
            $sheet->setCellValue($this->getColLetter($col++) . $headerRow, 'принято ' . $monthName);
            $sheet->setCellValue($this->getColLetter($col++) . $headerRow, 'обучено ' . $monthName);
        }
        
        $sheet->setCellValue($this->getColLetter($col++) . $headerRow, 'всего принято');
        $sheet->setCellValue($this->getColLetter($col++) . $headerRow, 'всего обучено');

        // Стиль шапки
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $headerRow . ':' . $this->getColLetter($col - 1) . $headerRow)->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(40);

        // Данные
        $dataStartRow = 4;
        $currentRow = $dataStartRow;
        
        foreach ($data as $index => $row) {
            $col = 1;
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $index + 1);
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $row['label']);
            
            foreach ($months as $month) {
                $key = $month['year'] . '-' . str_pad($month['month'], 2, '0', STR_PAD_LEFT);
                $monthData = $row['months'][$key] ?? ['hired' => 0, 'trained' => 0];
                $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $monthData['hired']);
                $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $monthData['trained']);
            }
            
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $row['total_hired']);
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $row['total_trained']);
            
            $currentRow++;
        }

        // Строка ИТОГО
        $totalRow = $currentRow;
        $col = 1;
        $sheet->setCellValue($this->getColLetter($col++) . $totalRow, '');
        $sheet->setCellValue($this->getColLetter($col++) . $totalRow, 'Итого');
        
        $colNum = 3;
        for ($i = 0; $i < count($months) * 2 + 2; $i++) {
            $colLetter = $this->getColLetter($colNum);
            $sheet->setCellValue($colLetter . $totalRow, 
                '=SUM(' . $colLetter . $dataStartRow . ':' . $colLetter . ($totalRow - 1) . ')');
            $colNum++;
        }

        // Стиль данных
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];
        $sheet->getStyle('A' . $dataStartRow . ':' . $this->getColLetter($col - 1) . $totalRow)->applyFromArray($dataStyle);

        // Стиль итого
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
        ];
        $sheet->getStyle('A' . $totalRow . ':' . $this->getColLetter($col - 1) . $totalRow)->applyFromArray($totalStyle);

        // Ширина столбцов
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        for ($i = 3; $i <= $col; $i++) {
            $sheet->getColumnDimension($this->getColLetter($i))->setWidth(12);
        }

        // Отдаём файл
        $filename = 'pervozimniki_' . date('Y-m-d_H-i-s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * -----------------------------------------------------------------
     * ПРИВАТНЫЕ МЕТОДЫ
     * -----------------------------------------------------------------
     */

    /**
     * Получить последние 12 месяцев
     */
    private function getLast12Months(): array
    {
        $months = [];
        $date = now();
        
        for ($i = 0; $i < 12; $i++) {
            $months[] = [
                'year' => (int) $date->format('Y'),
                'month' => (int) $date->format('m'),
                'label' => $this->monthNames[(int) $date->format('m')] . ' ' . $date->format('Y')
            ];
            $date->subMonth();
        }
        
        return array_reverse($months);
    }

    /**
     * Данные для диаграммы по РДЖВ
     */
    private function getChartDataByRegions(): array
    {
        $results = DB::table('regions')
            ->leftJoin('stations', 'stations.region_id', '=', 'regions.id')
            ->leftJoin('winter_workers', 'winter_workers.station_id', '=', 'stations.id')
            ->whereNotIn('regions.id', [16, 17])
            ->groupBy('regions.id', 'regions.name')
            ->select(
                'regions.name as label',
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->orderBy('regions.name')
            ->get();

        return $results->map(fn($item) => [
            'label' => $item->label,
            'hired' => (int) $item->hired,
            'trained' => (int) $item->trained
        ])->toArray();
    }

    /**
     * Данные для диаграммы по вокзалам
     */
    private function getChartDataByStations(int $regionId): array
    {
        $results = DB::table('stations')
            ->leftJoin('winter_workers', 'winter_workers.station_id', '=', 'stations.id')
            ->where('stations.region_id', $regionId)
            ->groupBy('stations.id', 'stations.name')
            ->select(
                'stations.name as label',
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->orderBy('stations.name')
            ->get();

        return $results->map(fn($item) => [
            'label' => $item->label,
            'hired' => (int) $item->hired,
            'trained' => (int) $item->trained
        ])->toArray();
    }

    /**
     * Данные для таблицы по РДЖВ (с разбивкой по месяцам)
     */
    private function getTableDataByRegions(array $months): array
    {
        // Получаем все регионы
        $regions = Region::whereNotIn('id', [16, 17])->orderBy('name')->get();
        
        // Получаем данные по месяцам
        $monthlyData = DB::table('winter_workers')
            ->join('stations', 'stations.id', '=', 'winter_workers.station_id')
            ->join('regions', 'regions.id', '=', 'stations.region_id')
            ->whereNotIn('regions.id', [16, 17])
            ->select(
                'regions.id as region_id',
                DB::raw('YEAR(winter_workers.hired_at) as year'),
                DB::raw('MONTH(winter_workers.hired_at) as month'),
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->groupBy('regions.id', DB::raw('YEAR(winter_workers.hired_at)'), DB::raw('MONTH(winter_workers.hired_at)'))
            ->get()
            ->groupBy('region_id');

        // Собираем результат
        $result = [];
        foreach ($regions as $region) {
            $row = [
                'id' => $region->id,
                'label' => $region->name,
                'months' => [],
                'total_hired' => 0,
                'total_trained' => 0
            ];

            $regionData = $monthlyData->get($region->id, collect());
            
            foreach ($months as $month) {
                $key = $month['year'] . '-' . str_pad($month['month'], 2, '0', STR_PAD_LEFT);
                $monthRecord = $regionData->first(fn($r) => $r->year == $month['year'] && $r->month == $month['month']);
                
                $hired = $monthRecord ? (int) $monthRecord->hired : 0;
                $trained = $monthRecord ? (int) $monthRecord->trained : 0;
                
                $row['months'][$key] = ['hired' => $hired, 'trained' => $trained];
                $row['total_hired'] += $hired;
                $row['total_trained'] += $trained;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Данные для таблицы по вокзалам (с разбивкой по месяцам)
     */
    private function getTableDataByStations(int $regionId, array $months): array
    {
        // Получаем все вокзалы региона
        $stations = Station::where('region_id', $regionId)->orderBy('name')->get();
        
        // Получаем данные по месяцам
        $monthlyData = DB::table('winter_workers')
            ->join('stations', 'stations.id', '=', 'winter_workers.station_id')
            ->where('stations.region_id', $regionId)
            ->select(
                'stations.id as station_id',
                DB::raw('YEAR(winter_workers.hired_at) as year'),
                DB::raw('MONTH(winter_workers.hired_at) as month'),
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->groupBy('stations.id', DB::raw('YEAR(winter_workers.hired_at)'), DB::raw('MONTH(winter_workers.hired_at)'))
            ->get()
            ->groupBy('station_id');

        // Собираем результат
        $result = [];
        foreach ($stations as $station) {
            $row = [
                'id' => $station->id,
                'label' => $station->name,
                'months' => [],
                'total_hired' => 0,
                'total_trained' => 0
            ];

            $stationData = $monthlyData->get($station->id, collect());
            
            foreach ($months as $month) {
                $key = $month['year'] . '-' . str_pad($month['month'], 2, '0', STR_PAD_LEFT);
                $monthRecord = $stationData->first(fn($r) => $r->year == $month['year'] && $r->month == $month['month']);
                
                $hired = $monthRecord ? (int) $monthRecord->hired : 0;
                $trained = $monthRecord ? (int) $monthRecord->trained : 0;
                
                $row['months'][$key] = ['hired' => $hired, 'trained' => $trained];
                $row['total_hired'] += $hired;
                $row['total_trained'] += $trained;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Получить букву столбца Excel по номеру
     */
    private function getColLetter(int $num): string
    {
        $letter = '';
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $num = (int)(($num - $mod) / 26);
        }
        return $letter;
    }
}
