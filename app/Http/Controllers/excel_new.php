<?php

    public function exportVaccination(Request $request)
    {
        $query = Worker::join('users', 'users.id', '=', 'workers.tabelNumber')->select('workers.*', 'users.workLocation');
        $workers = $this->applyFilters($query, $request)->get();

        // Справочники
        $stationsMap = Station::whereHas('region', function($q) { $q->whereNotIn('id', [16, 17]); })->with('region')->get()->mapWithKeys(fn($s) => [$s->name => $s->region->name]);
        $regionsNames = Region::whereNotIn('id', [16, 17])->pluck('name')->toArray();

        $tableData = [];
        foreach ($workers as $worker) {
            $location = $worker->workLocation;
            $rdzvName = ($location === 'ДЖВ') ? 'ОУ ДЖВ' : (in_array($location, $regionsNames) ? $location : ($stationsMap[$location] ?? 'ОУ ДЖВ'));
            $category = $worker->statusVokzal ?? 'остальные';

            if (!isset($tableData[$rdzvName])) $tableData[$rdzvName] = ['total' => 0, 'vacc' => 0, 'cats' => []];
            if (!isset($tableData[$rdzvName]['cats'][$category])) $tableData[$rdzvName]['cats'][$category] = ['total' => 0, 'vacc' => 0];
            
            $tableData[$rdzvName]['total']++;
            $tableData[$rdzvName]['cats'][$category]['total']++;
            if ($worker->vakcina) { $tableData[$rdzvName]['vacc']++; $tableData[$rdzvName]['cats'][$category]['vacc']++; }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Регион')->setCellValue('B1', 'Категория')->setCellValue('C1', 'Всего')->setCellValue('D1', 'Вакцинировано')->setCellValue('E1', '%');
        
        $row = 2;
        foreach ($tableData as $name => $data) {
            foreach ($data['cats'] as $catName => $cat) {
                $perc = $cat['total'] > 0 ? round(($cat['vacc'] / $cat['total']) * 100, 1) : 0;
                $sheet->setCellValue('A'.$row, $name)->setCellValue('B'.$row, $catName)->setCellValue('C'.$row, $cat['total'])->setCellValue('D'.$row, $cat['vacc'])->setCellValue('E'.$row, $perc.'%');
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Vaccination_Report.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        $writer->save('php://output');
        exit;
    }