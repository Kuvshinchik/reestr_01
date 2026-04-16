{{-- 
    Файл: resources/views/winter-table/index.blade.php
    Страница с таблицей подготовки к зиме
--}}
@extends('layouts.admin')

@section('title', 'Подготовка к зиме - Таблица')

@section('content')
<div class="container-fluid mt-4">
    
    {{-- ===== ЗАГОЛОВОК СТРАНИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Подготовка к зиме: Таблица данных</h2>
        </div>
    </div>

    {{-- ===== НАВИГАЦИОННЫЕ КНОПКИ ===== --}}
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <a href="{{ route('winter-analytics.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-arrow-left-circle mdi-36px text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">ПЕРЕЙТИ</h5>
                            <h3 class="mb-0">к аналитике</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <a href="{{ route('winter-chart.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-chart-bar mdi-36px text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">ПЕРЕЙТИ</h5>
                            <h3 class="mb-0">к диаграмме</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ОПИСАНИЕ ===== --}}
    <h5 class="text-muted">
        Детальные данные по работам подготовки к зиме.
    </h5>
    <p class="mb-4">
        Выберите ДЖВ для просмотра данных по всем РДЖВ, 
        или конкретную РДЖВ для просмотра данных по её вокзалам.
    </p>

    {{-- ===== ФИЛЬТР И КНОПКА ЭКСПОРТА ===== --}}
    <div class="row mb-4">
        {{-- Select для выбора РДЖВ --}}
        <div class="col-md-4">
            <label for="regionFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="regionFilter" class="form-select" style="border-color: #007bff; color: #333;">
                <option value="all" {{ $selectedRegionId === 'all' ? 'selected' : '' }}>
                    ДЖВ (все РДЖВ)
                </option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $selectedRegionId == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        {{-- Кнопка экспорта в Excel --}}
        <div class="col-md-4 d-flex align-items-end">
            <a href="{{ route('winter-table.export', ['region_id' => $selectedRegionId]) }}" 
               id="exportBtn"
               class="btn btn-success">
                <i class="mdi mdi-file-excel"></i> Выгрузить в Excel
            </a>
        </div>
    </div>

    {{-- ===== ТАБЛИЦА ===== --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border: 1px solid #dee2e6;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        @if($selectedRegionId === 'all')
                            Данные по всем РДЖВ (ДЖВ)
                        @else
                            @php
                                $currentRegion = $regions->firstWhere('id', $selectedRegionId);
                            @endphp
                            Данные по вокзалам: {{ $currentRegion ? $currentRegion->name : '' }}
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
                        <table class="table table-bordered table-hover table-sm mb-0" id="winterTable">
                            {{-- ===== ШАПКА ТАБЛИЦЫ ===== --}}
                            <thead>
                                <tr class="bg-light">
                                    {{-- Первая колонка - наименование работ --}}
                                    <th rowspan="2" class="align-middle text-center sticky-col sticky-header" 
                                        style="min-width: 300px;">
                                        Наименование работ
                                    </th>
                                    
                                    {{-- Колонки для каждой РДЖВ/вокзала --}}
                                    @foreach($columns as $column)
                                        <th colspan="2" class="text-center sticky-header">
                                            {{ $column['name'] }}
                                        </th>
                                    @endforeach
                                    
                                    {{-- Итого --}}
                                    <th colspan="2" class="text-center bg-info text-white sticky-header">
                                        ИТОГО
                                    </th>
                                </tr>
                                <tr class="bg-light">
                                    {{-- Подзаголовки План/Факт для каждой колонки --}}
                                    @foreach($columns as $column)
                                        <th class="text-center sticky-header-2" style="min-width: 60px;">План</th>
                                        <th class="text-center sticky-header-2" style="min-width: 60px;">Факт</th>
                                    @endforeach
                                    
                                    {{-- Итого План/Факт --}}
                                    <th class="text-center bg-info text-white sticky-header-2" style="min-width: 60px;">План</th>
                                    <th class="text-center bg-info text-white sticky-header-2" style="min-width: 60px;">Факт</th>
                                </tr>
                            </thead>
                            
                            {{-- ===== ТЕЛО ТАБЛИЦЫ ===== --}}
                            <tbody>
                                @foreach($workStructure as $header)
                                    {{-- === ЗАГОЛОВОК (level 1) === --}}
                                    <tr class="header-row">
                                        <td class="sticky-col header-cell">
                                            <strong>{{ $header['item']->name }}</strong>
                                        </td>
                                        
                                        {{-- Суммы по заголовку для каждой колонки --}}
                                        @php
                                            $headerTotalPlan = 0;
                                            $headerTotalFact = 0;
                                        @endphp
                                        
                                        @foreach($columns as $column)
                                            @php
                                                $colPlan = 0;
                                                $colFact = 0;
                                                // Суммируем по всем работам в этом заголовке
                                                foreach ($header['subsections'] as $subsection) {
                                                    foreach ($subsection['works'] as $work) {
                                                        if (isset($tableData[$work->id][$column['id']])) {
                                                            $colPlan += $tableData[$work->id][$column['id']]['plan'];
                                                            $colFact += $tableData[$work->id][$column['id']]['fact'];
                                                        }
                                                    }
                                                }
                                                $headerTotalPlan += $colPlan;
                                                $headerTotalFact += $colFact;
                                            @endphp
                                            <td class="text-center"><strong>{{ $colPlan }}</strong></td>
                                            <td class="text-center"><strong>{{ $colFact }}</strong></td>
                                        @endforeach
                                        
                                        {{-- Итого по заголовку --}}
                                        <td class="text-center bg-info text-white"><strong>{{ $headerTotalPlan }}</strong></td>
                                        <td class="text-center bg-info text-white"><strong>{{ $headerTotalFact }}</strong></td>
                                    </tr>
                                    
                                    @foreach($header['subsections'] as $subsection)
                                        {{-- === ПОДЗАГОЛОВОК (level 2/3) === --}}
                                        <tr class="subheader-row">
                                            <td class="sticky-col subheader-cell">
                                                <em>{{ $subsection['item']->name }}</em>
                                            </td>
                                            
                                            {{-- Суммы по подзаголовку для каждой колонки --}}
                                            @php
                                                $subTotalPlan = 0;
                                                $subTotalFact = 0;
                                            @endphp
                                            
                                            @foreach($columns as $column)
                                                @php
                                                    $colPlan = 0;
                                                    $colFact = 0;
                                                    foreach ($subsection['works'] as $work) {
                                                        if (isset($tableData[$work->id][$column['id']])) {
                                                            $colPlan += $tableData[$work->id][$column['id']]['plan'];
                                                            $colFact += $tableData[$work->id][$column['id']]['fact'];
                                                        }
                                                    }
                                                    $subTotalPlan += $colPlan;
                                                    $subTotalFact += $colFact;
                                                @endphp
                                                <td class="text-center"><em>{{ $colPlan }}</em></td>
                                                <td class="text-center"><em>{{ $colFact }}</em></td>
                                            @endforeach
                                            
                                            {{-- Итого по подзаголовку --}}
                                            <td class="text-center bg-secondary text-white"><em>{{ $subTotalPlan }}</em></td>
                                            <td class="text-center bg-secondary text-white"><em>{{ $subTotalFact }}</em></td>
                                        </tr>
                                        
                                        {{-- === РАБОТЫ (листовые элементы) === --}}
                                        @foreach($subsection['works'] as $work)
                                            {{-- Не показываем работу если она совпадает с подзаголовком --}}
                                            @if($work->id !== $subsection['item']->id)
                                                <tr class="work-row">
                                                    <td class="sticky-col work-cell">
                                                        {{ $work->name }}
                                                    </td>
                                                    
                                                    @php
                                                        $workTotalPlan = 0;
                                                        $workTotalFact = 0;
                                                    @endphp
                                                    
                                                    @foreach($columns as $column)
                                                        @php
                                                            $plan = $tableData[$work->id][$column['id']]['plan'] ?? 0;
                                                            $fact = $tableData[$work->id][$column['id']]['fact'] ?? 0;
                                                            $workTotalPlan += $plan;
                                                            $workTotalFact += $fact;
                                                        @endphp
                                                        <td class="text-center">{{ $plan }}</td>
                                                        <td class="text-center">{{ $fact }}</td>
                                                    @endforeach
                                                    
                                                    {{-- Итого по работе --}}
                                                    <td class="text-center bg-light"><strong>{{ $workTotalPlan }}</strong></td>
                                                    <td class="text-center bg-light"><strong>{{ $workTotalFact }}</strong></td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach
                                
                                {{-- === ИТОГОВАЯ СТРОКА === --}}
                                <tr class="total-row">
                                    <td class="sticky-col total-cell">
                                        <strong>ИТОГО ПО ВСЕМ РАБОТАМ</strong>
                                    </td>
                                    
                                    @php
                                        $grandTotalPlan = 0;
                                        $grandTotalFact = 0;
                                    @endphp
                                    
                                    @foreach($columns as $column)
                                        @php
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
                                            $grandTotalPlan += $colPlan;
                                            $grandTotalFact += $colFact;
                                        @endphp
                                        <td class="text-center"><strong>{{ $colPlan }}</strong></td>
                                        <td class="text-center"><strong>{{ $colFact }}</strong></td>
                                    @endforeach
                                    
                                    {{-- Общий итог --}}
                                    <td class="text-center bg-warning text-dark"><strong>{{ $grandTotalPlan }}</strong></td>
                                    <td class="text-center bg-warning text-dark"><strong>{{ $grandTotalFact }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== СКРИПТЫ ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    /**
     * При изменении Select - перезагружаем страницу с новым параметром
     */
    $('#regionFilter').on('change', function() {
        const selectedValue = $(this).val();
        
        // Формируем URL с параметром region_id
        const url = new URL(window.location.href);
        url.searchParams.set('region_id', selectedValue);
        
        // Переходим на новый URL
        window.location.href = url.toString();
    });
});
</script>

{{-- ===== СТИЛИ ===== --}}
<style>
    /* Базовые стили таблицы */
    #winterTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #winterTable th,
    #winterTable td {
        border: 1px solid #dee2e6;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Фиксированная первая колонка */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 5;
        background: #fff;
    }

    /* Фиксированная шапка */
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #e9ecef !important;
    }

    .sticky-header-2 {
        position: sticky;
        top: 38px; /* Высота первой строки шапки */
        z-index: 10;
        background: #e9ecef !important;
    }

    /* Угловая ячейка (и фиксированная колонка, и шапка) */
    .sticky-col.sticky-header {
        z-index: 15;
    }

    /* Стили для заголовков (level 1) */
    .header-row td {
        background-color: #cce5ff !important;
    }
    .header-cell {
        background-color: #cce5ff !important;
        padding-left: 10px !important;
    }

    /* Стили для подзаголовков */
    .subheader-row td {
        background-color: #e2e3e5 !important;
    }
    .subheader-cell {
        background-color: #e2e3e5 !important;
        padding-left: 25px !important;
    }

    /* Стили для работ */
    .work-cell {
        padding-left: 50px !important;
    }

    /* Итоговая строка */
    .total-row td {
        background-color: #343a40 !important;
        color: #fff !important;
    }
    .total-cell {
        background-color: #343a40 !important;
        color: #fff !important;
    }

    /* Подсветка строки при наведении */
    #winterTable tbody tr:hover td:not(.bg-info):not(.bg-secondary):not(.bg-warning) {
        background-color: #fff3cd !important;
    }

    /* Стиль Select при фокусе */
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Тень для карточек */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Кнопка экспорта */
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
</style>
@endsection
