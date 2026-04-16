@extends('layouts.admin')

@section('title', 'Регистрация обучения первозимника')

@section('content')
<div class="row justify-content-center">
<a href="{{ route('kadriWinterWorker') }}">
    <div class="col-sm-12">	
        <div class="page-title-box">
            <h4 class="mt-0 header-title text-center mb-4 text-uppercase">перейти на уровень выше</h4>
        </div>
    </div>
</a>	
</div>
<!--
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-flex align-items-center">
            {{-- Ссылка на главную (логотип) --}}
            <a href="{{ route('home') }}" class="logo mr-3" style="font-size: 24px; color: #5b73e8; text-decoration: none;">
                <i class="mdi mdi-train"></i> ДЖВ
            </a><br>
            <h4 class="page-title mb-0">Регистрация обучения (Первозимники)</h4>
        </div>
    </div>
</div>
-->
<div class="row mb-4 justify-content-center">
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card m-b-30">
                    <div class="card-body">
                        <a href="{{ route('winter-worker-chart.index') }}" class="d-flex align-items-center text-dark">
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

            <div class="col-md-6">
                <div class="card m-b-30">
                    <div class="card-body">
                        <a href="{{ route('winter-worker-table.index') }}" class="d-flex align-items-center text-dark">
                            <div class="mr-3">
                                <i class="mdi mdi-arrow-left-circle mdi-36px text-primary"></i>
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
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Ввод данных.</h4>
				{{--                <p class="text-muted m-b-30 font-14">
                    Введите данные.
                </p>

                 Вывод ошибок валидации --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('worker.winter.store') }}">
                    @csrf 
{{--  				
					<div class="form-group">
                        <label for="personnel_number" style="font-size: 16px;">ID сотрудника</label>
                        <input class="form-control form-control-lg" 
                               type="text" 
                               placeholder="Например: 123456" 
                               id="personnel_number" 
                               name="personnel_number" 
                               required>
                    </div>
--}}	
                    <div class="form-group">
                        <label for="hired_at" style="font-size: 16px;">Дата приема на работу</label>
                        <input class="form-control form-control-lg" 
                               type="date" 
                               id="hired_at" 
                               name="hired_at" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="trained_at" style="font-size: 16px;">Дата прохождения обучения (если пройдено)</label>
                        <input class="form-control form-control-lg" 
                               type="date" 
                               id="trained_at" 
                               name="trained_at">
                        <small class="form-text text-muted">Оставьте пустым, если сотрудник еще не прошел обучение.</small>
                    </div>

                    <hr class="mt-4 mb-4">

                    <div class="text-right">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="mdi mdi-content-save mr-2"></i>
                            Сохранить данные
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mdi-36px { font-size: 36px; }
    .logo:hover { opacity: 0.8; }
</style>
@endpush