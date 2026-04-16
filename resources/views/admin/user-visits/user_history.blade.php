{{-- 
    Файл: resources/views/admin/user-visits/user-history.blade.php
    История посещений конкретного пользователя
--}}
@extends('layouts.admin')

@section('title', 'История посещений: ' . $user->name)

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="mdi mdi-account-clock mr-2"></i>
                История посещений: {{ $user->name }}
            </h2>
            <p class="text-muted">{{ $user->email }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    {{-- ===== СТАТИСТИКА ПОЛЬЗОВАТЕЛЯ ===== --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($userStats['total_visits']) }}</h3>
                    <small>Всего визитов</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($userStats['total_pages']) }}</h3>
                    <small>Просмотров стр.</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    @php
                        $avgMin = round($userStats['avg_duration'] / 60, 1);
                    @endphp
                    <h3 class="mb-0">{{ $avgMin }} мин</h3>
                    <small>Ср. длительность</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $userStats['unique_ips'] }}</h3>
                    <small>Уникальных IP</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center py-2">
                    <small>Первый визит:</small><br>
                    <strong>{{ $userStats['first_visit'] ? \Carbon\Carbon::parse($userStats['first_visit'])->format('d.m.Y') : '-' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center py-2">
                    <small>Последний визит:</small><br>
                    <strong>{{ $userStats['last_visit'] ? \Carbon\Carbon::parse($userStats['last_visit'])->format('d.m.Y') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ФИЛЬТР ПО ДАТАМ ===== --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="mdi mdi-filter mr-1"></i> Фильтр по периоду
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visits.user-history', $user) }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Дата с:</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Дата по:</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="mdi mdi-magnify"></i> Применить
                        </button>
                        <a href="{{ route('admin.visits.user-history', $user) }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Сбросить
                        </a>
                    </div>
                </div>
            </form>
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
                            <th>IP-адрес</th>
                            <th>Начало сессии</th>
                            <th>Конец сессии</th>
                            <th class="text-center">Длительность</th>
                            <th class="text-center">Страниц</th>
                            <th class="text-center">Статус</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td>{{ $visit->id }}</td>
                                <td><code>{{ $visit->ip_address }}</code></td>
                                <td>{{ $visit->session_start->format('d.m.Y H:i:s') }}</td>
                                <td>{{ $visit->session_end ? $visit->session_end->format('d.m.Y H:i:s') : '-' }}</td>
                                <td class="text-center">{{ $visit->getDurationFormatted() }}</td>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Посещения не найдены за выбранный период
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
@endsection
