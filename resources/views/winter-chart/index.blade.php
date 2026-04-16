{{-- 
    Файл: resources/views/winter-chart/index.blade.php
    ОБНОВЛЕННАЯ ВЕРСИЯ с двумя фильтрами и значениями над столбиками
--}}
@extends('layouts.admin')

@section('title', 'Подготовка к зиме - Диаграмма')

@section('content')
<div class="container-fluid mt-4">
    
    {{-- ===== ЗАГОЛОВОК СТРАНИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Подготовка к зиме: План и Факт</h2>
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
                    <a href="{{ route('winter-table.index') }}" class="d-flex align-items-center text-dark">
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
        Количество работ по подготовке к зиме (План/Факт).
    </h5>
    <p class="mb-4">
        Выберите РДЖВ и категорию работ для фильтрации данных.
    </p>

    {{-- ===== ФИЛЬТРЫ (ДВА SELECT) ===== --}}
    <div class="row mb-4">
        {{-- Первый Select: выбор РДЖВ --}}
        <div class="col-md-4">
            <label for="regionFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="regionFilter" class="form-select" style="border-color: #007bff; color: #333;">
                <option value="all" selected>ДЖВ (все РДЖВ)</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
        
        {{-- Второй Select: выбор категории работ --}}
        <div class="col-md-4">
            <label for="categoryFilter" class="form-label fw-bold">Выберите категорию:</label>
            <select id="categoryFilter" class="form-select" style="border-color: #28a745; color: #333;">
                {{-- Первый вариант - все категории --}}
                <option value="all" selected>Выбрать все</option>
                
                {{-- Категории из базы данных --}}
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ===== КОНТЕЙНЕР ДЛЯ ДИАГРАММЫ ===== --}}
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card" style="border: 1px solid #dee2e6;">
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

    {{-- ===== ЛЕГЕНДА ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center">
                <span class="mr-3">
                    <span style="display:inline-block;width:20px;height:20px;background:#007bff;margin-right:5px;vertical-align:middle;"></span>
                    План
                </span>
                <span style="margin-left: 20px;">
                    <span style="display:inline-block;width:20px;height:20px;background:#28a745;margin-right:5px;vertical-align:middle;"></span>
                    Факт
                </span>
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
     * Функция загрузки данных с сервера
     * Теперь передаем ДВА параметра: region_id и category_id
     */
    function loadChart() {
        // Получаем значения из ОБОИХ Select
        const regionId = $('#regionFilter').val();
        const categoryId = $('#categoryFilter').val();
        
        $.ajax({
            url: "{{ route('winter-chart.chart-data') }}",
            type: 'GET',
            data: { 
                region_id: regionId,      // Параметр 1: регион
                category_id: categoryId   // Параметр 2: категория работ
            },
            dataType: 'json',
            success: function(response) {
                renderChart(response.data, response.label);
            },
            error: function(xhr, status, error) {
                $('#chart_container').html(
                    '<div class="alert alert-danger">Ошибка при загрузке данных</div>'
                );
                console.error('Ошибка:', error);
            }
        });
    }

    /**
     * Функция отрисовки диаграммы
     * С ОТКЛЮЧЕННОЙ всплывающей подсказкой и ЗНАЧЕНИЯМИ НАД СТОЛБИКАМИ
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

        // Создаем диаграмму
        currentChart = Morris.Bar({
            element: 'chart_container',
            data: data,
            xkey: 'label',
            ykeys: ['plan', 'fact'],
            labels: ['План', 'Факт'],
            barColors: ['#007bff', '#28a745'],
            
            // =====================================================
            // ВАЖНО: ОТКЛЮЧЕНИЕ ВСПЛЫВАЮЩЕЙ ПОДСКАЗКИ
            // =====================================================
            // Значение 'always' означает "всегда скрывать подсказку"
            // Другие варианты:
            //   - 'auto' = скрывать когда курсор уходит с диаграммы
            //   - false = всегда показывать
            hideHover: 'always',
            
            // Убираем hoverCallback - он больше не нужен
            // hoverCallback: ... (УДАЛЕНО)
            
            gridLineColor: '#eee',
            resize: true,
            xLabelAngle: 45,
            xLabelMargin: 10,
            
            // Отступ сверху для размещения подписей над столбиками
            padding: 25
        });

        // =====================================================
        // ДОБАВЛЕНИЕ ЗНАЧЕНИЙ НАД СТОЛБИКАМИ
        // =====================================================
        // После отрисовки диаграммы добавляем текстовые метки
        addBarLabels(data);
    }

    /**
     * =====================================================
     * ФУНКЦИЯ ДОБАВЛЕНИЯ ЗНАЧЕНИЙ НАД СТОЛБИКАМИ
     * =====================================================
     * 
     * Morris.js не имеет встроенной функции для этого,
     * поэтому мы добавляем текст вручную через SVG
     * 
     * Как это работает:
     * 1. Находим SVG элемент диаграммы
     * 2. Находим все прямоугольники (столбики) - это элементы <rect>
     * 3. Для каждого столбика вычисляем позицию и добавляем текст
     */
    function addBarLabels(data) {
        // Небольшая задержка, чтобы Morris.js успел отрисовать SVG
        setTimeout(function() {
            // Находим SVG внутри контейнера
            const svg = $('#chart_container svg');
            if (svg.length === 0) return;

            // Получаем все прямоугольники (столбики)
            // Morris.js рисует столбики как <rect> элементы
            const bars = svg.find('rect');
            
            // Получаем пространство имен SVG (нужно для создания элементов)
            const svgNS = "http://www.w3.org/2000/svg";
            
            // Счетчик для определения какой это столбик (план или факт)
            let barIndex = 0;
            
            // Проходим по всем столбикам
            bars.each(function() {
                const bar = $(this);
                
                // Получаем позицию и размеры столбика
                const x = parseFloat(bar.attr('x'));
                const y = parseFloat(bar.attr('y'));
                const width = parseFloat(bar.attr('width'));
                const height = parseFloat(bar.attr('height'));
                
                // Пропускаем столбики с нулевой высотой
                if (height < 1) {
                    barIndex++;
                    return;
                }
                
                // Вычисляем какая это группа данных и какой столбик (план/факт)
                // data.length = количество групп (РДЖВ или вокзалов)
                // Для каждой группы 2 столбика: план и факт
                const dataIndex = Math.floor(barIndex / 2);  // Индекс группы
                const isFactBar = (barIndex % 2 === 1);      // Четный = план, нечетный = факт
                
                // Получаем значение для этого столбика
                let value = 0;
                if (dataIndex < data.length) {
                    value = isFactBar ? data[dataIndex].fact : data[dataIndex].plan;
                }
                
                // Создаем текстовый элемент SVG
                const text = document.createElementNS(svgNS, 'text');
                
                // Позиция текста:
                // X: центр столбика
                // Y: чуть выше верхней границы столбика
                text.setAttribute('x', x + width / 2);
                text.setAttribute('y', y - 5);
                
                // Стили текста
                text.setAttribute('text-anchor', 'middle');  // Выравнивание по центру
                text.setAttribute('font-size', '11px');
                text.setAttribute('font-weight', 'bold');
                text.setAttribute('fill', isFactBar ? '#28a745' : '#007bff');  // Цвет как у столбика
                
                // Устанавливаем значение
                text.textContent = value;
                
                // Добавляем текст в SVG
                svg[0].appendChild(text);
                
                barIndex++;
            });
            
        }, 100); // Задержка 100мс для завершения отрисовки
    }

    // ===== ИНИЦИАЛИЗАЦИЯ =====
    loadChart();

    // ===== ОБРАБОТЧИКИ СОБЫТИЙ =====
    // При изменении ЛЮБОГО из Select - перезагружаем диаграмму
    $('#regionFilter, #categoryFilter').on('change', function() {
        loadChart();
    });
});
</script>

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
    
    /* 
     * Дополнительно скрываем hover-элементы Morris.js через CSS
     * Это страховка на случай если hideHover не сработает
     */
    .morris-hover {
        display: none !important;
    }
</style>
@endsection
