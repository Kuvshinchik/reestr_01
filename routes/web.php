<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\WinterChartController;
use App\Http\Controllers\WinterTableController;
use App\Http\Controllers\WinterWorkerController;
use App\Http\Controllers\PreparationDataController;
use App\Http\Controllers\WinterAnalyticsController;
use App\Http\Controllers\InvitationRegisterController;
use App\Http\Controllers\productionDevelopment\ProductionDevelopmentController;
use App\Http\Controllers\WinterWorkerChartController;
use App\Http\Controllers\WinterWorkerTableController;
use App\Http\Controllers\WinterWorkerAnalyticsController;
use App\Http\Controllers\Admin\UserVisitsController;
use App\Models\Station;
use App\Models\ProductionDevelopment\Clock\Clock;
use App\Http\Controllers\VksController\vksnew2;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// ГОСТЕВЫЕ МАРШРУТЫ (GUEST)
// =========================================================================
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
        ->name('register.invited') // register.invited и register.invited.store
        ->group(function () {
            Route::get('/', 'show');
            Route::post('/', 'store')->name('.store');
        });
});

// =========================================================================
// АВТОРИЗОВАННЫЕ МАРШРУТЫ (AUTH)
// =========================================================================
// Главная и Выход
Route::middleware(['auth', 'track.visits'])->group(function () {
    
    Route::get('/', fn() => view('home'))->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
	
	
	

    // ---------------------------------------------------------------------
    // МОДУЛЬ: ПОДГОТОВКА К ЗИМЕ (WINTER)
    // ---------------------------------------------------------------------
    
    // Первозимники (Winter Worker) 
	
	// Диаграмма winter-worker-chart.index
	
	Route::controller(WinterWorkerChartController::class)
    ->prefix('winter-worker-chart')
    ->name('winter-worker-chart.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/chart-data', 'chartData')->name('chart-data');
        Route::get('/table-data', 'tableData')->name('table-data');  // НОВЫЙ
        Route::get('/export', 'export')->name('export');              // НОВЫЙ
    });

	
		
	 // форма
    Route::controller(WinterWorkerController::class)
        ->prefix('worker/winter')
        ->name('worker.winter.')
        ->group(function () {
            Route::get('/add', 'create')->name('create');
            Route::post('/add', 'store')->name('store');
        });
		
	// Таблица winter-worker-table.index
	Route::controller(WinterWorkerTableController::class)
		->prefix('winter-worker-table')
		->name('winter-worker-table.')
		->group(function () {
			Route::get('/', 'index')->name('index');
			Route::get('/table-data', 'tableData')->name('table-data');
			Route::get('/export', 'export')->name('export');
		});
	
	// ---------------------------------------------------------------------
    // Аналитика первозимников (Winter Worker Analytics) winter-worker-analytics.index
    // ---------------------------------------------------------------------
    Route::controller(WinterWorkerAnalyticsController::class)
        ->prefix('winter-worker-analytics')
        ->name('winter-worker-analytics.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });
		
		
 // ---------------------------------------------------------------------
    // Диаграмма подготовки (Winter Chart)
    Route::controller(WinterChartController::class)
        ->prefix('winter-chart')
        ->name('winter-chart.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/chart-data', 'chartData')->name('chart-data');
        });

    // Таблица подготовки (Winter Table)
    Route::controller(WinterTableController::class)
        ->prefix('winter-table')
        ->name('winter-table.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export', 'export')->name('export');
        });

    // Аналитика (Winter Analytics)
    Route::controller(WinterAnalyticsController::class)
        ->prefix('winter-analytics')
        ->name('winter-analytics.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });

    // ---------------------------------------------------------------------
    // МОДУЛЬ: КАДРЫ И СОТРУДНИКИ (WORKER)
    // ---------------------------------------------------------------------
    Route::controller(WorkerController::class)
        ->prefix('worker')
        ->name('worker.')
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/table', 'table')->name('table');
            Route::get('/analytics', 'analytics')->name('analytics');
            Route::get('/forma', 'forma')->name('forma');
            
            // AJAX и Экспорт
            Route::get('/get-vokzals', 'getVokzals')->name('get-vokzals');
            Route::get('/vaccination-chart-data', 'getVaccinationChartData')->name('vaccination-chart-data');
            Route::get('/export-vaccination', 'exportVaccination')->name('export.vaccination');
        });

    // ---------------------------------------------------------------------
    // ДАННЫЕ ДЛЯ ПОДГОТОВКИ (PREPARATION DATA)
    // ---------------------------------------------------------------------
    Route::controller(PreparationDataController::class)
        ->prefix('preparation-data')
        ->name('preparation-data.') // preparation-data.create, etc.
        ->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/stations/{region}', 'stations')->name('stations');
        });

    // ---------------------------------------------------------------------
    // ПРОИЗВОДСТВЕННОЕ РАЗВИТИЕ
    // ---------------------------------------------------------------------
    Route::controller(ProductionDevelopmentController::class)
        ->prefix('productionDevelopment')
        ->name('productionDevelopment.')
        ->group(function () {    
             // Вложенные (2 уровня)
        Route::get('/{folder}/{page}', 'index')->name('inner');

        // Обычные (1 уровень)
        Route::get('/{slug}', 'index')->name('index');
        });

    // ---------------------------------------------------------------------
    // СТАТИЧЕСКИЕ СТРАНИЦЫ
    // ---------------------------------------------------------------------
    Route::prefix('pages')->group(function () {
        Route::view('/kadri', 'pages.kadri')->name('kadriMain');
        Route::view('/kadriVakcinacia', 'pages.kadriVakcinacia')->name('kadriVakcinacia');
		Route::view('/kadriWinterWorker', 'pages.kadriWinterWorker')->name('kadriWinterWorker');
    });

    // ---------------------------------------------------------------------
    // АДМИН-ПАНЕЛЬ (INVITATIONS)
    // ---------------------------------------------------------------------
    Route::middleware('admin-by-name')
        ->controller(InvitationController::class)
        ->prefix('invitations')
        ->name('invitations.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
        });
		
		
		
	// -----------------------------------------------------------------
    // МОДУЛЬ: ВКС (Видеоконференцсвязь)
    // -----------------------------------------------------------------
    Route::controller(vksnew2::class)
        ->prefix('vks')
        ->name('vksnew2.')
        ->middleware('auth')
        ->group(function () {
           
			Route::get('/',              'index')->name('index');
            Route::get('/view/{id}',    'view')->name('view');
            Route::get('/add',          'addForm')->name('add');
            Route::post('/add',         'addStore')->name('store');
            Route::post('/delete/{id}', 'delete')->name('delete');
            Route::get('/closestatus',  'closeStatus')->name('closestatus');
            Route::get('/recipient',    'recipientForm')->name('recipient');
            Route::post('/recipient',   'recipientSave')->name('recipient.save');
	 /*		
	Route::get('/',              [vksnew2::class, 'index'])->name('index');
    Route::get('/view/{id}',    [vksnew2::class, 'view'])->name('view');
    Route::get('/add',          [vksnew2::class, 'addForm'])->name('add');
    Route::post('/add',         [vksnew2::class, 'addStore'])->name('store');
    Route::post('/delete/{id}', [vksnew2::class, 'delete'])->name('delete');
    Route::get('/closestatus',  [vksnew2::class, 'closeStatus'])->name('closestatus');
    Route::get('/recipient',    [vksnew2::class, 'recipientForm'])->name('recipient');
    Route::post('/recipient',   [vksnew2::class, 'recipientSave'])->name('recipient.save');
			
	*/		
        });	
		
});

