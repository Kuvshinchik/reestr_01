{{-- 
    Файл: resources/views/winter-worker-table/index.blade.php
    Таблица первозимников с фильтрами и экспортом в Excel
--}}
@extends('layouts.admin')

@section('title', 'Первозимники - Таблица')

@section('content')
<div class="container-fluid mt-4">
    
    {{-- ===== ЗАГОЛОВОК СТРАНИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Первозимники: Таблица данных</h2>
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
                    <a href="{{ route('winter-worker-chart.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-chart-bar mdi-36px text-info"></i>
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
        Таблица первозимников с данными о приёме и обучении.
    </h5>
    <p class="mb-4">
        Выберите ДЖВ или РДЖВ, укажите период (необязательно) и нажмите "Применить".
    </p>

    {{-- ===== ФИЛЬТРЫ ===== --}}
    <div class="row mb-4">
        {{-- Фильтр: Выбор РДЖВ --}}
        <div class="col-md-3">
            <label for="regionFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="regionFilter" class="form-select" style="border-color: #007bff; color: #333;">
                <option value="all" selected>ДЖВ (все РДЖВ)</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
        
        {{-- Фильтр: Дата С --}}
        <div class="col-md-2">
            <label for="dateFrom" class="form-label fw-bold">Дата приёма С:</label>
            <input type="date" id="dateFrom" class="form-control" style="border-color: #28a745;">
        </div>
        
        {{-- Фильтр: Дата ПО --}}
        <div class="col-md-2">
            <label for="dateTo" class="form-label fw-bold">Дата приёма ПО:</label>
            <input type="date" id="dateTo" class="form-control" style="border-color: #28a745;">
        </div>
        
        {{-- Кнопка применить --}}
        <div class="col-md-2 d-flex align-items-end">
            <button id="applyFilter" class="btn btn-primary btn-block">
                <i class="mdi mdi-filter mr-1"></i> Применить
            </button>
        </div>
        
        {{-- Кнопка сброса --}}
        <div class="col-md-2 d-flex align-items-end">
            <button id="resetFilter" class="btn btn-secondary btn-block">
                <i class="mdi mdi-refresh mr-1"></i> Сбросить
            </button>
        </div>
    </div>

    {{-- ===== КНОПКА ЭКСПОРТА В EXCEL ===== --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <button id="exportExcel" class="btn btn-success">
                <i class="mdi mdi-file-excel mr-1"></i> Выгрузить в Excel
            </button>
        </div>
    </div>

    {{-- ===== ТАБЛИЦА ===== --}}
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card" style="border: 1px solid #dee2e6;">
                <div class="card-body">
                    {{-- Заголовок таблицы (меняется динамически) --}}
                    <h5 id="tableTitle" class="card-title mb-3">ДЖВ (все РДЖВ)</h5>
                    
                    {{-- Контейнер для таблицы --}}
                    <div id="table_container">
                        <div class="text-center text-muted py-5">
                            <p>Загрузка данных...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ЛЕГЕНДА ===== --}}
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

</div>

{{-- ===== ПОДКЛЮЧЕНИЕ jQuery ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/**
 * =====================================================================
 * JAVASCRIPT ДЛЯ РАБОТЫ С ТАБЛИЦЕЙ
 * =====================================================================
 */
$(document).ready(function() {

    /**
     * -----------------------------------------------------------------
     * ФУНКЦИЯ: Загрузка данных таблицы
     * -----------------------------------------------------------------
     */
    function loadTable() {
        const regionId = $('#regionFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        // Показываем индикатор загрузки
        $('#table_container').html(
            '<div class="text-center text-muted py-5">' +
            '<i class="mdi mdi-loading mdi-spin mdi-36px"></i>' +
            '<p>Загрузка данных...</p>' +
            '</div>'
        );
        
        $.ajax({
            url: "{{ route('winter-worker-table.table-data') }}",
            type: 'GET',
            data: { 
                region_id: regionId,
                date_from: dateFrom,
                date_to: dateTo
            },
            dataType: 'json',
            success: function(response) {
                renderTable(response.data, response.columnName, response.totals);
                $('#tableTitle').text(response.label);
            },
            error: function(xhr, status, error) {
                $('#table_container').html(
                    '<div class="alert alert-danger">Ошибка при загрузке данных</div>'
                );
                console.error('Ошибка:', error);
            }
        });
    }

    /**
     * -----------------------------------------------------------------
     * ФУНКЦИЯ: Отрисовка таблицы
     * -----------------------------------------------------------------
     * 
     * @param data       - массив данных [{label, hired, trained}, ...]
     * @param columnName - название столбца (РДЖВ или Вокзал)
     * @param totals     - итоги {hired, trained}
     */
    function renderTable(data, columnName, totals) {
        if (!data || data.length === 0) {
            $('#table_container').html(
                '<div class="alert alert-info text-center py-5">' +
                '<p>Данные отсутствуют</p>' +
                '</div>'
            );
            return;
        }

        // Начинаем формировать HTML таблицы
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-primary">
                        <tr>
                            <th class="text-center" style="width: 80px;">№ п/п</th>
                            <th>${columnName}</th>
                            <th class="text-center" style="width: 120px;">Принято</th>
                            <th class="text-center" style="width: 120px;">Обучено</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        // Добавляем строки данных
        data.forEach((row, index) => {
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${row.label}</td>
                    <td class="text-center">${row.hired}</td>
                    <td class="text-center">${row.trained}</td>
                </tr>
            `;
        });

        // Добавляем строку ИТОГО
        html += `
                    </tbody>
                    <tfoot>
                        <tr class="table-success font-weight-bold">
                            <td class="text-center"></td>
                            <td><strong>ИТОГО</strong></td>
                            <td class="text-center"><strong>${totals.hired}</strong></td>
                            <td class="text-center"><strong>${totals.trained}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        $('#table_container').html(html);
    }

    /**
     * -----------------------------------------------------------------
     * ФУНКЦИЯ: Экспорт в Excel
     * -----------------------------------------------------------------
     * 
     * Формирует URL с параметрами и открывает его.
     * Сервер вернёт файл Excel для скачивания.
     */
    function exportToExcel() {
        const regionId = $('#regionFilter').val();
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        // Формируем URL с параметрами
        let url = "{{ route('winter-worker-table.export') }}";
        url += '?region_id=' + encodeURIComponent(regionId);
        
        if (dateFrom) {
            url += '&date_from=' + encodeURIComponent(dateFrom);
        }
        if (dateTo) {
            url += '&date_to=' + encodeURIComponent(dateTo);
        }
        
        // Открываем URL - браузер скачает файл
        window.location.href = url;
    }

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    loadTable();

    // ===== ОБРАБОТЧИКИ СОБЫТИЙ =====
    
    // Кнопка "Применить"
    $('#applyFilter').on('click', function() {
        loadTable();
    });

    // Кнопка "Сбросить"
    $('#resetFilter').on('click', function() {
        $('#regionFilter').val('all');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        loadTable();
    });

    // Кнопка "Выгрузить в Excel"
    $('#exportExcel').on('click', function() {
        exportToExcel();
    });

    // При изменении Select автоматически загружаем таблицу
    $('#regionFilter').on('change', function() {
        loadTable();
    });
});
</script>

{{-- ===== СТИЛИ ===== --}}
<style>
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: #4472C4;
        color: white;
        border-color: #3562B4;
        vertical-align: middle;
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
    
    .form-select:focus,
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .btn-block {
        width: 100%;
    }
    
    /* Анимация загрузки */
    .mdi-spin {
        animation: mdi-spin 1s infinite linear;
    }
    @keyframes mdi-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Стили для кнопок */
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
