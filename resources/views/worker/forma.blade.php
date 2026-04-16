@extends('layouts.admin')

@section('title', 'Отметка вакцинации')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center">
            <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                <i class="mdi mdi-assistant"></i> ДЖВ
            </a>
            <h4 class="page-title mb-0">Отметка о вакцинации</h4>
        </div>
    </div>
</div>

<!-- Навигация -->
<div class="row mb-4">
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

<!-- Форма отметки вакцинации -->
<div class="row">
    <div class="col-lg-8 col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="mdi mdi-needle text-success mr-2"></i>
                    Статус вакцинации
                </h4>

                <form action="#" method="POST">
                    @csrf

                    <div class="form-group form-check mb-4">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="vaccinated" 
                            name="vaccinated"
                            value="1"
                            style="width: 20px; height: 20px; cursor: pointer;"
                        >
                        <label class="form-check-label ml-2" for="vaccinated" style="font-size: 18px;">
                            Вакцинация пройдена
                        </label>
                    </div>

                    <div class="text-right">
					{{--       <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="mdi mdi-content-save mr-2"></i>
                            Сохранить
                        </button>
						class="d-flex align-items-center text-dark"
					--}}	
						<a href="#" class="btn btn-success btn-lg px-5">
                            <i class="mdi mdi-content-save mr-2"></i>
                            Сохранить
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

    /* Улучшение внешнего вида чекбокса */
    .form-check-input:checked {
        background-color: #1dc9b7;
        border-color: #1dc9b7;
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(29, 201, 183, 0.25);
    }
</style>
@endpush