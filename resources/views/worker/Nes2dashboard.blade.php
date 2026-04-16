@extends('layouts.admin')

@section('title', 'Дашборд')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center">
            <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                <i class="mdi mdi-assistant"></i> ДЖВ
            </a>
            <h4 class="page-title mb-0">Дашборд</h4>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="mdi mdi-account-multiple mdi-36px text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Всего сотрудников</h5>
                        <h3 class="mb-0">{{ number_format($totalWorkers, 0, '', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="mdi mdi-needle mdi-36px text-success"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Привито</h5>
                        <h3 class="mb-0">{{ number_format($vaccinatedCount, 0, '', ' ') }}</h3>
                    </div>
                </div>
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
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30">
            <div class="card-body">
                <a href="{{ route('worker.analytics') }}" class="d-flex align-items-center text-dark">
                    <div class="mr-3">
                        <i class="mdi mdi-chart-bar mdi-36px text-warning"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">ПЕРЕЙТИ</h5>
                        <h3 class="mb-0">к аналитике</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- График: Вакцинация по РДЖВ -->
<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">
                    <i class="mdi mdi-chart-bar text-primary mr-2"></i>
                    Вакцинация по РДЖВ
                </h4>
                <p class="text-muted mb-4">
                    Количество вакцинированных сотрудников по каждой РДЖВ. 
                    При наведении отображается процент от общего числа сотрудников.
                </p>
                
                {{-- Контейнер для графика Morris --}}
                <div id="vaccination-chart" style="height: 420px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Таблица: Вакцинация по РДЖВ -->

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">
                    <i class="mdi mdi-table-large text-primary mr-2"></i>
                    Сводная таблица вакцинации
                </h4>
        {{-- Кнопка экспорта в Excel --}}
        <a href="{{ route('worker.export.vaccination') }}" class="btn btn-success">
            <i class="mdi mdi-file-excel mr-1"></i>
            Скачать в Excel
        </a>
		</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center align-middle" style="width: 50px;" rowspan="2">№ п/п</th>
                                <th class="align-middle" rowspan="2">Наименование РДЖВ</th>
                                <th class="align-middle" rowspan="2">Категория персонала</th>
                                
                                <th class="text-center" colspan="4">Показатели вакцинации</th>
                            </tr>
                            <tr>
                                <th class="text-center">Численность<br>работников (чел.)</th>
                                <th class="text-center">Прошли<br>вакцинацию (чел.)</th>
                                <th class="text-center">% вакцини-<br>рованных</th>
                                <th class="text-center">Уровень достижения<br>целевого значения 75%</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Итоговая строка ИТОГО --}}
                            <tr class="table-primary font-weight-bold">
                                <td class="text-center align-middle" rowspan="4">—</td>
                                <td class="align-middle" rowspan="4"><strong>ИТОГО</strong></td>
                                <td class="text-center">кадры массовых профессий</td>
                                @php
                                    $itogo_cat1_total = 0;
                                    $itogo_cat1_vacc = 0;
                                    foreach ($tableData as $rdzvData) {
                                        if (isset($rdzvData['categories']['кадры массовых профессий'])) {
                                            $itogo_cat1_total += $rdzvData['categories']['кадры массовых профессий']['total'];
                                            $itogo_cat1_vacc += $rdzvData['categories']['кадры массовых профессий']['vaccinated'];
                                        }
                                    }
                                    $itogo_cat1_percent = $itogo_cat1_total > 0 ? round(($itogo_cat1_vacc / $itogo_cat1_total) * 100, 1) : 0;
                                    $itogo_cat1_level = round($itogo_cat1_percent / 75, 2);
                                @endphp
                                <td class="text-center">{{ number_format($itogo_cat1_total, 0, '', ' ') }}</td>
                                <td class="text-center">{{ number_format($itogo_cat1_vacc, 0, '', ' ') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat1_percent >= 75 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat1_percent }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat1_level >= 1 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat1_level }}
                                    </span>
                                </td>
                            </tr>
                            
                            <tr class="table-primary font-weight-bold">
                                <td class="text-center">работники, непосредственно связанные с обслуживанием пассажиров</td>
                                @php
                                    $itogo_cat2_total = 0;
                                    $itogo_cat2_vacc = 0;
                                    foreach ($tableData as $rdzvData) {
                                        if (isset($rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров'])) {
                                            $itogo_cat2_total += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['total'];
                                            $itogo_cat2_vacc += $rdzvData['categories']['работники, непосредственно связанные с обслуживанием пассажиров']['vaccinated'];
                                        }
                                    }
                                    $itogo_cat2_percent = $itogo_cat2_total > 0 ? round(($itogo_cat2_vacc / $itogo_cat2_total) * 100, 1) : 0;
                                    $itogo_cat2_level = round($itogo_cat2_percent / 75, 2);
                                @endphp
                                <td class="text-center">{{ number_format($itogo_cat2_total, 0, '', ' ') }}</td>
                                <td class="text-center">{{ number_format($itogo_cat2_vacc, 0, '', ' ') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat2_percent >= 75 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat2_percent }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat2_level >= 1 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat2_level }}
                                    </span>
                                </td>
                            </tr>
                            
                            <tr class="table-primary font-weight-bold">
                                <td class="text-center">остальные</td>
                                @php
                                    $itogo_cat3_total = 0;
                                    $itogo_cat3_vacc = 0;
                                    foreach ($tableData as $rdzvData) {
                                        if (isset($rdzvData['categories']['остальные'])) {
                                            $itogo_cat3_total += $rdzvData['categories']['остальные']['total'];
                                            $itogo_cat3_vacc += $rdzvData['categories']['остальные']['vaccinated'];
                                        }
                                    }
                                    $itogo_cat3_percent = $itogo_cat3_total > 0 ? round(($itogo_cat3_vacc / $itogo_cat3_total) * 100, 1) : 0;
                                    $itogo_cat3_level = round($itogo_cat3_percent / 75, 2);
                                @endphp
                                <td class="text-center">{{ number_format($itogo_cat3_total, 0, '', ' ') }}</td>
                                <td class="text-center">{{ number_format($itogo_cat3_vacc, 0, '', ' ') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat3_percent >= 75 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat3_percent }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $itogo_cat3_level >= 1 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        {{ $itogo_cat3_level }}
                                    </span>
                                </td>
                            </tr>
                            
                            <tr class="table-primary font-weight-bold">
                                <td class="text-center"><strong>ВСЕГО</strong></td>
                                <td class="text-center"><strong>{{ number_format($totalWorkers, 0, '', ' ') }}</strong></td>
                                <td class="text-center"><strong>{{ number_format($vaccinatedCount, 0, '', ' ') }}</strong></td>
                                <td class="text-center">
                                    <span class="badge {{ $totalVaccinatedPercent >= 75 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        <strong>{{ $totalVaccinatedPercent }}%</strong>
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php $totalTargetLevel = round($totalVaccinatedPercent / 75, 2); @endphp
                                    <span class="badge {{ $totalTargetLevel >= 1 ? 'badge-success' : 'badge-warning' }} px-2 py-1">
                                        <strong>{{ $totalTargetLevel }}</strong>
                                    </span>
                                </td>
                            </tr>
                            
                            {{-- Данные по каждой РДЖВ --}}
                            @php $rowNumber = 1; @endphp
                            @foreach($tableData as $rdzvData)
                                @php
                                    $categories = [
                                        'кадры массовых профессий',
                                        'работники, непосредственно связанные с обслуживанием пассажиров',
                                        'остальные'
                                    ];
                                    $rowspanCount = count($categories) + 1; // +1 для строки "ВСЕГО по РДЖВ"
                                @endphp
                                
                                @foreach($categories as $catIndex => $categoryName)
                                    <tr>
                                        @if($catIndex === 0)
                                            <td class="text-center align-middle" rowspan="{{ $rowspanCount }}">{{ $rowNumber }}</td>
                                            <td class="align-middle" rowspan="{{ $rowspanCount }}">{{ $rdzvData['rdzv'] }}</td>
                                        @endif
                                        
                                        <td class="text-center">{{ $categoryName }}</td>
                                        
                                        @if(isset($rdzvData['categories'][$categoryName]))
                                            @php $catData = $rdzvData['categories'][$categoryName]; @endphp
                                            <td class="text-center">{{ number_format($catData['total'], 0, '', ' ') }}</td>
                                            <td class="text-center">{{ number_format($catData['vaccinated'], 0, '', ' ') }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $catData['vaccinated_percent'] >= 75 ? 'badge-success' : ($catData['vaccinated_percent'] >= 50 ? 'badge-warning' : 'badge-danger') }} px-2 py-1">
                                                    {{ $catData['vaccinated_percent'] }}%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $catData['target_level'] >= 1 ? 'badge-success' : ($catData['target_level'] >= 0.7 ? 'badge-warning' : 'badge-danger') }} px-2 py-1">
                                                    {{ $catData['target_level'] }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary px-2 py-1">0%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary px-2 py-1">0</span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                
                                {{-- Строка ВСЕГО по текущей РДЖВ --}}
                                <tr class="table-light font-weight-bold">
                                    <td class="text-center"><strong>ВСЕГО</strong></td>
                                    <td class="text-center"><strong>{{ number_format($rdzvData['total_workers'], 0, '', ' ') }}</strong></td>
                                    <td class="text-center"><strong>{{ number_format($rdzvData['total_vaccinated'], 0, '', ' ') }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge {{ $rdzvData['vaccinated_percent'] >= 75 ? 'badge-success' : ($rdzvData['vaccinated_percent'] >= 50 ? 'badge-warning' : 'badge-danger') }} px-2 py-1">
                                            <strong>{{ $rdzvData['vaccinated_percent'] }}%</strong>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $rdzvData['target_level'] >= 1 ? 'badge-success' : ($rdzvData['target_level'] >= 0.7 ? 'badge-warning' : 'badge-danger') }} px-2 py-1">
                                            <strong>{{ $rdzvData['target_level'] }}</strong>
                                        </span>
                                    </td>
                                </tr>
                                
                                @php $rowNumber++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="mdi mdi-information-outline mr-1"></i>
                        Целевое значение вакцинации — 75%. 
                        Уровень достижения = % вакцинированных / 75%.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-btn {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        min-height: 150px;
        white-space: normal;
    }
    
    .nav-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .nav-btn span {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }
    
    .mdi-36px {
        font-size: 36px;
    }
    
    .logo:hover {
        opacity: 0.8;
    }

    /* Стили для подсказки Morris */
    .morris-hover {
        position: absolute;
        z-index: 1000;
        padding: 10px 15px;
        background-color: #333;
        color: #fff;
        border-radius: 5px;
        font-size: 13px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .morris-hover .morris-hover-row-label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #5b73e8;
    }
    
    .morris-hover .morris-hover-point {
        margin: 3px 0;
    }
