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
 * КОНТРОЛЛЕР ТАБЛИЦЫ ПЕРВОЗИМНИКОВ
 * =====================================================================
 * 
 * Что делает этот контроллер:
 * 1. index()      - показывает страницу с таблицей
 * 2. tableData()  - возвращает данные для таблицы (AJAX)
 * 3. export()     - экспортирует данные в Excel
 * 
 * Фильтры:
 * - region_id: ДЖВ (все РДЖВ) или конкретный РДЖВ (вокзалы)
 * - date_from, date_to: период для фильтрации по дате приёма (hired_at)
 */
class WinterWorkerTableController extends Controller
{
    /**
     * -----------------------------------------------------------------
     * МЕТОД 1: Отображение страницы
     * -----------------------------------------------------------------
     */
    public function index()
    {
        // Получаем регионы для Select (без ДЖВ и КЛНГ)
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();
        
        return view('winter-worker-table.index', compact('regions'));
    }

    /**
     * -----------------------------------------------------------------
     * МЕТОД 2: Получение данных для таблицы (AJAX)
     * -----------------------------------------------------------------
     * 
     * @param Request $request - содержит region_id, date_from, date_to
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableData(Request $request)
    {
        $regionId = $request->input('region_id', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($regionId === 'all') {
            // Данные по всем РДЖВ
            $data = $this->getDataByRegions($dateFrom, $dateTo);
            $label = 'ДЖВ (все РДЖВ)';
            $columnName = 'РДЖВ';
        } else {
            // Данные по вокзалам конкретного РДЖВ
            $data = $this->getDataByStations($regionId, $dateFrom, $dateTo);
            $region = Region::find($regionId);
            $label = $region ? $region->name : 'РДЖВ';
            $columnName = 'Вокзал';
        }

        // Считаем итоги
        $totals = [
            'hired' => array_sum(array_column($data, 'hired')),
            'trained' => array_sum(array_column($data, 'trained'))
        ];

        return response()->json([
            'data' => $data,
            'label' => $label,
            'columnName' => $columnName,
            'totals' => $totals
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * МЕТОД 3: Экспорт в Excel
     * -----------------------------------------------------------------
     * 
     * Использует библиотеку PhpSpreadsheet (phpoffice/phpspreadsheet)
     * Она уже установлена в проекте (см. composer.json)
     */
    public function export(Request $request)
    {
        $regionId = $request->input('region_id', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Получаем данные
        if ($regionId === 'all') {
            $data = $this->getDataByRegions($dateFrom, $dateTo);
            $title = 'ДЖВ (все РДЖВ)';
            $columnName = 'РДЖВ';
        } else {
            $data = $this->getDataByStations($regionId, $dateFrom, $dateTo);
            $region = Region::find($regionId);
            $title = $region ? $region->name : 'РДЖВ';
            $columnName = 'Вокзал';
        }

        // =========================================================
        // СОЗДАНИЕ EXCEL-ФАЙЛА
        // =========================================================
        
        // Создаём новую книгу Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Первозимники');

        // ---------------------------------------------------------
        // ЗАГОЛОВОК ОТЧЁТА (строка 1)
        // ---------------------------------------------------------
        $sheet->setCellValue('A1', 'Отчёт по первозимникам: ' . $title);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---------------------------------------------------------
        // ПЕРИОД (строка 2)
        // ---------------------------------------------------------
        $periodText = 'Период: ';
        if ($dateFrom && $dateTo) {
            $periodText .= $dateFrom . ' - ' . $dateTo;
        } elseif ($dateFrom) {
            $periodText .= 'с ' . $dateFrom;
        } elseif ($dateTo) {
            $periodText .= 'по ' . $dateTo;
        } else {
            $periodText .= 'Все данные';
        }
        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---------------------------------------------------------
        // ШАПКА ТАБЛИЦЫ (строка 4)
        // ---------------------------------------------------------
        $headerRow = 4;
        $sheet->setCellValue('A' . $headerRow, '№ п/п');
        $sheet->setCellValue('B' . $headerRow, $columnName);
        $sheet->setCellValue('C' . $headerRow, 'Принято');
        $sheet->setCellValue('D' . $headerRow, 'Обучено');

        // Стиль шапки: жирный шрифт, заливка, границы
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']  // Синий
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']  // Белый текст
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];
        $sheet->getStyle('A' . $headerRow . ':D' . $headerRow)->applyFromArray($headerStyle);

        // ---------------------------------------------------------
        // ДАННЫЕ (начиная со строки 5)
        // ---------------------------------------------------------
        $dataStartRow = 5;
        $currentRow = $dataStartRow;
        
        foreach ($data as $index => $row) {
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $row['label']);
            $sheet->setCellValue('C' . $currentRow, $row['hired']);
            $sheet->setCellValue('D' . $currentRow, $row['trained']);
            $currentRow++;
        }

