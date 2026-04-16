<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VaccinationExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $tableData;
    protected $totalWorkers;
    protected $vaccinatedCount;
    protected $totalVaccinatedPercent;

    public function __construct($tableData, $totalWorkers, $vaccinatedCount, $totalVaccinatedPercent)
    {
        $this->tableData = $tableData;
        $this->totalWorkers = $totalWorkers;
        $this->vaccinatedCount = $vaccinatedCount;
        $this->totalVaccinatedPercent = $totalVaccinatedPercent;
    }

    /**
     * Формируем данные для экспорта
     */
    public function collection()
    {
        $rows = collect();

        // 1. ИТОГО - категория 1
        $itogo_cat1_total = 0;
        $itogo_cat1_vacc = 0;
        foreach ($this->tableData as $rdzvData) {
            if (isset($rdzvData['categories']['кадры массовых профессий'])) {
                $itogo_cat1_total += $rdzvData['categories']['кадры массовых профессий']['total'];
                $itogo_cat1_vacc += $rdzvData['categories']['кадры массовых профессий']['vaccinated'];
            }
        }
        $itogo_cat1_percent = $itogo_cat1_total > 0 ? round(($itogo_cat1_vacc / $itogo_cat1_total) * 100, 1) : 0;
        $itogo_cat1_level = round($itogo_cat1_percent / 75, 2);

        $rows->push([
            '№ п/п' => '—',
            'Наименование РДЖВ' => 'ИТОГО',
            'Категория персонала' => 'кадры массовых профессий',
            'Численность работников (чел.)' => $itogo_cat1_total,
            'Прошли вакцинацию (чел.)' => $itogo_cat1_vacc,
            '% вакцинированных' => $itogo_cat1_percent,
            'Уровень достижения целевого значения 75%' => $itogo_cat1_level
        ]);

        // 2. ИТОГО - категория 2
        $itogo_cat2_total = 0;
        $itogo_cat2_vacc = 0;
        foreach ($this->tableData as $rdzvData) {
            if (isset($rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров'])) {
                $itogo_cat2_total += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['total'];
                $itogo_cat2_vacc += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['vaccinated'];
            }
        }
        $itogo_cat2_percent = $itogo_cat2_total > 0 ? round(($itogo_cat2_vacc / $itogo_cat2_total) * 100, 1) : 0;
        $itogo_cat2_level = round($itogo_cat2_percent / 75, 2);

        $rows->push([
            '№ п/п' => '',
            'Наименование РДЖВ' => '',
            'Категория персонала' => 'работники, непосредственно связанные с обслуживанием пассажиров',
            'Численность работников (чел.)' => $itogo_cat2_total,
            'Прошли вакцинацию (чел.)' => $itogo_cat2_vacc,
            '% вакцинированных' => $itogo_cat2_percent,
            'Уровень достижения целевого значения 75%' => $itogo_cat2_level
        ]);

        // 3. ИТОГО - категория 3
        $itogo_cat3_total = 0;
        $itogo_cat3_vacc = 0;
        foreach ($this->tableData as $rdzvData) {
            if (isset($rdzvData['categories']['остальные'])) {
                $itogo_cat3_total += $rdzvData['categories']['остальные']['total'];
                $itogo_cat3_vacc += $rdzvData['categories']['остальные']['vaccinated'];
            }
        }
        $itogo_cat3_percent = $itogo_cat3_total > 0 ? round(($itogo_cat3_vacc / $itogo_cat3_total) * 100, 1) : 0;
        $itogo_cat3_level = round($itogo_cat3_percent / 75, 2);

        $rows->push([
            '№ п/п' => '',
            'Наименование РДЖВ' => '',
            'Категория персонала' => 'остальные',
            'Численность работников (чел.)' => $itogo_cat3_total,
            'Прошли вакцинацию (чел.)' => $itogo_cat3_vacc,
            '% вакцинированных' => $itogo_cat3_percent,
            'Уровень достижения целевого значения 75%' => $itogo_cat3_level
        ]);

        // 4. ИТОГО - ВСЕГО
        $totalTargetLevel = round($this->totalVaccinatedPercent / 75, 2);
        $rows->push([
            '№ п/п' => '',
            'Наименование РДЖВ' => '',
            'Категория персонала' => 'ВСЕГО',
            'Численность работников (чел.)' => $this->totalWorkers,
            'Прошли вакцинацию (чел.)' => $this->vaccinatedCount,
            '% вакцинированных' => $this->totalVaccinatedPercent,
            'Уровень достижения целевого значения 75%' => $totalTargetLevel
        ]);

        // 5. Данные по каждой РДЖВ
        $rowNumber = 1;
        foreach ($this->tableData as $rdzvData) {
            $categories = [
                'кадры массовых профессий',
                'работники, непосредственно связанные с обслуживанием пассажиров',
                'остальные'
            ];

            foreach ($categories as $catIndex => $categoryName) {
                if (isset($rdzvData['categories'][$categoryName])) {
                    $catData = $rdzvData['categories'][$categoryName];
                    
                    $rows->push([
                        '№ п/п' => $catIndex === 0 ? $rowNumber : '',
                        'Наименование РДЖВ' => $catIndex === 0 ? $rdzvData['rdzv'] : '',
                        'Категория персонала' => $categoryName,
                        'Численность работников (чел.)' => $catData['total'],
                        'Прошли вакцинацию (чел.)' => $catData['vaccinated'],
                        '% вакцинированных' => $catData['vaccinated_percent'],
                        'Уровень достижения целевого значения 75%' => $catData['target_level']
                    ]);
                } else {
                    $rows->push([
                        '№ п/п' => $catIndex === 0 ? $rowNumber : '',
                        'Наименование РДЖВ' => $catIndex === 0 ? $rdzvData['rdzv'] : '',
                        'Категория персонала' => $categoryName,
                        'Численность работников (чел.)' => 0,
                        'Прошли вакцинацию (чел.)' => 0,
                        '% вакцинированных' => 0,
                        'Уровень достижения целевого значения 75%' => 0
                    ]);
                }
            }

            // ВСЕГО по РДЖВ
            $rows->push([
                '№ п/п' => '',
                'Наименование РДЖВ' => '',
                'Категория персонала' => 'ВСЕГО',
                'Численность работников (чел.)' => $rdzvData['total_workers'],
                'Прошли вакцинацию (чел.)' => $rdzvData['total_vaccinated'],
                '% вакцинированных' => $rdzvData['vaccinated_percent'],
                'Уровень достижения целевого значения 75%' => $rdzvData['target_level']
            ]);

            $rowNumber++;
        }

        return $rows;
    }

    /**
     * Заголовки таблицы
     */
    public function headings(): array
    {
        return [
            '№ п/п',
            'Наименование РДЖВ',
            'Категория персонала',
            'Численность работников (чел.)',
            'Прошли вакцинацию (чел.)',
            '% вакцинированных',
            'Уровень достижения целевого значения 75%'
        ];
    }

    /**
     * Ширина столбцов
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // № п/п
            'B' => 25,  // Наименование РДЖВ
            'C' => 50,  // Категория персонала
            'D' => 20,  // Численность
            'E' => 20,  // Прошли вакцинацию
            'F' => 15,  // %
            'G' => 25,  // Уровень
        ];
    }

    /**
     * Стили для всей таблицы
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Стиль для заголовков
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ],
        ];
    }

    /**
     * События после создания листа
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Получаем последнюю строку
                $lastRow = $sheet->getHighestRow();
                
                // Применяем границы ко всем ячейкам
                $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);
                
                // Выравнивание текста по левому краю для столбцов B и C
                $sheet->getStyle('B2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Выделяем строки ИТОГО жирным
                $sheet->getStyle('A2:G5')->getFont()->setBold(true);
                $sheet->getStyle('A2:G5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E8F4F8');
                
                // Выделяем строки "ВСЕГО" жирным
                for ($row = 2; $row <= $lastRow; $row++) {
                    $categoryValue = $sheet->getCell('C' . $row)->getValue();
                    if ($categoryValue === 'ВСЕГО') {
                        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
                        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F0F0F0');
                    }
                }
                
                // Перенос текста в заголовках
                $sheet->getStyle('A1:G1')->getAlignment()->setWrapText(true);
                $sheet->getRowDimension(1)->setRowHeight(40);
            }
        ];
    }
}