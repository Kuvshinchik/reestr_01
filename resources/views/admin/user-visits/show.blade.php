{{-- 
    Файл: resources/views/admin/user-visits/show.blade.php
    Детальный просмотр сессии посещения
--}}
@extends('layouts.admin')

@section('title', 'Детали сессии #' . $visit->id)

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="mdi mdi-account-details mr-2"></i>
                Сессия #{{ $visit->id }}
                @if($visit->is_active)
                    <span class="badge badge-success">Активна</span>
                @else
                    <span class="badge badge-secondary">Завершена</span>
                @endif
            </h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад к списку
            </a>
            <a href="{{ route('admin.visits.user-history', $visit->user_id) }}" class="btn btn-info">
                <i class="mdi mdi-account-clock"></i> История пользователя
            </a>
        </div>
    </div>

    <div class="row">
        {{-- ===== ИНФОРМАЦИЯ О СЕССИИ ===== --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="mdi mdi-account mr-1"></i> Информация о пользователе
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Имя:</td>
                            <td><strong>{{ $visit->user_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $visit->user_email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">IP-адрес:</td>
                            <td><code>{{ $visit->ip_address }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">User-Agent:</td>
                            <td><small class="text-break">{{ $visit->user_agent ?: '-' }}</small></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="mdi mdi-clock mr-1"></i> Время сессии
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Начало:</td>
                            <td>{{ $visit->session_start->format('d.m.Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Окончание:</td>
                            <td>{{ $visit->session_end ? $visit->session_end->format('d.m.Y H:i:s') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Длительность:</td>
                            <td><strong>{{ $visit->getDurationFormatted() }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Статус:</td>
                            <td>
                                @if($visit->is_active)
                                    <span class="badge badge-success">Активна</span>
                                @else
                                    <span class="badge badge-secondary">Завершена</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="mdi mdi-chart-bar mr-1"></i> Статистика сессии
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary mb-0">{{ $visit->pages_count }}</h3>
                            <small class="text-muted">Просмотров</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success mb-0">{{ count($visit->getUniqueUrls()) }}</h3>
                            <small class="text-muted">Уникальных стр.</small>
                        </div>
                    </div>
                </div>
                @if($visit->is_active)
                    <div class="card-footer">
                        <form action="{{ route('admin.visits.end-session', $visit) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="mdi mdi-stop"></i> Завершить сессию
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== ПОСЕЩЁННЫЕ СТРАНИЦЫ ===== --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="mdi mdi-file-document-multiple mr-1"></i> 
                    Посещённые страницы ({{ $visit->pages_count }})
                </div>
                <div class="card-body p-0">
                    @if(empty($visit->visited_pages))
                        <div class="text-center text-muted py-5">
                            <i class="mdi mdi-file-hidden mdi-48px"></i>
                            <p>Страницы не зафиксированы</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>URL</th>
                                        <th>Заголовок</th>
                                        <th style="width: 160px;">Время</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visit->visited_pages as $index => $page)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <code>{{ $page['url'] ?? '-' }}</code>
                                            </td>
                                            <td>{{ $page['title'] ?? '-' }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ isset($page['visited_at']) ? \Carbon\Carbon::parse($page['visited_at'])->format('H:i:s') : '-' }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== УНИКАЛЬНЫЕ СТРАНИЦЫ ===== --}}
            @php
                $uniqueUrls = $visit->getUniqueUrls();
            @endphp
            @if(!empty($uniqueUrls))
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="mdi mdi-filter-variant mr-1"></i> 
                        Уникальные страницы ({{ count($uniqueUrls) }})
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            @foreach($uniqueUrls as $url)
                                <span class="badge badge-light mr-2 mb-2 p-2">
                                    <code>{{ $url }}</code>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
    .text-break {
        word-break: break-all;
    }
    .badge-light {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
</style>
@endsection
