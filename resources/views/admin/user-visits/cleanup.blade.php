{{-- 
    Файл: resources/views/admin/user-visits/cleanup.blade.php
    Очистка данных посещений
--}}
@extends('layouts.admin')

@section('title', 'Очистка данных посещений')

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="mdi mdi-delete-sweep mr-2 text-danger"></i>
                Очистка данных посещений
            </h2>
            <p class="text-muted">Удаление записей из таблицы user_visits</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    {{-- ===== СТАТИСТИКА ТАБЛИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($tableStats['total_records']) }}</h4>
                    <small>Всего записей</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($tableStats['unique_users']) }}</h4>
                    <small>Пользователей</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($tableStats['total_pages']) }}</h4>
                    <small>Просмотров</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ $tableStats['active_sessions'] }}</h4>
                    <small>Активных</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center py-2">
                    <small>Первая запись:</small><br>
                    <strong>{{ $tableStats['oldest_record'] ? \Carbon\Carbon::parse($tableStats['oldest_record'])->format('d.m.Y') : '-' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center py-2">
                    <small>Последняя запись:</small><br>
                    <strong>{{ $tableStats['newest_record'] ? \Carbon\Carbon::parse($tableStats['newest_record'])->format('d.m.Y') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ===== ОЧИСТКА ПО ПЕРИОДУ ===== --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="mdi mdi-calendar-remove mr-1"></i> Удалить за период
                </div>
                <div class="card-body">
                    <form id="cleanupPeriodForm" action="{{ route('admin.visits.cleanup.execute') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Дата с:</label>
                                <input type="date" name="date_from" id="cleanup_date_from" 
                                       class="form-control" required
                                       value="{{ now()->subMonths(6)->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Дата по:</label>
                                <input type="date" name="date_to" id="cleanup_date_to" 
                                       class="form-control" required
                                       value="{{ now()->subMonths(3)->format('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Пользователь (опционально):</label>
                            <select name="user_id" id="cleanup_user_id" class="form-select">
                                <option value="">Все пользователи</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Предпросмотр --}}
                        <div id="previewResult" class="alert alert-info d-none mb-3">
                            <strong>Будет удалено:</strong>
                            <span id="previewCount">0</span> записей
                            (<span id="previewUsers">0</span> польз., 
                            <span id="previewPages">0</span> стр.)
                        </div>

                        <div class="mb-3">
                            <button type="button" id="previewBtn" class="btn btn-outline-info">
                                <i class="mdi mdi-eye"></i> Предпросмотр
                            </button>
                        </div>

                        <hr>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="confirm" value="1" 
                                   class="form-check-input" id="confirmPeriod" required>
                            <label class="form-check-label text-danger" for="confirmPeriod">
                                <strong>Я понимаю, что данные будут удалены безвозвратно</strong>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="mdi mdi-delete"></i> Удалить записи
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== ОЧИСТКА СТАРЫХ ЗАПИСЕЙ ===== --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="mdi mdi-clock-alert mr-1"></i> Удалить старые записи
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.visits.cleanup.old') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Удалить записи старше:</label>
                            <div class="input-group">
                                <input type="number" name="days" class="form-control" 
                                       min="1" max="3650" value="90" required>
                                <span class="input-group-text">дней</span>
                            </div>
                            <small class="text-muted">
                                Будут удалены записи с session_start ранее 
                                {{ now()->subDays(90)->format('d.m.Y') }}
                            </small>
                        </div>

                        <hr>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="confirm" value="1" 
                                   class="form-check-input" id="confirmOld" required>
                            <label class="form-check-label text-danger" for="confirmOld">
                                <strong>Я понимаю, что данные будут удалены безвозвратно</strong>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="mdi mdi-delete-clock"></i> Удалить старые записи
                        </button>
                    </form>
                </div>
            </div>

            {{-- ===== АВТООЧИСТКА ===== --}}
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="mdi mdi-robot mr-1"></i> Автоматическая очистка
                </div>
                <div class="card-body">
                    <p>Для автоматической очистки добавьте в <code>app/Console/Kernel.php</code>:</p>
                    <pre class="bg-dark text-light p-3 rounded"><code>// Удалять записи старше 90 дней каждую ночь
$schedule->call(function () {
    (new UserVisitService())->deleteOlderThan(90);
})->dailyAt('03:00');</code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ПРЕДУПРЕЖДЕНИЕ ===== --}}
    <div class="alert alert-danger mt-4">
        <h5><i class="mdi mdi-alert mr-1"></i> Внимание!</h5>
        <ul class="mb-0">
            <li>Удалённые данные <strong>невозможно восстановить</strong></li>
            <li>Перед удалением рекомендуется сделать <strong>экспорт в Excel</strong></li>
            <li>Активные сессии также будут удалены, если попадают в период</li>
            <li>Операция может занять время при большом объёме данных</li>
        </ul>
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

{{-- ===== СКРИПТЫ ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Предпросмотр очистки
    $('#previewBtn').on('click', function() {
        const dateFrom = $('#cleanup_date_from').val();
        const dateTo = $('#cleanup_date_to').val();
        const userId = $('#cleanup_user_id').val();

        if (!dateFrom || !dateTo) {
            alert('Укажите период');
            return;
        }

        $.ajax({
            url: "{{ route('admin.visits.cleanup.preview') }}",
            type: 'GET',
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                user_id: userId
            },
            success: function(response) {
                $('#previewCount').text(response.records_count);
                $('#previewUsers').text(response.unique_users);
                $('#previewPages').text(response.total_pages);
                $('#previewResult').removeClass('d-none');
            },
            error: function() {
                alert('Ошибка при получении данных');
            }
        });
    });

    // Подтверждение удаления
    $('#cleanupPeriodForm').on('submit', function(e) {
        const count = $('#previewCount').text();
        if (count === '0') {
            return confirm('Записей для удаления не найдено. Продолжить?');
        }
        return confirm('Вы уверены, что хотите удалить ' + count + ' записей?');
    });
});
</script>
@endsection
