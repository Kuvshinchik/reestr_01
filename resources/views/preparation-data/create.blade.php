@extends('layouts.admin')

@section('title', 'Подготовка вокзалов к зиме')

@push('styles')
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row">
					<div class="col-sm-12">
						<div class="page-title-box">
							<div class="btn-group float-right">
								<ol class="breadcrumb hide-phone p-0 m-0">
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', ['slug' => 'index']) }}">БЛОК РАЗВИТИЯ ПРОИЗВОДСТВА</a></li>
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', 'seasons') }}">Сезоны</a></li>
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', ['slug' => 'zima']) }}">Зима</a></li>
									<li class="breadcrumb-item active">Форма ввода данных</li>
									
									
									
								</ol>
							</div>
							<h4 class="page-title">Форма ввода данных</h4>
						</div>
					</div>
				</div>

    {{-- Сообщение об успехе --}}
    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    {{-- Ошибки валидации --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="font-weight-bold mb-2">Исправьте ошибки:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $regionValue  = old('region_id', $selectedRegionId ?: '');
        $stationValue = old('station_id', $selectedStationId ?: '');
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h4 class="mt-0 header-title">Форма ввода данных</h4>
                    <p class="text-muted font-14">
                        1) Выберите РДЖВ → 2) выберите вокзал → 3) заполните работы → 4) нажмите «Сохранить».
                        Значения подставляются, если уже были сохранены.
                    </p>

                    <form method="POST" action="{{ route('preparation-data.store') }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="region_id">РДЖВ</label>
                                <select id="region_id"
                                        name="region_id"
                                        class="form-control select2 {{ $errors->has('region_id') ? 'is-invalid' : '' }}">
                                    <option value="">Выберите РДЖВ</option>
                                    @foreach($regions as $region)
										@continue($region->name === 'ДЖВ' || $region->name === 'КЛНГ')

											<option value="{{ $region->id }}" {{ (string)$regionValue === (string)$region->id ? 'selected' : '' }}>
												{{ $region->name }} — {{ $region->full_name }}
											</option>
									@endforeach
                                </select>
                                @error('region_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="station_id">Вокзал</label>
                                <select id="station_id"
                                        name="station_id"
                                        class="form-control select2 {{ $errors->has('station_id') ? 'is-invalid' : '' }}">
                                    <option value="">Сначала выберите РДЖВ</option>
                                </select>
                                @error('station_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        @if($stationValue)
                            <h5 class="mt-2 mb-3">Перечень работ</h5>

                            <div id="workAccordion" class="accordion">
                                @foreach($workStructure as $i => $block)
                                    @php $l1 = $block['item']; @endphp

                                    <div class="card">
                                        <div class="card-header" id="heading-l1-{{ $l1->id }}">
                                            <h6 class="mb-0">
                                                <button class="btn btn-link p-0" type="button"
                                                        data-toggle="collapse"
                                                        data-target="#collapse-l1-{{ $l1->id }}"
                                                        aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"
                                                        aria-controls="collapse-l1-{{ $l1->id }}">
                                                    {{ $l1->name }}
                                                </button>
                                            </h6>
                                        </div>

                                        <div id="collapse-l1-{{ $l1->id }}"
                                             class="collapse {{ $i === 0 ? 'show' : '' }}"
                                             aria-labelledby="heading-l1-{{ $l1->id }}"
                                             data-parent="#workAccordion">

                                            <div class="card-body">
                                                <div id="subAccordion-{{ $l1->id }}" class="accordion">
                                                    @foreach($block['subsections'] as $j => $sub)
                                                        @php
                                                            $subId = "sub-{$l1->id}-{$sub['key']}";
                                                        @endphp

                                                        <div class="card">
                                                            <div class="card-header" id="heading-{{ $subId }}">
                                                                <h6 class="mb-0">
                                                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                                                            data-toggle="collapse"
                                                                            data-target="#collapse-{{ $subId }}"
                                                                            aria-expanded="false"
                                                                            aria-controls="collapse-{{ $subId }}">
                                                                        {{ $sub['title'] }}
                                                                    </button>
                                                                </h6>
                                                            </div>

                                                            <div id="collapse-{{ $subId }}"
                                                                 class="collapse"
                                                                 aria-labelledby="heading-{{ $subId }}"
                                                                 data-parent="#subAccordion-{{ $l1->id }}">

                                                                <div class="card-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered mb-0">
                                                                            <thead class="thead-light">
                                                                                <tr>
                                                                                    <th style="width: 40%;">Наименование работ</th>
                                                                                    <th style="width: 10%;">План</th>
                                                                                    <th style="width: 10%;">Факт</th>
                                                                                    <th style="width: 10%;">Готово</th>
                                                                                    <th style="width: 30%;">Комментарий</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($sub['rows'] as $wi)
                                                                                    @php
                                                                                        // В rows уже должны приходить только "листья" (работы)
                                                                                        $savedRow = $saved[$wi->id] ?? null;

                                                                                        $planVal = old("plan.$wi->id", $savedRow->plan ?? 0);
                                                                                        $factVal = old("fact.$wi->id", $savedRow->fact ?? 0);

                                                                                        $checked = old("is_completed.$wi->id", $savedRow->is_completed ?? 0) ? true : false;

                                                                                        $commentVal = old("comment.$wi->id", $savedRow->comment ?? '');
                                                                                    @endphp

                                                                                    <tr>
                                                                                        <td>{{ $wi->name }}</td>
                                                                                        <td>
                                                                                            <input type="number" min="0"
                                                                                                   class="form-control form-control-sm"
                                                                                                   name="plan[{{ $wi->id }}]"
                                                                                                   value="{{ $planVal }}">
                                                                                        </td>
                                                                                        <td>
                                                                                            <input type="number" min="0"
                                                                                                   class="form-control form-control-sm"
                                                                                                   name="fact[{{ $wi->id }}]"
                                                                                                   value="{{ $factVal }}">
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            <input type="checkbox"
                                                                                                   name="is_completed[{{ $wi->id }}]"
                                                                                                   value="1"
                                                                                                   {{ $checked ? 'checked' : '' }}>
                                                                                        </td>
                                                                                        <td>
                                                                                            <input type="text"
                                                                                                   class="form-control form-control-sm"
                                                                                                   name="comment[{{ $wi->id }}]"
                                                                                                   value="{{ $commentVal }}"
                                                                                                   placeholder="Комментарий (если нужно)">
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div> {{-- subAccordion --}}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                <button type="reset" class="btn btn-secondary">Очистить</button>
                            </div>
                        @else
                            <div class="alert alert-info">
                                Сначала выберите РДЖВ и вокзал — после этого появится перечень работ.
                            </div>
                        @endif
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>

    <script>
        $(function () {
            $('.select2').select2();

            var stationsUrlBase = '{{ url('/preparation-data/stations') }}';
            var createUrl = '{{ route('preparation-data.create') }}';

            var selectedRegionId  = '{{ $regionValue }}';
            var selectedStationId = '{{ $stationValue }}';

            var suppressStationChange = false;

            function fillStations(regionId, stationIdToSelect) {
                var $stationSelect = $('#station_id');
                $stationSelect.empty();

                if (!regionId) {
                    $stationSelect.append('<option value="">Сначала выберите РДЖВ</option>');
                    $stationSelect.trigger('change.select2');
                    return;
                }

                $.getJSON(stationsUrlBase + '/' + regionId, function (data) {
                    if (!data || data.length === 0) {
                        $stationSelect.append('<option value="">Вокзалов нет (ОУ ДЖВ)</option>');
                        $stationSelect.trigger('change.select2');
                        return;
                    }

                    $stationSelect.append('<option value="">Выберите вокзал</option>');
                    $.each(data, function (_, station) {
                        $stationSelect.append(
                            $('<option>', { value: station.id, text: station.name })
                        );
                    });

                    if (stationIdToSelect) {
                        suppressStationChange = true;
                        $stationSelect.val(stationIdToSelect);
                        $stationSelect.trigger('change.select2');
                        suppressStationChange = false;
                    } else {
                        $stationSelect.trigger('change.select2');
                    }
                });
            }

            $('#region_id').on('change', function () {
                fillStations($(this).val(), null);
            });

            $('#station_id').on('change', function () {
                if (suppressStationChange) return;

                var regionId = $('#region_id').val();
                var stationId = $(this).val();

                if (!regionId || !stationId) return;

                window.location.href = createUrl
                    + '?region_id=' + encodeURIComponent(regionId)
                    + '&station_id=' + encodeURIComponent(stationId);
            });

            // Начальная загрузка (после сохранения или прямой ссылки)
            if (selectedRegionId) {
                fillStations(selectedRegionId, selectedStationId);
            }
        });
    </script>
@endpush
