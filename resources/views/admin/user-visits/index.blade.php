{{-- 
    Файл: resources/views/admin/user-visits/index.blade.php
    Админ-панель учёта посещений (v2)
--}}
@extends('layouts.admin')

@section('title', 'Учёт посещений')

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="mdi mdi-account-clock mr-2"></i>Учёт посещений сайта</h2>
            <p class="text-muted">Мониторинг активности зарегистрированных пользователей</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.visits.online') }}" class="btn btn-info mr-1">
                <i class="mdi mdi-account-multiple"></i> Онлайн ({{ $activeSessions->count() }})
            </a>
            <a href="{{ route('admin.visits.exclusions') }}" class="btn btn-warning mr-1">
                <i class="mdi mdi-account-off"></i> Исключения
                @if($exclusionsCount > 0)
                    <span class="badge badge-light">{{ $exclusionsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.visits.cleanup') }}" class="btn btn-danger mr-1">
                <i class="mdi mdi-delete-sweep"></i> Очистка
            </a>
            <a href="{{ route('admin.visits.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success">
                <i class="mdi mdi-file-excel"></i> Экспорт
            </a>
        </div>
    </div>

    {{-- ===== СТАТИСТИКА ===== --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statistics['total_visits']) }}</h3>
                    <small>Всего посещений</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statistics['unique_users']) }}</h3>
                    <small>Уникальных польз.</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statistics['total_pages']) }}</h3>
                    <small>Просмотров стр.</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ round($statistics['avg_pages'], 1) }}</h3>
                    <small>Ср. страниц/сессию</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    @php
                        $avgMin = round($statistics['avg_duration'] / 60, 1);
                    @endphp
                    <h3 class="mb-0">{{ $avgMin }} мин</h3>
                    <small>Ср. длительность</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statistics['active_sessions'] }}</h3>
                    <small>Активных сессий</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ФИЛЬТРЫ ===== --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="mdi mdi-filter mr-1"></i> Фильтры
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visits.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Дата с:</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Дата по:</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Пользователь:</label>
                        <select name="user_id" class="form-select">
                            <option value="">Все пользователи</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">IP-адрес:</label>
                        <input type="text" name="ip_address" class="form-control" 
                               value="{{ $ipAddress }}" placeholder="192.168...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="mdi mdi-magnify"></i> Применить
                        </button>
                        <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Сбросить
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- ===== АКТИВНЫЕ СЕССИИ ===== --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="mdi mdi-access-point mr-1"></i> Активные сессии (сейчас онлайн)
                    <form action="{{ route('admin.visits.close-stale') }}" method="POST" class="float-right d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light">
                            <i class="mdi mdi-broom"></i> Закрыть устаревшие
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if($activeSessions->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="mdi mdi-account-off mdi-48px"></i>
                            <p>Нет активных сессий</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Пользователь</th>
                                        <th>IP</th>
                                        <th>Посл. активность</th>
                                        <th>Страниц</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSessions as $session)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.visits.user-history', $session->user_id) }}">
                                                    {{ $session->user_name }}
                                                </a>
                                            </td>
                                            <td><code>{{ $session->ip_address }}</code></td>
                                            <td>
                                                <small>{{ $session->session_end ? $session->session_end->diffForHumans() : '-' }}</small>
                                            </td>
                                            <td>{{ $session->pages_count }}</td>
                                            <td>
                                                <a href="{{ route('admin.visits.show', $session) }}" 
                                                   class="btn btn-xs btn-outline-primary">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== ТОП ПОЛЬЗОВАТЕЛЕЙ ===== --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="mdi mdi-trophy mr-1"></i> Топ-10 пользователей за период
                </div>
                <div class="card-body p-0">
                    @if($topUsers->isEmpty())
                        <div class="text-center text-muted py-4">
                            <p>Нет данных за выбранный период</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Пользователь</th>
                                        <th class="text-center">Визитов</th>
                                        <th class="text-center">Страниц</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topUsers as $index => $topUser)
                                        <tr>
                                            <td>
                                                @if($index < 3)
                                                    <span class="badge badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.visits.user-history', $topUser->user_id) }}">
                                                    {{ $topUser->user_name }}
                                                </a>
                                            </td>
                                            <td class="text-center">{{ $topUser->visits_count }}</td>
                                            <td class="text-center">{{ $topUser->total_pages }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ТОП СТРАНИЦ ===== --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="mdi mdi-file-document-multiple mr-1"></i> Топ-10 посещаемых страниц за период
                </div>
                <div class="card-body p-0">
                    @if(empty($topPages))
                        <div class="text-center text-muted py-4">
                            <p>Нет данных за выбранный период</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>URL</th>
                                        <th>Заголовок</th>
                                        <th class="text-center" style="width: 120px;">Просмотров</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topPages as $index => $page)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><code>{{ $page['url'] }}</code></td>
                                            <td>{{ $page['title'] ?: '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $page['count'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ИСТОРИЯ ПОСЕЩЕНИЙ ===== --}}
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="mdi mdi-history mr-1"></i> История посещений
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>IP</th>
                            <th>Начало</th>
                            <th>Конец</th>
                            <th class="text-center">Длит.</th>
                            <th class="text-center">Стр.</th>
                            <th class="text-center">Статус</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td>{{ $visit->id }}</td>
                                <td>
                                    <a href="{{ route('admin.visits.user-history', $visit->user_id) }}">
                                        {{ $visit->user_name }}
                                    </a>
                                </td>
                                <td><small>{{ $visit->user_email }}</small></td>
                                <td><code>{{ $visit->ip_address }}</code></td>
                                <td><small>{{ $visit->session_start->format('d.m.Y H:i') }}</small></td>
                                <td>
                                    <small>{{ $visit->session_end ? $visit->session_end->format('d.m.Y H:i') : '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <small>{{ $visit->getDurationFormatted() }}</small>
                                </td>
                                <td class="text-center">{{ $visit->pages_count }}</td>
                                <td class="text-center">
                                    @if($visit->is_active)
                                        <span class="badge badge-success">Активна</span>
                                    @else
                                        <span class="badge badge-secondary">Завершена</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.visits.show', $visit) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Подробнее">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    @if($visit->is_active)
                                        <form action="{{ route('admin.visits.end-session', $visit) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Завершить сессию">
                                                <i class="mdi mdi-stop"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Посещения не найдены
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($visits->hasPages())
            <div class="card-footer">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ===== УВЕДОМЛЕНИЯ ===== --}}
@if(session('success'))
    <div class="position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    </div>
@endif

<style>
    .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-secondary { background-color: #6c757d; }
    .badge-dark { background-color: #8B4513; }
    .badge-light { background-color: #fff; color: #212529; }
    .btn-xs { padding: 0.125rem 0.25rem; font-size: 0.75rem; }
    .table-sm td, .table-sm th { padding: 0.4rem; }
</style>
@endsection
