

<!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <button type="button" class="button-menu-mobile button-menu-mobile-topbar open-left waves-effect">
                    <i class="ion-close"></i>
                </button>

                <!-- LOGO -->
                <div class="topbar-left">
                    <div class="text-center">
                        <a href="{{ route('home') }}" class="logo"><i class="mdi mdi-train "></i> ДЖВ</a>
                    </div>
                </div>

                <div class="sidebar-inner slimscrollleft">
				{{--dd(route('productionDevelopment.index', ['slug' => 'index']));--}}
                    <div id="sidebar-menu">
                        <ul>
                           <li class="menu-title" ><a href="{{ route('productionDevelopment.index', ['slug' => 'index']) }}"><b>БЛОК РАЗВИТИЯ ПРОИЗВОДСТВА</b></a></li>

							<li class="has_sub">
								<a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i> <span> VKS </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
								<ul class="list-unstyled">
									<li><a href="{{ route('vksnew2.index') }}">VKSold</a></li>
									<li><a href="#">VKSnew</a></li>		
								</ul>
							</li>


							<li class="has_sub">
								<a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i> <span> Сезоны </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
								<ul class="list-unstyled">
									<li><a href="{{ route('productionDevelopment.index', ['slug' => 'zima']) }}">Зима</a></li>
									<li><a href="{{ route('productionDevelopment.index', ['slug' => 'zima']) }}">Лето</a></li>		
								</ul>
							</li>

							<li class="has_sub">
								<a href="{{ route('productionDevelopment.index', 'ckim') }}" class="waves-effect"><i class="mdi mdi-layers"></i> <span> СКИМ </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
								<ul class="list-unstyled">
									<li><a href="javascript:void(0);">Табло</a></li>
									<li><a href="javascript:void(0);">АРМ диктора</a></li>
									<li><a href="javascript:void(0);">Громкоговорители</a></li>
									<li><a href="javascript:void(0);">Часофикация</a></li>
								</ul>
							</li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i> <span> Пожарная безопасность </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="javascript:void(0);">Дашборд</a></li>
                                    <li><a href="javascript:void(0);">Формы</a></li>
                                    <li><a href="javascript:void(0);">Таблицы</a></li>
                                    <li><a href="javascript:void(0);">Аналитика</a></li>
                                </ul>
                            </li>
                        
<li class="menu-title" ><a href="{{ route('kadriMain') }}"><b>БЛОК ПО УПРАВЛЕНИЮ ПЕРСОНАЛОМ, ТРУДОВЫМИ РЕСУРСАМИ И ОРГАНИЗАЦИОННОЙ СТРУКТУРОЙ</b></a></li>
                            
							{{--							
							<li class="menu-title"><b>БЛОК ПО УПРАВЛЕНИЮ ПЕРСОНАЛОМ, ТРУДОВЫМИ РЕСУРСАМИ И ОРГАНИЗАЦИОННОЙ СТРУКТУРОЙ</b></li>
							--}}
                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i> <span> Вакцинация </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                                <ul class="list-unstyled">
									<li><a href="{{ route('worker.dashboard') }}">Дашборд</a></li>
                                    <li><a href="{{ route('worker.forma') }}">Формы</a></li>
                                    <li><a href="{{ route('worker.table') }}">Таблицы</a></li>
                                    <li><a href="{{ route('worker.analytics') }}">Аналитика</a></li>
                                </ul>
                            </li>
							<li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-layers"></i> <span> Первозимники </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                                <ul class="list-unstyled">
									<li><a href="{{ route('winter-worker-chart.index') }}">Дашборд</a></li>
                                    <li><a href="{{ route('worker.winter.create') }}">Формы</a></li>
                                    <li><a href="{{ route('winter-worker-table.index') }}">Таблицы</a></li>
                                    <li><a href="{{ route('winter-worker-analytics.index') }}">Аналитика</a></li>
                                </ul>
                            </li>				
                          


                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div> <!-- end sidebarinner -->
            </div>
            <!-- Left Sidebar End -->
@push('scripts')
 <script>			
			// Исправляем прямо сейчас
document.querySelectorAll('.menu-title a').forEach(link => {
    link.style.pointerEvents = 'auto';
    link.style.cursor = 'pointer';
});

// Или добавьте в CSS
var style = document.createElement('style');
style.innerHTML = `
    .menu-title a,
    .menu-title > a,
    #sidebar-menu .menu-title a {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
`;
document.head.appendChild(style);

 </script>
 @endpush
