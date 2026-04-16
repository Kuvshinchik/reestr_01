@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
{{-- style="margin-top: 30px;"--}}
<div class="row justify-content-center">
<a href="{{ route('home') }}">
    <div class="col-sm-12">
	
        <div class="page-title-box">
            <h4 class="mt-0 header-title text-center mb-4 text-uppercase">перейти на уровень выше</h4>
        </div>
    </div>
</div>
</a>
<div class="row justify-content-center">
   <div class="col-lg-10">
        <div class="card m-b-30">
             <div class="card-body">
                <h4 class="mt-0 header-title text-center mb-4">БЛОК РАЗВИТИЯ ПРОИЗВОДСТВА</h4>
                
                <div class="row">
                    <!-- Сезоны -->
                    <div class="col-md-6 col-lg-4 mb-4">
	<a href="{{ route('productionDevelopment.index', 'seasons') }}" class="btn btn-primary btn-lg btn-block py-4 d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
        <div class="mb-2"> <img src="{{ asset('assets/icons/ionicons/png/512/android-display.png') }}" alt="" height="36" style="filter:invert(1);"/>
        </div>
        <div>								
            <span>Сезоны</span>
        </div>
    </a>	
					
						
						
                    </div>                    
                    
                    
                    <!-- СКИМ -->
                    <div class="col-md-6 col-lg-4 mb-4">
						<a href="{{ route('productionDevelopment.index', 'ckim') }}" class="btn btn-primary btn-lg btn-block py-4 d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
							<div class="mb-2"> <img src="{{ asset('assets/icons/ionicons/png/512/settings.png') }}" alt="" height="36" style="filter:invert(1);"/>
							</div>
							<div>								
								<span>СКИМ</span>
							</div>
						</a>	
					
						
						
                    </div>
    
<!-- ПОЖАРКА 
                    <div class="col-md-6 col-lg-4 mb-4">
					<a href="#" class="btn btn-primary btn-lg btn-block py-4 d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
							<div class="mb-2"> <img src="{{ asset('assets/icons/ionicons/png/512/no-smoking.png') }}" alt="" height="36" style="filter:invert(1);"/>
							</div>
							<div>								
								<span>Пожарная безопасность</span>
							</div>
						</a>	
					
						
						
                    </div>	
                  -->  
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
