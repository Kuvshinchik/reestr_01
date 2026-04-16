@extends('layouts.admin')

@section('title', 'Аналитика')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center">
            <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                <i class="mdi mdi-assistant"></i> ДЖВ
            </a>
            <h4 class="page-title mb-0">Аналитика</h4>
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

<!-- Блок с изображениями аналитики -->
<div class="row">
    <div class="col-12">
        <h4 class="mt-0 header-title mb-4">
            <i class="mdi mdi-chart-areaspline text-primary mr-2"></i>
            Аналитика вакцинации
        </h4>
    </div>
</div>

<div class="row justify-content-center">
    @for($i = 1; $i <= 5; $i++)
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 text-center">
                    <img src="{{ asset('assets/images/analitika/analitika_0' . $i . '.jpg') }}" 
                         alt="Аналитика {{ $i }}" 
                         class="img-fluid rounded" 
                         style="max-height: 800px; width: auto; height: auto;">
                </div>
            </div>
        </div>
        @if($i % 3 === 0 && $i < 5)</div><div class="row justify-content-center">@endif
    @endfor
</div>

@endsection

@push('styles')
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
    
    .badge-soft-success {
        background-color: rgba(29, 201, 183, 0.15);
        color: #1dc9b7;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Дополнительно для лучшей читаемости изображений */
    .card img {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush