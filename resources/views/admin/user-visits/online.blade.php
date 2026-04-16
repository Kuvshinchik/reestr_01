{{-- 
    Файл: resources/views/admin/user-visits/online.blade.php
    Пользователи онлайн (активные сессии)
--}}
@extends('layouts.admin')

@section('title', 'Пользователи онлайн')

@section('content')
<div class="container-fluid mt-4">

    {{-- ===== ЗАГОЛОВОК ===== --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="mdi mdi-account-multiple mr-2 text-success"></i>
                Пользователи онлайн
                <span class="badge badge-success">{{ $activeSessions->count() }}</span>
            </h2>
            <p class="text-muted">Активные сессии пользователей в данный момент</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад к списку
            </a>
            <form action="{{ route('admin.visits.close-stale') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="mdi mdi-broom"></i> Закрыть устаревшие
                </button>
            </form>
        </div>
    </div>

    {{-- ===== КАРТОЧКИ ОНЛАЙН ПОЛЬЗОВАТЕЛЕЙ ===== --}}
    @if($activeSessions->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-account-off mdi-72px text-muted"></i>
                <h4 class="text-muted mt-3">Нет активных пользователей</h4>
                <p class="text-muted">В данный момент никто не просматривает сайт</p>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($activeSessions as $session)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span>
                                <i class="mdi mdi-circle text-white mr-1" style="animation: pulse 1s infinite;"></i>
                                Онлайн
                            </span>
                            <small>{{ $session->session_end ? $session->session_end->diffForHumans() : '-' }}</small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="mdi mdi-account mr-1"></i>
                                {{ $session->user_name }}
                            </h5>
                            <p class="card-text">
                                <small class="text-muted">{{ $session->user_email }}</small>
                            </p>
                            
                            <hr>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="mb-0 text-primary">{{ $session->pages_count }}</h4>
                                    <small class="text-muted">страниц</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-0 text-info">{{ $session->getDurationFormatted() }}</h4>
                                    <small class="text-muted">на сайте</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <p class="mb-1">
                                <small class="text-muted">IP:</small> 
                                <code>{{ $session->ip_address }}</code>
                            </p>
                            
                            @php
                                $lastPage = $session->getLastPage();
                            @endphp
                            @if($lastPage)
                                <p class="mb-0">
                                    <small class="text-muted">Текущая страница:</small><br>
                                    <code class="small">{{ $lastPage['url'] ?? '-' }}</code>
                                </p>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ route('admin.visits.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-eye"></i> Подробнее
                            </a>
                            <a href="{{ route('admin.visits.user-history', $session->user_id) }}" class="btn btn-sm btn-outline-info">
                                <i class="mdi mdi-history"></i> История
                            </a>
                            <form action="{{ route('admin.visits.end-session', $session) }}" method="POST" class="d-inline float-right">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Завершить сессию">
                                    <i class="mdi mdi-stop"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- ===== СТИЛИ ===== --}}
<style>
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .card.border-success {
        border-width: 2px;
    }
</style>

{{-- ===== АВТООБНОВЛЕНИЕ ===== --}}
<script>
    // Автообновление страницы каждые 30 секунд
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
