@extends('layouts.admin')

@section('title', 'Таблица сотрудников')

@push('styles')
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .mdi-36px {
            font-size: 36px;
        }
        
        .logo:hover {
            opacity: 0.8;
        }
        
        .badge-soft-primary {
            background-color: rgba(91, 115, 232, 0.15);
            color: #5b73e8;
        }
        
        .badge-soft-info {
            background-color: rgba(23, 162, 184, 0.15);
            color: #17a2b8;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }
        
        .thead-dark th {
            background-color: #343a40;
            color: #fff;
            border-color: #454d55;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .table-container {
            position: relative;
            min-height: 200px;
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center">
            <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                <i class="mdi mdi-assistant"></i> ДЖВ
            </a>
            <h4 class="page-title mb-0">Таблица сотрудников</h4>
        </div>
    </div>
</div>

<!-- Навигация -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card m-b-30">
            <div class="card-body">
                <a href="{{ route('worker.dashboard') }}" class="d-flex align-items-center text-dark">
                    <div class="mr-3">
                        <i class="mdi mdi-arrow-left-circle mdi-36px text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">НАЗАД</h5>
                        <h3 class="mb-0">к дашборду</h3>
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

<!-- Блок фильтров -->
<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">
                    <i class="mdi mdi-filter-variant text-primary mr-2"></i>
                    Фильтры
                </h4>

                <div class="row">
                    {{-- Фильтр по РДЖВ --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_rdzv"><strong>РДЖВ</strong></label>
			{{--					
							<select name="rdzv" id="rdzv-select" class="form-control">
								<option value="all" {{ $selectedRdzv === 'all' ? 'selected' : '' }}>Все РДЖВ</option>
								<option value="ou_dzhv" {{ $selectedRdzv === 'ou_dzhv' ? 'selected' : '' }}>ОУ ДЖВ</option>
								@foreach($rdzvList as $code => $fullName)
									<option value="{{ $code }}" {{ $selectedRdzv === $code ? 'selected' : '' }}>
										{{ $fullName }}
									</option>
								@endforeach
							</select>
							
			--}}					
                            
							
							<select id="filter_rdzv" class="form-control select2">
                                <option value="all">Показать всех</option>
                                <option value="ou_dzhv">ОУ ДЖВ</option>
                                @foreach($rdzvList as $rdzv)
                                    <option value="{{ $rdzv }}">{{ $rdzv }}</option>
                                @endforeach
                            </select>
						
							
							
                        </div>
                    </div>

                    {{-- Фильтр по вокзалу --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_vokzal"><strong>Вокзалы</strong></label>
                            <select id="filter_vokzal" class="form-control select2" disabled>
                                <option value="">Выберите РДЖВ</option>
                            </select>
                        </div>
                    </div>

                    {{-- Кнопка сброса --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="btn_reset" class="btn btn-secondary btn-block">
                                <i class="mdi mdi-refresh mr-1"></i> Сбросить фильтры
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Таблица сотрудников -->
<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">
                    <i class="mdi mdi-account-group text-primary mr-2"></i>
                    Список сотрудников
                    <span id="workers_count" class="badge badge-primary ml-2">{{ $workers->total() }}</span>
                </h4>
                
                <div class="table-container" id="table_container">
                    {{-- Индикатор загрузки --}}
                    <div class="loading-overlay" id="loading" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка...</span>
                        </div>
                    </div>

                    {{-- Контент таблицы --}}
                    <div id="table_content">
                        @include('worker.table_body', ['workers' => $workers])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    <script>
        $(function () {
            // Инициализация Select2
            $('.select2').select2();

            // URL для AJAX-запросов
            var urlVokzals = '{{ route("worker.get-vokzals") }}';
            var urlTable = '{{ route("worker.table") }}';

            // Элементы
            var $rdzv = $('#filter_rdzv');
            var $vokzal = $('#filter_vokzal');
            var $loading = $('#loading');
            var $tableContent = $('#table_content');
            var $workersCount = $('#workers_count');

            /**
             * Показать/скрыть загрузку
             */
            function showLoading(show) {
                $loading.toggle(show);
            }

            /**
             * Загрузить данные таблицы
             */
            function loadTable() {
                showLoading(true);

                $.ajax({
                    url: urlTable,
                    type: 'GET',
                    data: {
                        rdzv: $rdzv.val(),
                        vokzal: $vokzal.val()
                    },
                    success: function (html) {
                        $tableContent.html(html);
                        
                        // Обновляем счётчик
                        var match = html.match(/data-total="(\d+)"/);
                        if (match) {
                            $workersCount.text(match[1]);
                        }
                    },
                    error: function () {
                        alert('Ошибка при загрузке данных');
                    },
                    complete: function () {
                        showLoading(false);
                    }
                });
            }

            /**
             * Сбросить селект вокзалов
             */
            function resetVokzal(placeholder, disabled) {
                $vokzal.empty();
                $vokzal.append('<option value="">' + placeholder + '</option>');
                $vokzal.prop('disabled', disabled);
                $vokzal.trigger('change.select2'); // Обновляем Select2
            }

            /**
             * При изменении РДЖВ
             */
            $rdzv.on('change', function () {
                var rdzv = $(this).val();

                // Если "Показать всех" или "ОУ ДЖВ" — деактивируем вокзалы
                if (rdzv === 'all' || rdzv === 'ou_dzhv') {
                    resetVokzal('Выберите РДЖВ', true);
                    loadTable();
                    return;
                }

                // Загружаем список вокзалов для выбранной РДЖВ
                $.getJSON(urlVokzals, { rdzv: rdzv }, function (data) {
                    $vokzal.empty();
                    $vokzal.append('<option value="all">Показать всех</option>');
                    $vokzal.append('<option value="ou_rdzv">ОУ РДЖВ</option>');
                    
                    $.each(data, function (index, vokzal) {
                        $vokzal.append('<option value="' + vokzal + '">' + vokzal + '</option>');
                    });

                    $vokzal.prop('disabled', false);
                    $vokzal.trigger('change.select2');
                });

                loadTable();
            });

            /**
             * При изменении вокзала
             */
            $vokzal.on('change', function () {
                if (!$(this).prop('disabled')) {
                    loadTable();
                }
            });

            /**
             * Сброс фильтров
             */
            $('#btn_reset').on('click', function () {
                $rdzv.val('all').trigger('change.select2');
                resetVokzal('Выберите РДЖВ', true);
                loadTable();
            });

            /**
             * AJAX-пагинация
             */
            $(document).on('click', '#table_content .pagination a', function (e) {
                e.preventDefault();
                showLoading(true);
                
                $.ajax({
                    url: $(this).attr('href'),
                    success: function (html) {
                        $tableContent.html(html);
                    },
                    complete: function () {
                        showLoading(false);
                    }
                });
            });
        });
    </script>
@endpush
