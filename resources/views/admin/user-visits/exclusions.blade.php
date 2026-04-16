{{-- 
    Файл: resources/views/admin/user-visits/exclusions.blade.php
    Управление исключениями пользователей
--}}
@extends('layouts.admin')

@section('title', 'Исключения из отслеживания')

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="mdi mdi-account-off mr-2"></i>
                Исключения из отслеживания
            </h2>
            <p class="text-muted">Пользователи в этом списке не отслеживаются системой учёта посещений</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    {{-- ===== ДОБАВЛЕНИЕ ИСКЛЮЧЕНИЯ ===== --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="mdi mdi-account-plus mr-1"></i> Добавить пользователя в исключения
        </div>
        <div class="card-body">
            <form action="{{ route('admin.visits.exclusions.add') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Пользователь:</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Выберите пользователя --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @if($availableUsers->isEmpty())
                            <small class="text-muted">Все пользователи уже добавлены в исключения</small>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Причина (опционально):</label>
                        <input type="text" name="reason" class="form-control" 
                               placeholder="Например: Тестовый аккаунт">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block" 
                                {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                            <i class="mdi mdi-plus"></i> Добавить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== СПИСОК ИСКЛЮЧЕНИЙ ===== --}}
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="mdi mdi-format-list-bulleted mr-1"></i> 
            Список исключений ({{ $exclusions->count() }})
        </div>
        <div class="card-body p-0">
            @if($exclusions->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="mdi mdi-account-check mdi-48px"></i>
                    <h5 class="mt-3">Исключений нет</h5>
                    <p>Все пользователи отслеживаются системой</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Причина</th>
                                <th>Добавил</th>
                                <th>Дата добавления</th>
                                <th class="text-center">Статус</th>
                                <th style="width: 150px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exclusions as $exclusion)
                                <tr class="{{ !$exclusion->is_active ? 'table-secondary' : '' }}">
                                    <td>{{ $exclusion->id }}</td>
                                    <td>
                                        <strong>{{ $exclusion->user->name ?? 'Удалён' }}</strong>
                                    </td>
                                    <td>{{ $exclusion->user->email ?? '-' }}</td>
                                    <td>
                                        @if($exclusion->reason)
                                            <small>{{ $exclusion->reason }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $exclusion->creator->name ?? 'Система' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $exclusion->created_at->format('d.m.Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($exclusion->is_active)
                                            <span class="badge badge-success">Активно</span>
                                        @else
                                            <span class="badge badge-secondary">Неактивно</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Переключить статус --}}
                                        <form action="{{ route('admin.visits.exclusions.toggle', $exclusion) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-{{ $exclusion->is_active ? 'warning' : 'success' }}"
                                                    title="{{ $exclusion->is_active ? 'Деактивировать' : 'Активировать' }}">
                                                <i class="mdi mdi-{{ $exclusion->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- Удалить --}}
                                        <form action="{{ route('admin.visits.exclusions.remove', $exclusion) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Удалить пользователя из исключений?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- История пользователя --}}
                                        @if($exclusion->user)
                                            <a href="{{ route('admin.visits.user-history', $exclusion->user_id) }}" 
                                               class="btn btn-sm btn-outline-info" title="История">
                                                <i class="mdi mdi-history"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== ИНФОРМАЦИЯ ===== --}}
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <i class="mdi mdi-information mr-1"></i> Как это работает
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Активное исключение</strong> — посещения пользователя НЕ записываются в базу данных</li>
                <li><strong>Неактивное исключение</strong> — посещения пользователя записываются (временная отмена)</li>
                <li>При добавлении в исключения активные сессии пользователя автоматически закрываются</li>
                <li>Уже записанные данные НЕ удаляются (используйте очистку данных для удаления)</li>
                <li>Рекомендуется добавлять в исключения: администраторов, тестовых пользователей, ботов</li>
            </ul>
        </div>
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
    .badge-success { background-color: #28a745; }
    .badge-secondary { background-color: #6c757d; }
</style>
@endsection
