

<div class="row"> 
 <!-- Column -->
<div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-3 align-self-center">
                            <div class="round">
                                <i class="mdi mdi-webcam"></i>
                            </div>
                        </div>
                        <div class="col-6 align-self-center text-center">
                            <div class="m-l-10">
                                <h5 class="mt-0 round-inner">{{count($rdzvList)}}</h5>
                                <p class="mb-0 text-muted">РДЖВ</p>
                            </div>
                        </div>
						{{--					
                        <div class="col-3 align-self-end align-self-center">
                            <h6 class="m-0 float-right text-center text-danger"> <i class="mdi mdi-arrow-down"></i> <span>5.26%</span></h6>
                        </div>
						--}}
                    </div>
                </div>
            </div>
</div>
<!-- Column -->
<!-- Column -->
<div class="col-md-6 col-lg-6 col-xl-3">
	<div class="card m-b-30">
		<div class="card-body">
			<div class="d-flex flex-row">
				<div class="col-3 align-self-center">
					<div class="round">
						<i class="mdi mdi-account-multiple-plus"></i>
					</div>
				</div>
				<div class="col-6 text-center align-self-center">
					<div class="m-l-10 ">
						<h5 class="mt-0 round-inner">{{count($vokzalList)+48}}</h5>
						<p class="mb-0 text-muted">вокзала</p>
					</div>
				</div>
				{{--
				<div class="col-3 align-self-end align-self-center">
					<h6 class="m-0 float-right text-center text-success"> <i class="mdi mdi-arrow-up"></i> <span>8.68%</span></h6>
				</div>
				--}}
			</div>
		</div>
	</div>
</div>
<!-- Column -->
<!-- Column -->
<div class="col-md-6 col-lg-6 col-xl-3">
	<div class="card m-b-30">
		<div class="card-body">
			<div class="d-flex flex-row">
				<div class="col-3 align-self-center">
					<div class="round ">
						<i class="mdi mdi-basket"></i>
					</div>
				</div>
				<div class="col-6 align-self-center text-center">
					<div class="m-l-10 ">
						<h5 class="mt-0 round-inner">1 028</h5>
						<p class="mb-0 text-muted">платформ</p>
					</div>
				</div>
				{{--
				<div class="col-3 align-self-end align-self-center">
					<h6 class="m-0 float-right text-center text-danger"> <i class="mdi mdi-arrow-down"></i> <span>2.35%</span></h6>
				</div>
				--}}
			</div>
		</div>
	</div>
</div>
<!-- Column -->
<!-- Column -->
<div class="col-md-6 col-lg-6 col-xl-3">
	<div class="card m-b-30">
		<div class="card-body">
			<div class="d-flex flex-row">
				<div class="col-3 align-self-center">
					<div class="round">
						<i class="mdi mdi-rocket"></i>
					</div>
				</div>
				<div class="col-6 align-self-center text-center">
					<div class="m-l-10">
						<h5 class="mt-0 round-inner">368</h5>
						<p class="mb-0 text-muted">зданий</p>
					</div>
				</div>
				{{--
				<div class="col-3 align-self-end align-self-center">
					<h6 class="m-0 float-right text-center text-success"> <i class="mdi mdi-arrow-up"></i> <span>2.35%</span></h6>
				</div>
				--}}
			</div>
		</div>
	</div>
</div>
<!-- Column -->
</div>



<!-- Фильтр по РДЖВ -->
<div class="form-group">
    <label for="rdzv-filter">Фильтр по РДЖВ:</label>
    <select id="rdzv-filter" class="form-control">
        <option value="">Все РДЖВ</option>
        @foreach($rdzvList as $rdzv)
            <option value="{{ $rdzv }}">{{ $rdzv }}</option>
        @endforeach
    </select>
</div>

<!-- Фильтр по вокзалам (изначально пустой) -->
<div class="form-group">
    <label for="vokzal-filter">Фильтр по вокзалу:</label>
    <select id="vokzal-filter" class="form-control" disabled>
        <option value="">Все вокзалы</option>
    </select>
</div>
							
							 
								@include('includes.dashboard_01')
								 
{{--                                @include('includes.dashboard_02')
								
                           
							
							@include('includes.dashboard_03')--}}
						
{{--dd(77777)--}}
                          
                                
                            
  