@extends('layouts.admin')

@section('title', 'Подготовка вокзалов к зиме')

@push('styles')
    {{-- Select2 + Datepicker (как в form-advanced) --}}
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
<!--	    <link href="{{ asset('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">  -->
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="btn-group float-right">
                    <ol class="breadcrumb hide-phone p-0 m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Главная</a></li>
                        <li class="breadcrumb-item active">Подготовка вокзалов</li>
                    </ol>
                </div>
                <h4 class="page-title">Подготовка вокзалов к зиме</h4>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h4 class="mt-0 header-title">Форма ввода данных</h4>
                    <p class="text-muted font-14">
                        Сначала выберите РДЖВ, затем вокзал, сезон и заполните план/факт по каждому виду работ.
                    </p>

                    <form method="POST" action="{{ route('preparation-data.store') }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="region_id">РДЖВ</label>
                                <select id="region_id" name="region_id"
                                        class="form-control select2 {{ $errors->has('region_id') ? 'is-invalid' : '' }}">
                                    <option value="">Выберите РДЖВ</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}"
                                            {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }} — {{ $region->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="station_id">Вокзал</label>
                                <select id="station_id" name="station_id"
                                        class="form-control select2 {{ $errors->has('station_id') ? 'is-invalid' : '' }}">
                                    <option value="">Сначала выберите РДЖВ</option>
                                </select>
                                @error('station_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            

<div class="form-group col-md-4">
    <label for="season">Сезон</label>
    
	
	<select id="season" name="season" class="form-control">
		<option value="">Выберите сезон</option>
		<option value="winter" {{ old('season') === 'winter' ? 'selected' : '' }}>Зима</option>
		<option value="summer" {{ old('season') === 'summer' ? 'selected' : '' }}>Лето</option>
	</select>

	
	
    @error('season')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>
							
							
                        </div>

                        <hr>

                        <h5 class="mt-4 mb-3">Виды работ</h5>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width: 55%;">Вид работ</th>
                                    <th style="width: 10%;">Ед. изм.</th>
                                    <th style="width: 15%;">План</th>
                                    <th style="width: 15%;">Факт</th>
                                </tr>
                                </thead>
								

    <tbody id="tbody-winter">
    @foreach($winterCategories as $category)
        <tr>
            <td>{{ $category->name }}</td>
            <td class="text-center">{{ $category->unit }}</td>
            <td>
                <input type="number"
                       name="plan[{{ $category->id }}]"
                       class="form-control form-control-sm"
                       min="0">
            </td>
            <td>
                <input type="number"
                       name="fact[{{ $category->id }}]"
                       class="form-control form-control-sm"
                       min="0">
            </td>
        </tr>
    @endforeach
    </tbody>

    <tbody id="tbody-summer" class="d-none">
    @foreach($summerCategories as $category)
        <tr>
            <td>{{ $category->name }}</td>
            <td class="text-center">{{ $category->unit }}</td>
            <td>
                <input type="number"
                       name="plan[{{ $category->id }}]"
                       class="form-control form-control-sm"
                       min="0">
            </td>
            <td>
                <input type="number"
                       name="fact[{{ $category->id }}]"
                       class="form-control form-control-sm"
                       min="0">
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
                        
						
						
						</div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                Сохранить
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                Очистить
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JS-плагины как в шаблоне form-advanced.html --}}
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<!--	    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>  

    <script>
        $(function () {
            // Инициализация select2
            $('.select2').select2();

            // Datepicker (формат под БД: yyyy-mm-dd)
/*            $('#report_date').datepicker({
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
*/
            var stationsUrlBase = '{{ url('/preparation-data/stations') }}';

            // При смене РДЖВ подгружаем вокзалы
            $('#region_id').on('change', function () {
                var regionId = $(this).val();
                var $stationSelect = $('#station_id');

                $stationSelect.empty().trigger('change');

                if (!regionId) {
                    $stationSelect.append('<option value="">Сначала выберите РДЖВ</option>');
                    return;
                }

                $.getJSON(stationsUrlBase + '/' + regionId, function (data) {
                    $stationSelect.append('<option value="">Выберите вокзал</option>');
                    $.each(data, function (index, station) {
                        $stationSelect.append(
                            $('<option>', {
                                value: station.id,
                                text: station.name
                            })
                        );
                    });

                    // Переподключаем select2, чтобы список обновился красиво
                    $stationSelect.trigger('change');
                });
            });

            // Если была ошибка валидации и старое значение region_id уже есть —
            // можно автоматически перезагрузить станции (опционально, если нужно)
            @if(old('region_id'))
                $('#region_id').trigger('change');
            @endif
        });
    </script>
	 -->
	<script>
    $(function () {
        $('.select2').select2();

        var stationsUrlBase = '{{ url('/preparation-data/stations') }}';

        $('#region_id').on('change', function () {
            var regionId = $(this).val();
            var $stationSelect = $('#station_id');

            $stationSelect.empty().trigger('change');

            if (!regionId) {
                $stationSelect.append('<option value="">Сначала выберите РДЖВ</option>');
                return;
            }

            $.getJSON(stationsUrlBase + '/' + regionId, function (data) {
                $stationSelect.append('<option value="">Выберите вокзал</option>');
                $.each(data, function (index, station) {
                    $stationSelect.append(
                        $('<option>', {
                            value: station.id,
                            text: station.name
                        })
                    );
                });

                $stationSelect.trigger('change');
            });
        });

        function switchSeasonTables(season) {
            var $tbodyWinter = $('#tbody-winter');
            var $tbodySummer = $('#tbody-summer');

            var $inputsWinter = $tbodyWinter.find('input');
            var $inputsSummer = $tbodySummer.find('input');

            if (season === 'summer') {
                $tbodyWinter.addClass('d-none');
                $inputsWinter.prop('disabled', true);

                $tbodySummer.removeClass('d-none');
                $inputsSummer.prop('disabled', false);
            } else {
                $tbodySummer.addClass('d-none');
                $inputsSummer.prop('disabled', true);

                $tbodyWinter.removeClass('d-none');
                $inputsWinter.prop('disabled', false);
            }
        }

        $('#season').on('change', function () {
            switchSeasonTables($(this).val());
        });

        // начальное состояние
        var initialSeason = $('#season').val() || 'winter';
        switchSeasonTables(initialSeason);

        @if(old('region_id'))
            $('#region_id').trigger('change');
        @endif
    });
</script>

@endpush
