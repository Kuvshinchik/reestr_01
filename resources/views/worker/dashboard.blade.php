@extends('layouts.admin')

@section('title', 'Дашборд')

@section('content')
<div class="container-fluid mt-4">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Статистика вакцинации сотрудников ДЖВ</h2>
            
        </div>
    </div>
<!-- Навигация -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30">
            <div class="card-body">
                <a href="{{ route('worker.analytics') }}" class="d-flex align-items-center text-dark">
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
                <a href="{{ route('worker.table') }}" class="d-flex align-items-center text-dark">
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

    <!-- Фильтр по РДЖВ -->
	<h5 class="text-muted">Количество вакцинированных сотрудников по каждой РДЖВ.
	
	</h5>
	<pmb-4>При наведении отображается процент от общего числа сотрудников.</p>
    <div class="row mb-4">
	
        <div class="col-md-4">
		
            <label for="rdzvFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="rdzvFilter" class="form-select" style="border-color: #007bff; color: #333;">
                <option value="all" selected>ВСЕГО по ДЖВ</option>
                @foreach($regions as $region)
                    <option value="{{ $region->name }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Контейнер для диаграммы -->
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
	
	{{--
    <!-- Таблица с данными -->
<div class="row">
    <div class="col-md-12">
        <div class="card" style="border: 1px solid #dee2e6;">
            <div class="card-header bg-light">
                <h5 class="mb-0">Таблица вакцинированных по РДЖВ</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>№ п/п</th>
                                <th>Наименование РДЖВ</th>
                                <th>Численность работников (чел.)</th>
                                <th>Прошли вакцинацию (чел.)</th>
                                <th>% вакцинированных</th>
                                <th>Уровень достижения целевого значения 75%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-info fw-bold">
                                <td>—</td>
                                <td>ВСЕГО по ДЖВ</td>
                                <td>{{ number_format($totalWorkers, 0, '', ' ') }}</td>
                                <td>{{ number_format($vaccinatedCount, 0, '', ' ') }}</td>
                                <td>{{ $totalVaccinatedPercent }}%</td>
                                <td>@php $totalTargetLevel = round($totalVaccinatedPercent / 75, 2); @endphp {{ $totalTargetLevel }}</td>
                            </tr>
                            @foreach($tableData as $loop_index => $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['rdzv'] }}</td>
                                    <td>{{ number_format($row['total_workers'], 0, '', ' ') }}</td>
                                    <td>{{ number_format($row['total_vaccinated'], 0, '', ' ') }}</td>
                                    <td>{{ $row['vaccinated_percent'] }}%</td>
                                    <td>{{ $row['target_level'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
	--}}
	
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Детальные показатели по категориям</h4>
				<a href="{{ route('worker.export.vaccination', request()->query()) }}" class="btn btn-success btn-sm  mb-4">
                <i class="mdi mdi-file-excel"></i> Выгрузить отчет
            </a>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>РДЖВ</th>
                                <th>Категория</th>
                                <th>Всего (чел.)</th>
                                <th>Вакцинировано (чел.)</th>
                                <th>%</th>
                                <th>Уровень (цель 75%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tableData as $rdzvName => $rdzv)
                                @foreach($rdzv['categories'] as $catName => $cat)
                                    <tr>
                                        @if($loop->first)
                                            <td rowspan="{{ count($rdzv['categories']) + 1 }}" class="align-middle font-weight-bold">
                                                {{ $rdzvName }}
                                            </td>
                                        @endif
                                        <td class="text-left">{{ $catName }}</td>
                                        <td>{{ $cat['total'] }}</td>
                                        <td>{{ $cat['vaccinated'] }}</td>
                                        <td>{{ $cat['vaccinated_percent'] }}%</td>
                                        <td>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar {{ $cat['target_level'] >= 1 ? 'bg-success' : 'bg-warning' }}" 
                                                     role="progressbar" style="width: {{ min($cat['target_level']*100, 100) }}%"></div>
                                            </div>
                                            <small>{{ $cat['target_level'] }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light font-weight-bold">
                                    <td class="text-left text-uppercase" style="font-size: 0.8rem">Итого по {{ $rdzvName }}</td>
                                    <td>{{ $rdzv['total_workers'] }}</td>
                                    <td>{{ $rdzv['total_vaccinated'] }}</td>
                                    <td>{{ $rdzv['vaccinated_percent'] }}%</td>
                                    <td>{{ $rdzv['target_level'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<!-- Подключаем jQuery, Morris.js и Raphael -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script>
$(document).ready(function() {
    let currentChart = null;

    function loadChart(rdzvValue) {
        $.ajax({
            url: "{{ route('worker.vaccination-chart-data') }}",
            type: 'GET',
            data: { rdzv: rdzvValue },
            dataType: 'json',
            success: function(response) {
                renderChart(response.data, response.label);
            },
            error: function(xhr, status, error) {
                $('#chart_container').html(
                    '<div class="alert alert-danger">Ошибка при загрузке данных диаграммы</div>'
                );
                console.error('Error:', error);
            }
        });
    }

    function renderChart(data, label) {
        // ПРОСТО ОЧИЩАЕМ КОНТЕЙНЕР вместо destroy()
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
            ykeys: ['value'],
            labels: ['% вакцинированных'],
            barColors: ['#007bff'],
            //hideHover: false,
			hideHover: 'always',
            gridLineColor: '#eee',
            resize: true,
            ymax: 100,
            ymin: 0,
            units: '%',
            xLabelAngle: 45,
            xLabelMargin: 10,
            formatter: function(value, data) {
                return value.toFixed(1) + '%';
            },
            hoverCallback: function(index, options, content, row) {
                return '<div class="morris-hover-row-label">' + row.label + '</div>' +
                       '<div class="morris-hover-row-label">' + row.value.toFixed(1) + '% вакцинировано</div>';
            }
        });
    }

    loadChart('all');

    $('#rdzvFilter').on('change', function() {
        const selectedValue = $(this).val();
        loadChart(selectedValue);
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

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Кастомные стили для Morris диаграммы */
    .morris-hover {
        position: absolute;
        z-index: 1000;
        background-color: rgba(0, 0, 0, 0.8);
        padding: 8px 12px;
        border-radius: 4px;
        color: white;
        font-size: 12px;
        pointer-events: none;
    }

    .morris-hover-row-label {
        white-space: nowrap;
    }
</style>
@endsection
