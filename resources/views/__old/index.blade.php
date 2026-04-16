@extends('layouts.admin')

@section('title', 'Дашборды ДЖВ')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="btn-group float-right">
                    <ol class="breadcrumb hide-phone p-0 m-0">
                        <li class="breadcrumb-item"><a href="#">ДЖВ</a></li>
                        <li class="breadcrumb-item active">ДАШБОРДЫ</li>
                    </ol>
                </div>
                <h4 class="page-title">Дашборды</h4>
            </div>
        </div>
    </div>
        
    @include('includes.dashboard')   
        
        
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/skycons/skycons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/raphael/raphael-min.js') }}"></script>
    <script src="{{ asset('assets/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ asset('assets/pages/dashborad.js') }}"></script>
    <script src="{{ asset('assets/pages/dashboard-itog.js') }}"></script>

    <script>
        /* BEGIN SVG WEATHER ICON */
        if (typeof Skycons !== 'undefined'){
            var icons = new Skycons({"color": "#fff"}, {"resizeClear": true}),
                list  = ["clear-day", "clear-night", "partly-cloudy-day", "partly-cloudy-night", "cloudy", "rain", "sleet", "snow", "wind", "fog"],
                i;
            for(i = list.length; i--; ) icons.set(list[i], list[i]);
            icons.play();
        };

        $(document).ready(function() {
            $("#boxscroll").niceScroll({cursorborder:"",cursorcolor:"#cecece",boxzoom:true});
            $("#boxscroll2").niceScroll({cursorborder:"",cursorcolor:"#cecece",boxzoom:true});
        });
    </script>
	
	<script>
    $(function () {

        // 1) Bar chart по РДЖВ
        $.getJSON('{{ route('api.zima.by-region') }}', function (data) {

            // data приходит в виде массива объектов:
            // [{rdzv: 'СЕВ', total_plan: 123, total_fact: 120}, ...]

            new Morris.Bar({
                element: 'zima-by-region',
                data: data,
                xkey: 'rdzv',
                ykeys: ['total_plan', 'total_fact'],
                labels: ['План', 'Факт'],
                hideHover: 'auto',
                resize: true
            });
        });

        // 2) Line chart по видам работ
        $.getJSON('{{ route('api.zima.by-work') }}', function (data) {

            // Виды работ длинные. Для реального проекта лучше сокращать,
            // либо брать топ N работ. Здесь берем как есть.

            new Morris.Line({
                element: 'zima-by-work',
                data: data,
                xkey: 'name_work',
                ykeys: ['total_plan', 'total_fact'],
                labels: ['План', 'Факт'],
                hideHover: 'auto',
                resize: true,
                xLabelAngle: 45 // подписи под углом, чтобы влезали
            });
        });

        // 3) Donut chart общий план / факт
        $.getJSON('{{ route('api.zima.summary') }}', function (row) {

            // row имеет вид:
            // { total_plan: 10000, total_fact: 9500 }

            var donutData = [
                { label: 'План', value: row.total_plan },
                { label: 'Факт', value: row.total_fact }
            ];

            new Morris.Donut({
                element: 'zima-summary-donut',
                data: donutData,
                resize: true
            });
        });

    });
</script>
@endpush