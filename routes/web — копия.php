<?php

use App\Http\Controllers\AuthController;
//use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvitationRegisterController;
use App\Http\Controllers\PreparationDataController;
use App\Http\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\productionDevelopment\ProductionDevelopmentController;
use App\Http\Controllers\WinterTableController;
use App\Http\Controllers\WinterChartController;
use App\Http\Controllers\WinterAnalyticsController;
use App\Http\Controllers\WinterWorkerController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Гостевые маршруты
Route::middleware('guest')->group(function () {
    // Аутентификация
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->name('login.post');
        Route::get('/first-user', 'showFirstUserForm')->name('first-user');
        Route::post('/first-user', 'storeFirstUser')->name('first-user.post');
    });

    // Регистрация по приглашению
    Route::controller(InvitationRegisterController::class)
        ->prefix('register/invite/{token}')
        ->group(function () {
            Route::get('/', 'show')->name('register.invited');
            Route::post('/', 'store')->name('register.invited.store');
        });
});

// Авторизованные маршруты
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => view('home'))->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

   // --- НОВАЯ ГРУППА: ПЕРВОЗИМНИКИ ---
    // Здесь мы группируем всё, что касается зимнего периода
    Route::controller(WinterWorkerController::class)
        ->prefix('worker/winter') // URL будет: site.com/worker/winter/add
        ->name('worker.winter.')  // Имя роута: worker.winter.create
        ->group(function () {
            // Форма добавления (GET)
            Route::get('/add', 'create')->name('create');
            // Сохранение данных (POST)
            Route::post('/add', 'store')->name('store');
        });

Route::get('/preparation-data/create', [PreparationDataController::class, 'create'])
    ->name('preparation-data.create');

Route::post('/preparation-data', [PreparationDataController::class, 'store'])
    ->name('preparation-data.store');

Route::get('/preparation-data/stations/{region}', [PreparationDataController::class, 'stations']);

   Route::prefix('productionDevelopment')
    ->name('productionDevelopment.')
    ->group(function () {    
		Route::get('/{slug}', [ProductionDevelopmentController::class, 'index'])
             ->name('index');			 
    });
	
// Диаграмма подготовки к зиме
Route::controller(WinterChartController::class)
    ->prefix('winter-chart')
    ->name('winter-chart.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/chart-data', 'chartData')->name('chart-data');
    });

// Таблица подготовки к зиме
Route::controller(WinterTableController::class)
    ->prefix('winter-table')
    ->name('winter-table.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
    });
	
// Аналитическая записка по подготовке к зиме
Route::controller(WinterAnalyticsController::class)
    ->prefix('winter-analytics')
    ->name('winter-analytics.')
    ->group(function () {
        
        // GET /winter-analytics
        // Показывает страницу с аналитической запиской
        // Параметры: ?region_id=all|{id} &category_id=all|{id}
        Route::get('/', 'index')->name('index');
    });

	


    // Worker
    Route::controller(WorkerController::class)
        ->prefix('worker')
        ->name('worker.')
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/table', 'table')->name('table');
            Route::get('/analytics', 'analytics')->name('analytics');
			Route::get('/forma', 'forma')->name('forma');
            Route::get('/get-vokzals', 'getVokzals')->name('get-vokzals'); // AJAX для фильтра
			Route::get('/vaccination-chart-data', 'getVaccinationChartData')->name('vaccination-chart-data');
			Route::get('/export-vaccination', [WorkerController::class, 'exportVaccination'])->name('export.vaccination');
        });

	
    // Статические страницы
    Route::prefix('pages')->group(function () {
        Route::view('/kadri', 'pages.kadri')->name('kadriMain');
        Route::view('/kadriVakcinacia', 'pages.kadriVakcinacia')->name('kadriVakcinacia');
    });

    // Админ-панель приглашений
    Route::middleware('admin-by-name')
        ->controller(InvitationController::class)
        ->prefix('invitations')
        ->name('invitations.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
});
