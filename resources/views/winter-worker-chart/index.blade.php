{{-- 
    Файл: resources/views/winter-worker-chart/index.blade.php
    Диаграмма и таблица первозимников (с разбивкой по месяцам)
--}}
@extends('layouts.admin')

@section('title', 'Первозимники - Диаграмма и таблица')

@section('content')
<div class="container-fluid mt-4">
    
    {{-- ===== ЗАГОЛОВОК СТРАНИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Первозимники: Принято и Обучено</h2>
        </div>
    </div>

    {{-- ===== НАВИГАЦИОННЫЕ КНОПКИ ===== --}}
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <a href="{{ route('winter-worker-analytics.index') }}" class="d-flex align-items-center text-dark">
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
                    <a href="{{ route('winter-worker-table.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-table-large mdi-36px text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">ПЕРЕЙТИ</h5>
                            <h3 class="mb-0">к таблице</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ОПИСАНИЕ ===== --}}
    <h5 class="text-muted">
        Количество первозимников (принятые на работу / прошедшие обучение).
    </h5>
    <p class="mb-4">
        Выберите ДЖВ для просмотра данных по всем РДЖВ, или конкретный РДЖВ для просмотра по вокзалам.
    </p>

    {{-- ===== ФИЛЬТР (SELECT) ===== --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <label for="regionFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="regionFilter" class="form-select" style="border-color: #007bff; color: #333;">
                <option value="all" selected>ДЖВ (все РДЖВ)</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ===== КОНТЕЙНЕР ДЛЯ ДИАГРАММЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border: 1px solid #dee2e6;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="mdi mdi-chart-bar mr-2"></i>Диаграмма (итого)</h5>
                </div>
                <div class="card-body">
                    <div id="chart_container" style="min-height: 400px;">
                        <div class="text-center text-muted py-5">
                            <p>Загрузка диаграммы...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ЛЕГЕНДА ДИАГРАММЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center">
                <span class="mr-3">
                    <span style="display:inline-block;width:20px;height:20px;background:#007bff;margin-right:5px;vertical-align:middle;"></span>
                    Принято на работу
                </span>
                <span style="margin-left: 20px;">
                    <span style="display:inline-block;width:20px;height:20px;background:#28a745;margin-right:5px;vertical-align:middle;"></span>
                    Прошли обучение
                </span>
            </div>
        </div>
    </div>

    {{-- ===== КНОПКА ЭКСПОРТА ===== --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <button id="exportExcel" class="btn btn-success">
                <i class="mdi mdi-file-excel mr-1"></i> Выгрузить в Excel
            </button>
        </div>
    </div>

    {{-- ===== ТАБЛИЦА ПО МЕСЯЦАМ ===== --}}
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card" style="border: 1px solid #dee2e6;">
                <div class="card-header bg-white">
                    <h5 class="mb-0" id="tableTitle">
                        <i class="mdi mdi-table-large mr-2"></i>Таблица по месяцам: ДЖВ (все РДЖВ)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="table_container">
                        <div class="text-center text-muted py-5">
                            <p>Загрузка таблицы...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== ПОДКЛЮЧЕНИЕ БИБЛИОТЕК ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">

<script>
$(document).ready(function() {
    
    let currentChart = null;

    /**
     * Загрузка диаграммы
     */
    function loadChart() {
        const regionId = $('#regionFilter').val();
        
        $.ajax({
            url: "{{ route('winter-worker-chart.chart-data') }}",
            type: 'GET',
            data: { region_id: regionId },
            dataType: 'json',
            success: function(response) {
                renderChart(response.data, response.label);
            },
            error: function(xhr, status, error) {
                $('#chart_container').html(
                    '<div class="alert alert-danger">Ошибка при загрузке диаграммы</div>'
                );
            }
        });
    }

    /**
     * Загрузка таблицы
     */
    function loadTable() {
        const regionId = $('#regionFilter').val();
        
        $('#table_container').html(
            '<div class="text-center text-muted py-5">' +
            '<i class="mdi mdi-loading mdi-spin mdi-36px"></i>' +
            '<p>Загрузка таблицы...</p>' +
            '</div>'
        );
        
        $.ajax({
            url: "{{ route('winter-worker-chart.table-data') }}",
            type: 'GET',
            data: { region_id: regionId },
            dataType: 'json',
            success: function(response) {
                renderTable(response.data, response.months, response.columnName);
                $('#tableTitle').html(
                    '<i class="mdi mdi-table-large mr-2"></i>Таблица по месяцам: ' + response.label
                );
            },
            error: function(xhr, status, error) {
                $('#table_container').html(
                    '<div class="alert alert-danger m-3">Ошибка при загрузке таблицы</div>'
                );
            }
        });
    }

    /**
     * Отрисовка диаграммы
     */
    function renderChart(data, label) {
        $('#chart_container').empty();

        if (!data || data.length === 0) {
            $('#chart_container').html(
                '<div class="alert alert-info text-center py-5">' +
                '<p>' + label + ' - данные отсутствуют</p>' +
                '</div>'
            );
            return;
        }

        currentChart = Morris.Bar({
            element: 'chart_container',
            data: data,
            xkey: 'label',
            ykeys: ['hired', 'trained'],
            labels: ['Принято', 'Обучено'],
            barColors: ['#007bff', '#28a745'],
            hideHover: 'always',
            gridLineColor: '#eee',
            resize: true,
            xLabelAngle: 45,
            xLabelMargin: 10,
            padding: 25
        });

        addBarLabels(data);
    }

    /**
     * Добавление значений над столбиками
     */
    function addBarLabels(data) {
        setTimeout(function() {
            const svg = $('#chart_container svg');
            if (svg.length === 0) return;

            const bars = svg.find('rect');
            const svgNS = "http://www.w3.org/2000/svg";
            let barIndex = 0;
            
            bars.each(function() {
                const bar = $(this);
                const x = parseFloat(bar.attr('x'));
                const y = parseFloat(bar.attr('y'));
                const width = parseFloat(bar.attr('width'));
                const height = parseFloat(bar.attr('height'));
                
                if (height < 1) {
                    barIndex++;
                    return;
                }
                
                const dataIndex = Math.floor(barIndex / 2);
                const isTrainedBar = (barIndex % 2 === 1);
                
                let value = 0;
                if (dataIndex < data.length) {
                    value = isTrainedBar ? data[dataIndex].trained : data[dataIndex].hired;
                }
                
                const text = document.createElementNS(svgNS, 'text');
                text.setAttribute('x', x + width / 2);
                text.setAttribute('y', y - 5);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('font-size', '11px');
                text.setAttribute('font-weight', 'bold');
                text.setAttribute('fill', isTrainedBar ? '#28a745' : '#007bff');
                text.textContent = value;
                
                svg[0].appendChild(text);
                barIndex++;
            });
        }, 100);
    }

    /**
     * Отрисовка таблицы
     */
    function renderTable(data, months, columnName) {
        if (!data || data.length === 0) {
            $('#table_container').html(
                '<div class="alert alert-info text-center m-3">Данные отсутствуют</div>'
            );
            return;
        }

        // Начинаем формировать HTML
        let html = '<div class="table-responsive"><table class="table table-bordered table-striped table-hover mb-0">';
        
        // Шапка таблицы
        html += '<thead class="thead-primary"><tr>';
        html += '<th class="text-center" style="min-width:60px;">№ п/п</th>';
        html += '<th style="min-width:150px;">' + columnName + '</th>';
        
        months.forEach(function(month) {
            html += '<th class="text-center month-col" style="min-width:80px;">принято<br>' + month.label + '</th>';
            html += '<th class="text-center month-col" style="min-width:80px;">обучено<br>' + month.label + '</th>';
        });
        
        html += '<th class="text-center total-col" style="min-width:80px;">всего<br>принято</th>';
        html += '<th class="text-center total-col" style="min-width:80px;">всего<br>обучено</th>';
        html += '</tr></thead>';
        
        // Тело таблицы
        html += '<tbody>';
        
        let grandTotalHired = 0;
        let grandTotalTrained = 0;
        let monthTotals = {};
        
        // Инициализируем итоги по месяцам
        months.forEach(function(month) {
            const key = month.year + '-' + String(month.month).padStart(2, '0');
            monthTotals[key] = { hired: 0, trained: 0 };
        });
        
        data.forEach(function(row, index) {
            html += '<tr>';
            html += '<td class="text-center">' + (index + 1) + '</td>';
            html += '<td>' + row.label + '</td>';
            
            months.forEach(function(month) {
                const key = month.year + '-' + String(month.month).padStart(2, '0');
                const monthData = row.months[key] || { hired: 0, trained: 0 };
                
                html += '<td class="text-center">' + (monthData.hired || 0) + '</td>';
                html += '<td class="text-center">' + (monthData.trained || 0) + '</td>';
                
                monthTotals[key].hired += (monthData.hired || 0);
                monthTotals[key].trained += (monthData.trained || 0);
            });
            
            html += '<td class="text-center font-weight-bold">' + row.total_hired + '</td>';
            html += '<td class="text-center font-weight-bold">' + row.total_trained + '</td>';
            html += '</tr>';
            
            grandTotalHired += row.total_hired;
            grandTotalTrained += row.total_trained;
        });
        
        html += '</tbody>';
        
        // Строка ИТОГО
        html += '<tfoot><tr class="table-success font-weight-bold">';
        html += '<td class="text-center"></td>';
        html += '<td><strong>Итого</strong></td>';
        
        months.forEach(function(month) {
            const key = month.year + '-' + String(month.month).padStart(2, '0');
            html += '<td class="text-center">' + monthTotals[key].hired + '</td>';
            html += '<td class="text-center">' + monthTotals[key].trained + '</td>';
        });
        
        html += '<td class="text-center"><strong>' + grandTotalHired + '</strong></td>';
        html += '<td class="text-center"><strong>' + grandTotalTrained + '</strong></td>';
        html += '</tr></tfoot>';
        
        html += '</table></div>';
        
        $('#table_container').html(html);
    }

    /**
     * Экспорт в Excel
     */
    function exportToExcel() {
        const regionId = $('#regionFilter').val();
        let url = "{{ route('winter-worker-chart.export') }}";
        url += '?region_id=' + encodeURIComponent(regionId);
        window.location.href = url;
    }

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    loadChart();
    loadTable();

    // ===== ОБРАБОТЧИКИ =====
    $('#regionFilter').on('change', function() {
        loadChart();
        loadTable();
    });

    $('#exportExcel').on('click', function() {
        exportToExcel();
    });
});
</script>

{{-- ===== СТИЛИ ===== --}}
<style>
    #chart_container {
        background-color: #f9f9f9;
        border-radius: 4px;
        padding: 20px;
    }

    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .morris-hover {
        display: none !important;
    }

    /* Стили таблицы */
    .table {
        margin-bottom: 0;
        font-size: 0.85rem;
    }
    
    .table thead th {
        background-color: #4472C4;
        color: white;
        border-color: #3562B4;
        vertical-align: middle;
        text-align: center;
        font-weight: 600;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    .table tfoot tr {
        background-color: #E2EFDA !important;
    }
    
    .table tfoot td {
        font-weight: bold;
    }

    .month-col {
        background-color: #5B9BD5 !important;
    }

    .total-col {
        background-color: #2E75B6 !important;
    }

    /* Анимация загрузки */
    .mdi-spin {
        animation: mdi-spin 1s infinite linear;
    }
    @keyframes mdi-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Адаптивная таблица */
    .table-responsive {
        overflow-x: auto;
    }

    /* Кнопка Excel */
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
