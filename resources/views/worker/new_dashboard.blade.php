@extends('layouts.admin')

@section('title', 'Дашборд')

@push('styles')
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .logo:hover { opacity: 0.8; }
        .card { border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .filter-section { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid #e9ecef; }
    </style>
@endpush

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                    <i class="mdi mdi-assistant"></i> ДЖВ
                </a>
                <h4 class="page-title mb-0">Статистика вакцинации</h4>
            </div>             
            <a href="{{ route('worker.export.vaccination', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="mdi mdi-file-excel"></i> Выгрузить отчет
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="filter-section">
            <form id="filter_form" method="GET" action="{{ route('worker.dashboard') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="font-weight-bold">Регион (РДЖВ):</label>
                        <select name="rdzv" id="filter_rdzv" class="form-control select2">
                            <option value="all" {{ $selectedRdzv == 'all' ? 'selected' : '' }}>Все регионы</option>
                            <option value="ou_dzhv" {{ $selectedRdzv == 'ou_dzhv' ? 'selected' : '' }}>ОУ ДЖВ</option>
                            @foreach($rdzvList as $rdzv)
                                <option value="{{ $rdzv }}" {{ $selectedRdzv == $rdzv ? 'selected' : '' }}>{{ $rdzv }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Вокзал:</label>
                        <select name="vokzal" id="filter_vokzal" class="form-control select2" {{ $selectedRdzv == 'all' || $selectedRdzv == 'ou_dzhv' ? 'disabled' : '' }}>
                            <option value="all">Все вокзалы</option>
                            <option value="ou_rdzv" {{ $selectedVokzal == 'ou_rdzv' ? 'selected' : '' }}>Аппарат РДЖВ</option>
                            {{-- Сюда JS подставит список вокзалов --}}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="btn_reset" class="btn btn-outline-secondary">
                            <i class="mdi mdi-refresh"></i> Сбросить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="mdi mdi-account-multiple mdi-36px text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-muted">Сотрудников</h5>
                        <h3 class="mb-0">{{ number_format($totalWorkers, 0, '', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="mdi mdi-shield-check mdi-36px text-success"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-muted">Вакцинировано</h5>
                        <h3 class="mb-0">{{ number_format($vaccinatedCount, 0, '', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="mdi mdi-percent mdi-36px text-info"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-muted">Процент</h5>
                        <h3 class="mb-0">{{ $totalVaccinatedPercent }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
{{--
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title">Распределение по регионам</h4>
                <div id="vaccination-chart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
--}}

@if(count($chartData) > 0)
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title">
                    @if($selectedRdzv == 'all')
                        Распределение по регионам
                    @else
                        Детализация по вокзалам: {{ $selectedRdzv }}
                    @endif
                </h4>
                <div id="vaccination-chart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
@endif



    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Детальные показатели по категориям</h4>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Регион</th>
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
</div>

@endsection

@push('scripts')
{{-- Raphael.js - обязательная зависимость для Morris --}}
<script src="{{ asset('assets/plugins/morris/raphael-min.js') }}"></script>
{{-- Morris.js --}}
<script src="{{ asset('assets/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    
    

    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });

            const $form = $('#filter_form');
            const $rdzv = $('#filter_rdzv');
            const $vokzal = $('#filter_vokzal');

            // 1. Обработка смены РДЖВ
            $rdzv.on('change', function () {
                const val = $(this).val();
                
                if (val === 'all' || val === 'ou_dzhv') {
                    $vokzal.html('<option value="all">Все вокзалы</option>').prop('disabled', true);
                    $form.submit();
                    return;
                }

                // Загружаем вокзалы через AJAX
                $.ajax({
                    url: '{{ route("worker.get-vokzals") }}',
                    data: { rdzv: val },
                    success: function (data) {
                        let html = '<option value="all">Все вокзалы</option>';
                        html += '<option value="ou_rdzv">Аппарат РДЖВ</option>';
                        $.each(data, function(i, name) {
                            html += `<option value="${name}">${name}</option>`;
                        });
                        $vokzal.html(html).prop('disabled', false).trigger('change.select2');
                        
                        // Если мы только что загрузили страницу с выбранным вокзалом
                        @if($selectedVokzal)
                            $vokzal.val('{{ $selectedVokzal }}').trigger('change.select2');
                        @endif
                    }
                });
            });

            // 2. Авто-отправка при выборе вокзала
            $vokzal.on('change', function() {
                if (!$(this).prop('disabled')) {
                    $form.submit();
                }
            });

            // 3. Сброс фильтров
            $('#btn_reset').on('click', function() {
                window.location.href = '{{ route("worker.dashboard") }}';
            });

            // Инициализация вокзалов при загрузке (если РДЖВ уже выбрана)
            if ($rdzv.val() !== 'all' && $rdzv.val() !== 'ou_dzhv') {
                $rdzv.trigger('change');
            }

            // --- График Morris ---
 
 
			const chartData = @json($chartData);
            
            if (chartData.length > 0) {
                new Morris.Bar({
                    element: 'vaccination-chart',
                    data: chartData,
                    xkey: 'rdzv',
                    ykeys: ['vaccinated_percent'],
                    labels: ['% Вакцинации'],
                    barColors: function (row, series, type) {
                        if (row.y < 75) return '#f1b44c'; // Warning
                        return '#34c38f'; // Success
                    },
                    hideHover: 'auto',
                    gridLineColor: '#eef0f2',
                    resize: true,
                    xLabelAngle: 35,
                    padding: 40,
                    hoverCallback: function(index, options, content, row) {
                        return `<div class='p-2'>
                            <b>${row.rdzv}</b><br/>
                            Вакцинировано: ${row.vaccinated_percent}%<br/>
                            Персонал: ${row.total} чел.
                        </div>`;
                    }
                });
            }
			
			
			
			
        });
    </script>
@endpush