        // Стиль данных: границы
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A' . $dataStartRow . ':D' . ($currentRow - 1))->applyFromArray($dataStyle);

        // Выравнивание чисел по центру
        $sheet->getStyle('A' . $dataStartRow . ':A' . ($currentRow - 1))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $dataStartRow . ':D' . ($currentRow - 1))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---------------------------------------------------------
        // СТРОКА ИТОГО
        // ---------------------------------------------------------
        $totalRow = $currentRow;
        $sheet->setCellValue('A' . $totalRow, '');
        $sheet->setCellValue('B' . $totalRow, 'ИТОГО');
        $sheet->setCellValue('C' . $totalRow, '=SUM(C' . $dataStartRow . ':C' . ($totalRow - 1) . ')');
        $sheet->setCellValue('D' . $totalRow, '=SUM(D' . $dataStartRow . ':D' . ($totalRow - 1) . ')');

        // Стиль итого: жирный, заливка
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA']  // Светло-зелёный
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet->getStyle('A' . $totalRow . ':D' . $totalRow)->applyFromArray($totalStyle);
        $sheet->getStyle('B' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ---------------------------------------------------------
        // АВТОШИРИНА СТОЛБЦОВ
        // ---------------------------------------------------------
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // =========================================================
        // ОТДАЧА ФАЙЛА НА СКАЧИВАНИЕ
        // =========================================================
        
        // Формируем имя файла
        $filename = 'pervozimniki_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Устанавливаем заголовки для скачивания
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Создаём writer и отдаём файл
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * -----------------------------------------------------------------
     * ПРИВАТНЫЙ МЕТОД: Данные по РДЖВ
     * -----------------------------------------------------------------
     */
    private function getDataByRegions(?string $dateFrom, ?string $dateTo): array
    {
        $query = DB::table('regions')
            ->leftJoin('stations', 'stations.region_id', '=', 'regions.id')
            ->leftJoin('winter_workers', function ($join) use ($dateFrom, $dateTo) {
                $join->on('winter_workers.station_id', '=', 'stations.id');
                
                // Фильтр по периоду
                if ($dateFrom) {
                    $join->where('winter_workers.hired_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $join->where('winter_workers.hired_at', '<=', $dateTo);
                }
            })
            ->whereNotIn('regions.id', [16, 17])
            ->groupBy('regions.id', 'regions.name')
            ->select(
                'regions.name as label',
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->orderBy('regions.name');

        return $query->get()->map(function ($item) {
            return [
                'label' => $item->label,
                'hired' => (int) $item->hired,
                'trained' => (int) $item->trained
            ];
        })->toArray();
    }

    /**
     * -----------------------------------------------------------------
     * ПРИВАТНЫЙ МЕТОД: Данные по вокзалам РДЖВ
     * -----------------------------------------------------------------
     */
    private function getDataByStations(int $regionId, ?string $dateFrom, ?string $dateTo): array
    {
        $query = DB::table('stations')
            ->leftJoin('winter_workers', function ($join) use ($dateFrom, $dateTo) {
                $join->on('winter_workers.station_id', '=', 'stations.id');
                
                if ($dateFrom) {
                    $join->where('winter_workers.hired_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $join->where('winter_workers.hired_at', '<=', $dateTo);
                }
            })
            ->where('stations.region_id', $regionId)
            ->groupBy('stations.id', 'stations.name')
            ->select(
                'stations.name as label',
                DB::raw('COUNT(winter_workers.id) as hired'),
                DB::raw('SUM(CASE WHEN winter_workers.trained_at IS NOT NULL THEN 1 ELSE 0 END) as trained')
            )
            ->orderBy('stations.name');

        return $query->get()->map(function ($item) {
            return [
                'label' => $item->label,
                'hired' => (int) $item->hired,
                'trained' => (int) $item->trained
            ];
        })->toArray();
    }
}