Route::middleware(['auth', 'admin.by.name'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::controller(UserVisitsController::class)
        ->prefix('visits')
        ->name('visits.')
        ->group(function () {
            
            // === ОСНОВНЫЕ СТРАНИЦЫ ===
            Route::get('/', 'index')->name('index');
            Route::get('/online', 'online')->name('online');
            Route::get('/export/excel', 'export')->name('export');
            
            // === ИСКЛЮЧЕНИЯ ===
            Route::get('/exclusions', 'exclusions')->name('exclusions');
            Route::post('/exclusions/add', 'addExclusion')->name('exclusions.add');
            Route::delete('/exclusions/{exclusion}', 'removeExclusion')->name('exclusions.remove');
            Route::post('/exclusions/{exclusion}/toggle', 'toggleExclusion')->name('exclusions.toggle');
            
            // === ОЧИСТКА ДАННЫХ ===
            Route::get('/cleanup', 'cleanup')->name('cleanup');
            Route::get('/cleanup/preview', 'cleanupPreview')->name('cleanup.preview');
            Route::post('/cleanup/execute', 'cleanupExecute')->name('cleanup.execute');
            Route::post('/cleanup/old', 'cleanupOld')->name('cleanup.old');
            Route::post('/cleanup/user/{user}', 'deleteUserData')->name('cleanup.user');
            
            // === ДЕЙСТВИЯ С СЕССИЯМИ ===
            Route::post('/close-stale', 'closeStaleSessions')->name('close-stale');
            Route::post('/{visit}/end', 'endSession')->name('end-session');
            
            // === ПРОСМОТР (в конце, т.к. {visit} ловит всё) ===
            Route::get('/user/{user}', 'userHistory')->name('user-history');
            Route::get('/{visit}', 'show')->name('show');
        });
});

Route::get('/api/stations/{region}', function($id) {
    return \App\Models\Station::where('region_id', $id)->get();
});

Route::get('/api/clocks/{station}', function($id) {
    return \App\Models\Clock::where('station_id', $id)->get();
});


/*
Route::get('/api/clock/{id}', function($id) {
    return \App\Models\Clock::with('images')->find($id);
});
*/


Route::post('/api/clock-issue', function(Request $request) {
    \App\Models\ClockIssue::create($request->all());
    return response()->json(['ok' => true]);
});


// =============================================================================
// Добавить в конец web.php
// Заменяет ранее существующие /api/stations и /api/clocks маршруты
// =============================================================================


// Вокзалы по региону
Route::get('/api/stations/{region}', function ($id) {
    return Station::where('region_id', $id)
        ->orderBy('name')
        ->get(['id', 'name']);
});

// Часы по вокзалу
Route::get('/api/clocks/{station}', function ($id) {
    return Clock::where('station_id', $id)
        ->orderBy('type')
        ->orderBy('id')
        ->get(['id', 'type', 'description', 'supply_year']);
});

// Одни часы по ID
Route::get('/api/clock/{id}', function ($id) {
    $clock = Clock::find($id);
    if (!$clock) {
        return response()->json(['error' => 'Не найдено'], 404);
    }
    return $clock;
});



