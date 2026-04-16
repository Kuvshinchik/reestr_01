<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Station;
use App\Models\WorkItem;
use App\Models\WinterPreparation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WinterTableController extends Controller
{
    /**
     * Главная страница с таблицей
     */
    public function index(Request $request)
    {
        // Получаем выбранный регион из URL (по умолчанию 'all' = ДЖВ)
        $selectedRegionId = $request->query('region_id', 'all');

        // Список регионов для Select (без КЛНГ id=17 и ДЖВ id=16)
        $regions = Region::whereNotIn('id', [16, 17])
            ->orderBy('name')
            ->get();

        // Получаем структуру работ (иерархия: заголовки -> подзаголовки -> работы)
        $workStructure = $this->getWorkStructure();

        // Получаем колонки (РДЖВ или вокзалы)
        $columns = $this->getColumns($selectedRegionId);

        // Получаем данные для таблицы
        $tableData = $this->getTableData($selectedRegionId, $workStructure);

        return view('winter-table.index', compact(
            'regions',
            'selectedRegionId',
            'workStructure',
            'columns',
            'tableData'
        ));
    }

    /**
     * Экспорт таблицы в Excel (используем PhpSpreadsheet напрямую)
     */
    public function export(Request $request)
    {
        $selectedRegionId = $request->query('region_id', 'all');

        // Получаем данные
        $workStructure = $this->getWorkStructure();
        $columns = $this->getColumns($selectedRegionId);
        $tableData = $this->getTableData($selectedRegionId, $workStructure);

        // Создаем новый Excel документ
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Устанавливаем название листа
        if ($selectedRegionId === 'all') {
            $sheet->setTitle('ДЖВ');
            $filename = 'Подготовка_к_зиме_ДЖВ_' . date('Y-m-d') . '.xlsx';
        } else {
            $region = Region::find($selectedRegionId);
            $regionName = $region ? $region->name : 'РДЖВ';
            $sheet->setTitle($regionName);
            $filename = 'Подготовка_к_зиме_' . $regionName . '_' . date('Y-m-d') . '.xlsx';
        }

        // === СТРОКА 1: Заголовки колонок ===
        $sheet->setCellValue('A1', 'Наименование работ');
        
        $colIndex = 2; // Начинаем с колонки B
        foreach ($columns as $column) {
            $colLetter = $this->getColumnLetter($colIndex);
            $nextColLetter = $this->getColumnLetter($colIndex + 1);
            
            // Объединяем ячейки для названия колонки
            $sheet->mergeCells("{$colLetter}1:{$nextColLetter}1");
            $sheet->setCellValue("{$colLetter}1", $column['name']);
            
            $colIndex += 2;
        }
        
        // ИТОГО
        $colLetter = $this->getColumnLetter($colIndex);
        $nextColLetter = $this->getColumnLetter($colIndex + 1);
        $sheet->mergeCells("{$colLetter}1:{$nextColLetter}1");
        $sheet->setCellValue("{$colLetter}1", 'ИТОГО');
        
        $lastColLetter = $nextColLetter;

        // === СТРОКА 2: План/Факт ===
        $sheet->mergeCells('A1:A2'); // Объединяем "Наименование работ"
        
        $colIndex = 2;
        foreach ($columns as $column) {
            $sheet->setCellValue($this->getColumnLetter($colIndex) . '2', 'План');
            $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . '2', 'Факт');
            $colIndex += 2;
        }
        $sheet->setCellValue($this->getColumnLetter($colIndex) . '2', 'План');
        $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . '2', 'Факт');

        // === ДАННЫЕ ===
        $rowIndex = 3;
        
        foreach ($workStructure as $header) {
            // --- Заголовок (level 1) ---
            $sheet->setCellValue("A{$rowIndex}", $header['item']->name);
            
            $colIndex = 2;
            $headerTotalPlan = 0;
            $headerTotalFact = 0;
            
            foreach ($columns as $column) {
                $colPlan = 0;
                $colFact = 0;
                
                foreach ($header['subsections'] as $subsection) {
                    foreach ($subsection['works'] as $work) {
                        if (isset($tableData[$work->id][$column['id']])) {
                            $colPlan += $tableData[$work->id][$column['id']]['plan'];
                            $colFact += $tableData[$work->id][$column['id']]['fact'];
                        }
                    }
                }
                
                $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $colPlan);
                $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $colFact);
                
                $headerTotalPlan += $colPlan;
                $headerTotalFact += $colFact;
                $colIndex += 2;
            }
            
            // Итого по заголовку
            $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $headerTotalPlan);
            $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $headerTotalFact);
            
            // Стиль для заголовка (синий фон)
            $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCE5FF'],
                ],
            ]);
            
            $rowIndex++;
            
            // --- Подзаголовки и работы ---
            foreach ($header['subsections'] as $subsection) {
                // Подзаголовок
                $sheet->setCellValue("A{$rowIndex}", '    ' . $subsection['item']->name);
                
                $colIndex = 2;
                $subTotalPlan = 0;
                $subTotalFact = 0;
                
                foreach ($columns as $column) {
                    $colPlan = 0;
                    $colFact = 0;
                    
                    foreach ($subsection['works'] as $work) {
                        if (isset($tableData[$work->id][$column['id']])) {
                            $colPlan += $tableData[$work->id][$column['id']]['plan'];
                            $colFact += $tableData[$work->id][$column['id']]['fact'];
                        }
                    }
                    
                    $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $colPlan);
                    $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $colFact);
                    
                    $subTotalPlan += $colPlan;
                    $subTotalFact += $colFact;
                    $colIndex += 2;
                }
                
                $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $subTotalPlan);
                $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $subTotalFact);
                
                // Стиль для подзаголовка (серый фон)
                $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                    'font' => ['italic' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E3E5'],
                    ],
                ]);
                
                $rowIndex++;
                
                // Работы (если не совпадают с подзаголовком)
                foreach ($subsection['works'] as $work) {
                    if ($work->id !== $subsection['item']->id) {
                        $sheet->setCellValue("A{$rowIndex}", '        ' . $work->name);
                        
                        $colIndex = 2;
                        $workTotalPlan = 0;
                        $workTotalFact = 0;
                        
                        foreach ($columns as $column) {
                            $plan = $tableData[$work->id][$column['id']]['plan'] ?? 0;
                            $fact = $tableData[$work->id][$column['id']]['fact'] ?? 0;
                            
                            $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $plan);
                            $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $fact);
                            
                            $workTotalPlan += $plan;
                            $workTotalFact += $fact;
                            $colIndex += 2;
                        }
                        
                        $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $workTotalPlan);
                        $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $workTotalFact);
                        
                        $rowIndex++;
                    }
                }
            }
        }

        // === ИТОГОВАЯ СТРОКА ===
        $sheet->setCellValue("A{$rowIndex}", 'ИТОГО ПО ВСЕМ РАБОТАМ');
        
        $colIndex = 2;
        $grandTotalPlan = 0;
        $grandTotalFact = 0;
        
        foreach ($columns as $column) {
            $colPlan = 0;
            $colFact = 0;
            
            foreach ($workStructure as $header) {
                foreach ($header['subsections'] as $subsection) {
                    foreach ($subsection['works'] as $work) {
                        if (isset($tableData[$work->id][$column['id']])) {
                            $colPlan += $tableData[$work->id][$column['id']]['plan'];
                            $colFact += $tableData[$work->id][$column['id']]['fact'];
                        }
                    }
                }
            }
            
            $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $colPlan);
            $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $colFact);
            
            $grandTotalPlan += $colPlan;
            $grandTotalFact += $colFact;
            $colIndex += 2;
        }
        
        $sheet->setCellValue($this->getColumnLetter($colIndex) . $rowIndex, $grandTotalPlan);
        $sheet->setCellValue($this->getColumnLetter($colIndex + 1) . $rowIndex, $grandTotalFact);
        
        // Стиль для итоговой строки (тёмный фон, белый текст)
        $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '343A40'],
            ],
        ]);

        // === ОБЩИЕ СТИЛИ ===
        
        // Шапка (строки 1-2)
        $sheet->getStyle("A1:{$lastColLetter}2")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Границы для всей таблицы
        $sheet->getStyle("A1:{$lastColLetter}{$rowIndex}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Ширина первой колонки
        $sheet->getColumnDimension('A')->setWidth(50);
        
        // Ширина остальных колонок
        for ($i = 2; $i <= $colIndex + 1; $i++) {
            $sheet->getColumnDimension($this->getColumnLetter($i))->setWidth(12);
        }
        
        // Выравнивание чисел по центру
        $sheet->getStyle("B3:{$lastColLetter}{$rowIndex}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === СКАЧИВАНИЕ ФАЙЛА ===
        
        // Устанавливаем заголовки для скачивания
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Создаем writer и выводим файл
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        
        exit;
    }

    /**
     * Получить букву колонки по индексу (1 = A, 2 = B, 27 = AA, ...)
     */
    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intval($index / 26);
        }
        return $letter;
    }

    /**
     * Получить иерархическую структуру работ
     */
    private function getWorkStructure(): array
    {
        $items = WorkItem::orderBy('id')->get();
        $byParent = $items->groupBy('parent_id');

        // Функция проверки - является ли элемент листом
        $isLeaf = function ($item) use ($byParent) {
            return !$byParent->has($item->id);
        };

        // Рекурсивно получить все листовые потомки
        $getLeafDescendants = function ($id) use (&$getLeafDescendants, $byParent, $isLeaf) {
            $children = $byParent->get($id, collect());
            if ($children->isEmpty()) {
                return collect();
            }

            $result = collect();
            foreach ($children as $child) {
                if ($isLeaf($child)) {
                    $result->push($child);
                } else {
                    $result = $result->merge($getLeafDescendants($child->id));
                }
            }
            return $result;
        };

        // Строим структуру
        $level1Items = $items->where('level', '1')->values();
        $structure = [];

        foreach ($level1Items as $l1) {
            $subsections = [];
            $childrenL1 = $byParent->get($l1->id, collect());

            foreach ($childrenL1 as $child) {
                if ($isLeaf($child)) {
                    $works = collect([$child]);
                } else {
                    $works = $getLeafDescendants($child->id);
                }

                if ($works->isEmpty()) {
                    continue;
                }

                $subsections[] = [
                    'item' => $child,
                    'works' => $works,
                ];
            }

            if (empty($subsections)) {
                continue;
            }

            $structure[] = [
                'item' => $l1,
                'subsections' => $subsections,
            ];
        }

        return $structure;
    }

    /**
     * Получить колонки таблицы (РДЖВ или вокзалы)
     */
    private function getColumns(string $regionId): array
    {
        if ($regionId === 'all') {
            return Region::whereNotIn('id', [16, 17])
                ->orderBy('name')
                ->get()
                ->map(function ($region) {
                    return [
                        'id' => $region->id,
                        'name' => $region->name,
                        'type' => 'region',
                    ];
                })
                ->toArray();
        } else {
            return Station::where('region_id', $regionId)
                ->orderBy('name')
                ->get()
                ->map(function ($station) {
                    return [
                        'id' => $station->id,
                        'name' => $station->name,
                        'type' => 'station',
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Получить данные для таблицы
     */
    private function getTableData(string $regionId, array $workStructure): array
    {
        // Собираем все ID листовых работ
        $workItemIds = [];
        foreach ($workStructure as $header) {
            foreach ($header['subsections'] as $subsection) {
                foreach ($subsection['works'] as $work) {
                    $workItemIds[] = $work->id;
                }
            }
        }

        if (empty($workItemIds)) {
            return [];
        }

        if ($regionId === 'all') {
            $data = $this->getDataGroupedByRegions($workItemIds);
        } else {
            $data = $this->getDataGroupedByStations((int)$regionId, $workItemIds);
        }

        return $data;
    }

    /**
     * Получить данные сгруппированные по регионам
     */
    private function getDataGroupedByRegions(array $workItemIds): array
    {
        $results = DB::table('winter_preparations as wp')
            ->select([
                'wp.work_item_id',
                'r.id as column_id',
                DB::raw('SUM(wp.plan) as plan'),
                DB::raw('SUM(wp.fact) as fact'),
            ])
            ->join('stations as s', 's.id', '=', 'wp.station_id')
            ->join('regions as r', 'r.id', '=', 's.region_id')
            ->whereIn('wp.work_item_id', $workItemIds)
            ->whereNotIn('r.id', [16, 17])
            ->groupBy('wp.work_item_id', 'r.id')
            ->get();

        return $this->formatTableData($results);
    }

    /**
     * Получить данные сгруппированные по вокзалам
     */
    private function getDataGroupedByStations(int $regionId, array $workItemIds): array
    {
        $results = DB::table('winter_preparations as wp')
            ->select([
                'wp.work_item_id',
                'wp.station_id as column_id',
                DB::raw('SUM(wp.plan) as plan'),
                DB::raw('SUM(wp.fact) as fact'),
            ])
            ->join('stations as s', 's.id', '=', 'wp.station_id')
            ->where('s.region_id', $regionId)
            ->whereIn('wp.work_item_id', $workItemIds)
            ->groupBy('wp.work_item_id', 'wp.station_id')
            ->get();

        return $this->formatTableData($results);
    }

    /**
     * Преобразовать результаты запроса в удобный формат
     */
    private function formatTableData($results): array
    {
        $data = [];
        foreach ($results as $row) {
            $workItemId = $row->work_item_id;
            $columnId = $row->column_id;

            if (!isset($data[$workItemId])) {
                $data[$workItemId] = [];
            }

            $data[$workItemId][$columnId] = [
                'plan' => (int)$row->plan,
                'fact' => (int)$row->fact,
            ];
        }
        return $data;
    }
}