</style>
@endpush

@push('scripts')
{{-- Raphael.js - обязательная зависимость для Morris --}}
<script src="{{ asset('assets/plugins/morris/raphael-min.js') }}"></script>
{{-- Morris.js --}}
<script src="{{ asset('assets/plugins/morris/morris.min.js') }}"></script>

<script>
$(function() {
    // Данные для графика из контроллера
    var chartData = @json($chartData);
    
    // Создаём столбчатую диаграмму Morris
    var chart = Morris.Bar({
        // ID элемента, куда вставить график
        element: 'vaccination-chart',
        
        // Данные для графика
        data: chartData,
        
        // Ключ для оси X (название РДЖВ)
        xkey: 'rdzv',
        
        // Ключи для оси Y (вакцинированные)
        ykeys: ['vaccinated'],
        
        // Подписи для легенды
        labels: ['Вакцинировано'],
        
        // Цвет столбцов
        barColors: ['#5b73e8'],
        
        // Скругление столбцов
        barRadius: [5, 5, 0, 0],
        
        // Отступ между столбцами
        barGap: 5,
        barSizeRatio: 0.6,
        
        // Сетка
        grid: true,
        gridTextSize: 10,
        gridTextColor: '#888',
        
        // Поворот подписей на оси X (в градусах)
        xLabelAngle: 45,
        
        // Отступ для подписей оси X
        xLabelMargin: 15,
        
        // Отступ снизу для повёрнутых подписей
        padding: 60,
        
        // Изменение размера при ресайзе
        resize: true,
        
        // Кастомная подсказка при наведении
        hoverCallback: function(index, options, content, row) {
            return '<div class="morris-hover-row-label">' + row.rdzv + '</div>' +
                   '<div class="morris-hover-point">Вакцинировано: <b>' + row.vaccinated + '</b> чел.</div>' +
                   '<div class="morris-hover-point">Всего в РДЖВ: <b>' + row.total + '</b> чел.</div>' +
                   '<div class="morris-hover-point">Процент от общего: <b>' + row.percent + '%</b></div>';
        }
    });

    // Принудительно показываем все подписи на оси X
    $('#vaccination-chart svg text').each(function() {
        $(this).css('display', 'block');
    });
});
</script>
@endpush

	{{--
	
	dd($row)
	array:6 [▼ // resources\views/worker/dashboard.blade.php
  "rdzv" => "ЮУР"
  "vaccinated" => 282
  "total" => 288
  "percent" => 4.47
  "vaccinated_percent" => 97.9
  "target_level" => 1.31
]
	
	
	
	
{{dd($chartData);}}	

	
	 1 => array:6 [▼
      "rdzv" => "ВСИБ"
      "vaccinated" => 281
      "total" => 310
      "percent" => 4.45
      "vaccinated_percent" => 90.6
      "target_level" => 1.21
    ]
	--}}