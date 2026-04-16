@extends('layouts.admin')

@section('title', 'Часофикация — Часы')

@section('content')

<div class="row justify-content-center">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="btn-group float-right">
                <ol class="breadcrumb hide-phone p-0 m-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('productionDevelopment.index', 'index') }}">Развитие производства</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('productionDevelopment.index', 'ckim') }}">СКИМ</a>
                    </li>
                    <li class="breadcrumb-item active">Часофикация</li>
                </ol>
            </div>
            <h4 class="page-title">Часофикация</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">

                <h4 class="mt-0 header-title mb-4">Часы (СКИМ)</h4>

                <!-- ===================== ФИЛЬТРЫ ===================== -->
                <div class="row mb-4">

                    <!-- РДЖВ -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Выберите РДЖВ</label>
                            <select id="regionSelect" class="form-control">
                                <option value="">-- выберите --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- ВОКЗАЛ -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Выберите вокзал</label>
                            <select id="stationSelect" class="form-control" disabled>
                                <option value="">-- сначала выберите РДЖВ --</option>
                            </select>
                        </div>
                    </div>

                    <!-- ЧАСЫ -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Выберите часы</label>
                            <select id="clockSelect" class="form-control" disabled>
                                <option value="">-- сначала выберите вокзал --</option>
                            </select>
                        </div>
                    </div>

                </div>
                <!-- /ФИЛЬТРЫ -->
				<div class="row mb-6">
				<!-- ===================== КАРУСЕЛЬ ===================== -->
				<div class="col-lg-6">
                <div id="clockCarouselWrapper" style="display:none;">

                    <div id="clockCarousel" class="carousel slide" data-ride="false">

                        <!-- Индикаторы -->
                        <ol class="carousel-indicators" id="carouselIndicators"></ol>

                        <div class="carousel-inner" id="carouselInner"></div>

                        <a class="carousel-control-prev" href="#clockCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Назад</span>
                        </a>
                        <a class="carousel-control-next" href="#clockCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Вперёд</span>
                        </a>
                    </div>

                    <p class="text-center text-muted mt-2" id="photoCounter"></p>
                </div>
				</div>
                <!-- /КАРУСЕЛЬ -->

				

                <!-- ===================== ИНФО-КАРТОЧКА ===================== -->
				<div class="col-lg-6">
					<div id="clockInfoCard" class="alert alert-info" style="display:none;">
						<div class="row">
							<div class="col-md-3"><strong>ID часов:</strong></div>
							<div class="col-md-9" id="infoId">—</div>
						</div>
						<div class="row mt-1">
							<div class="col-md-3"><strong>Тип:</strong></div>
							<div class="col-md-9" id="infoType">—</div>
						</div>
						<div class="row mt-1">
							<div class="col-md-3"><strong>Описание:</strong></div>
							<div class="col-md-9" id="infoDescription">—</div>
						</div>
						<div class="row mt-1">
							<div class="col-md-3"><strong>Год поставки:</strong></div>
							<div class="col-md-9" id="infoYear">—</div>
						</div>
					</div>
				</div>
                <!-- ===================== /ИНФО-КАРТОЧКА ===================== -->
				
				 <!-- ФОРМА -->
        <div id="cardInfoCard"  class="card col-lg-12" style="display:none;">
            <div class="card-body">
                <h5>Сообщить о неисправности</h5>

                <form id="issueForm">
                    @csrf

                    <input type="hidden" name="clock_id" id="clock_id">

                    <div class="mb-3">
                        <label>Описание проблемы</label>
                        <textarea name="message" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Ответственный сотрудник</label>
                        <input type="text" name="user" class="form-control" required>
                    </div>

                    <button class="btn btn-danger">Отправить</button>
                </form>
            </div>
        </div>
				
			</div>	
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
$(document).ready(function () {

    var IMG_BASE   = '/assets/images/productionDevelopment/clock';
    var TEMP_IMG   = IMG_BASE + '/temp_img.png';
    var MAX_PHOTOS = 20;

    // =========================================================
    // РЕГИОН → ВОКЗАЛЫ
    // =========================================================
    $('#regionSelect').on('change', function () {
        var regionId = $(this).val();

        resetSelect('#stationSelect', '-- выберите --', true);
        resetSelect('#clockSelect', '-- сначала выберите вокзал --', true);
        hideInfo();

        if (!regionId) return;

        $('#stationSelect').html('<option>Загрузка...</option>').prop('disabled', true);

        $.getJSON('/api/stations/' + regionId, function (data) {
            var html = '<option value="">-- выберите --</option>';
            $.each(data, function (i, station) {
                html += '<option value="' + station.id + '">' + station.name + '</option>';
            });
            $('#stationSelect').html(html).prop('disabled', false);
        }).fail(function () {
            $('#stationSelect').html('<option value="">Ошибка загрузки</option>');
        });
    });


    // =========================================================
    // ВОКЗАЛ → ЧАСЫ
    // =========================================================
    $('#stationSelect').on('change', function () {
        var stationId = $(this).val();

        resetSelect('#clockSelect', '-- выберите --', true);
        hideInfo();

        if (!stationId) return;

        $('#clockSelect').html('<option>Загрузка...</option>').prop('disabled', true);

        $.getJSON('/api/clocks/' + stationId, function (data) {
            if (!data.length) {
                $('#clockSelect').html('<option value="">Часы не найдены</option>');
                return;
            }

            // Группируем по типу
            var groups = {};
            $.each(data, function (i, clock) {
                var type = clock.type || 'прочие';
                if (!groups[type]) groups[type] = [];
                groups[type].push(clock);
            });

            var html = '<option value="">-- выберите --</option>';
            $.each(groups, function (type, clocks) {
                var label = type.charAt(0).toUpperCase() + type.slice(1);
                html += '<optgroup label="' + label + '">';
                $.each(clocks, function (i, clock) {
                    html += '<option value="' + clock.id + '"'
                          + ' data-type="'        + escAttr(clock.type        || '') + '"'
                          + ' data-description="' + escAttr(clock.description || 'нет описания') + '"'
                          + ' data-year="'        + escAttr(clock.supply_year || '—') + '"'
                          + '>ID ' + clock.id + '</option>';
                });
                html += '</optgroup>';
            });

            $('#clockSelect').html(html).prop('disabled', false);
        }).fail(function () {
            $('#clockSelect').html('<option value="">Ошибка загрузки</option>');
        });
    });


    // =========================================================
    // ЧАСЫ → ИНФО + КАРУСЕЛЬ
    // =========================================================
    $('#clockSelect').on('change', function () {
        var clockId = $(this).val();
        hideInfo();

        if (!clockId) return;

        var opt = $(this).find('option:selected');
        $('#infoId').text(clockId);
        $('#infoType').text(opt.data('type') || '—');
        $('#infoDescription').text(opt.data('description') || '—');
        $('#infoYear').text(opt.data('year') || '—');
        $('#clockInfoCard').show();
		$('#cardInfoCard').show();

        loadCarousel(clockId);
    });


    // =========================================================
    // ЗАГРУЗКА КАРУСЕЛИ
    // =========================================================
    function loadCarousel(clockId) {
        var basePath   = IMG_BASE + '/' + clockId;
        var loadedSrcs = [];
        var checked    = 0;

        for (var i = 1; i <= MAX_PHOTOS; i++) {
            (function (num) {
                var src = basePath + '/' + num + '.jpg';
                var img = new Image();

                img.onload = function () {
                    loadedSrcs.push({ index: num, src: src });
                    checked++;
                    if (checked === MAX_PHOTOS) renderCarousel(loadedSrcs);
                };

                img.onerror = function () {
                    checked++;
                    if (checked === MAX_PHOTOS) renderCarousel(loadedSrcs);
                };

                img.src = src;
            })(i);
        }
    }

    function renderCarousel(srcs) {
        srcs.sort(function (a, b) { return a.index - b.index; });

        var useFallback = srcs.length === 0;
        var images      = useFallback ? [{ src: TEMP_IMG, index: 0 }] : srcs;

        var innerHtml     = '';
        var indicatorHtml = '';

        $.each(images, function (pos, item) {
            var activeClass = pos === 0 ? ' active' : '';
            var altText     = useFallback ? 'Фото отсутствует' : 'Фото ' + item.index;

            indicatorHtml +=
                '<li data-target="#clockCarousel" data-slide-to="' + pos + '" class="' + activeClass.trim() + '"></li>';

            innerHtml +=
                '<div class="carousel-item' + activeClass + '">' +
                    '<img src="' + item.src + '" class="d-block w-100 clock-carousel-img"' +
                         ' alt="' + altText + '"' +
                         ' onerror="this.onerror=null;this.src=\'' + TEMP_IMG + '\'">' +
                '</div>';
        });

        $('#carouselIndicators').html(indicatorHtml);
        $('#carouselInner').html(innerHtml);
        $('#photoCounter').text(
            useFallback ? 'Фотографии отсутствуют' : 'Фотографий: ' + images.length
        );

        // Bootstrap 4 jQuery API (в проекте используется Bootstrap 4 — jquery.min.js + bootstrap.min.js)
        $('#clockCarousel').carousel({ interval: false, wrap: true });
        $('#clockCarousel').carousel(0);

        $('#clockCarouselWrapper').show();
    }


    // =========================================================
    // ВСПОМОГАТЕЛЬНЫЕ
    // =========================================================
    function resetSelect(selector, placeholder, disabled) {
        $(selector)
            .html('<option value="">' + placeholder + '</option>')
            .prop('disabled', disabled);
    }

    function hideInfo() {
        $('#clockInfoCard').hide();
        $('#clockCarouselWrapper').hide();
        $('#carouselInner').html('');
        $('#carouselIndicators').html('');
        $('#photoCounter').text('');
    }

    // Экранирование для data-атрибутов
    function escAttr(str) {
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#39;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;');
    }

});
</script>
@endpush


@push('styles')
<style>
    .clock-carousel-img {
        max-height: 500px;
        object-fit: contain;
        background: #f4f4f4;
    }

    #clockCarousel {
        background: #f4f4f4;
        border-radius: 6px;
        overflow: hidden;
    }

    /* Тёмные стрелки — карусель на светлом фоне */
    #clockCarousel .carousel-control-prev-icon,
    #clockCarousel .carousel-control-next-icon {
        filter: invert(1);
    }

    #clockCarousel .carousel-indicators li {
        background-color: #555;
    }
</style>
@endpush
