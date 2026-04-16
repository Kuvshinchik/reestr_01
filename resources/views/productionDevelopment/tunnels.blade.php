@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
{{----}}
<div class="row justify-content-center">
<a href="{{ route('productionDevelopment.index', ['slug' => 'zima']) }}">
    <div class="col-sm-12">
	
        <div class="page-title-box">
            <h4 class="mt-0 header-title text-center mb-4 text-uppercase">перейти на уровень выше</h4>
        </div>
    </div>
</a>
</div>
<div class="row justify-content-center">
   <div class="col-lg-10">
        <div class="card m-b-30">
             <div class="card-body">			 
				<div class="row">
					<div class="col-sm-12">
						<div class="page-title-box">
							<div class="btn-group float-right">
								<ol class="breadcrumb hide-phone p-0 m-0">
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', ['slug' => 'index']) }}">БЛОК РАЗВИТИЯ ПРОИЗВОДСТВА</a></li>
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', 'seasons') }}">Сезоны</a></li>
									<li class="breadcrumb-item"><a href="{{ route('productionDevelopment.index', ['slug' => 'zima']) }}">Зима</a></li>
									<li class="breadcrumb-item active">Пешеходные тоннели</li>
									
									
									
								</ol>
							</div>
							<h4 class="page-title">Пешеходные тоннели</h4>
						</div>
					</div>
				</div>
				
				
				
               
                <div class="row">
                    <!-- Блок развития производства -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Дашборды</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Блок по управлению персоналом -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Формы</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Блок по реконструкции и инвестициям -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Таблицы</span>
                            </div>
                        </a>
                    </div>
                    
                    
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Аналитика</span>
                            </div>
                        </a>
                    </div>
                </div>
				
                
             </div>
        </div>
   </div>
</div>
@endsection

@push('styles')
<style>
    .btn-block {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .btn-block:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .btn-block span {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }
    
    .mdi-36px {
        font-size: 36px;
    }
</style>
@endpush
