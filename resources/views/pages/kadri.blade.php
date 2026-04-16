@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
{{----}}
<div class="row justify-content-center">
<a href="{{ route('home') }}">
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
                <h4 class="mt-0 header-title text-center mb-4">БЛОК ПО УПРАВЛЕНИЮ ПЕРСОНАЛОМ, ТРУДОВЫМИ РЕСУРСАМИ И ОРГАНИЗАЦИОННОЙ СТРУКТУРОЙ</h4>
                
                <div class="row">
                    <!-- Блок развития производства -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="{{ route('kadriVakcinacia') }}" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Вакцинация</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Блок по управлению персоналом -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="{{ route('kadriWinterWorker') }}" class="btn btn-primary btn-lg btn-block py-4 d-flex align-items-center justify-content-center text-center" style="min-height: 150px; white-space: normal;">
                            <div>
                                <i class="d-block mb-2"></i>
                                <span>Первозимники</span>
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
