{{-- 
    Файл: resources/views/winter-worker-analytics/index.blade.php
    Аналитическая записка по первозимникам
--}}
@extends('layouts.admin')

@section('title', 'Первозимники - Аналитика')

@section('content')
<div class="container-fluid mt-4">
    
    {{-- ===== ЗАГОЛОВОК СТРАНИЦЫ ===== --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Аналитическая записка по первозимникам</h2>
        </div>
    </div>

    {{-- ===== НАВИГАЦИОННЫЕ КНОПКИ ===== --}}
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <a href="{{ route('winter-worker-chart.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-chart-bar mdi-36px text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">ПЕРЕЙТИ</h5>
                            <h3 class="mb-0">к диаграмме</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card m-b-30">
                <div class="card-body">
                    <a href="{{ route('winter-worker-table.index') }}" class="d-flex align-items-center text-dark">
                        <div class="mr-3">
                            <i class="mdi mdi-table-large mdi-36px text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">ПЕРЕЙТИ</h5>
                            <h3 class="mb-0">к таблице</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ФИЛЬТРЫ ===== --}}
    <div class="row mb-4">
        {{-- Select для выбора РДЖВ --}}
        <div class="col-md-4">
            <label for="regionFilter" class="form-label fw-bold">Выберите РДЖВ:</label>
            <select id="regionFilter" class="form-select" style="border-color: #007bff;">
                <option value="all" {{ $selectedRegionId === 'all' ? 'selected' : '' }}>
                    ДЖВ (все РДЖВ)
                </option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $selectedRegionId == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Кнопка печати --}}
        <div class="col-md-4 d-flex align-items-end">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="mdi mdi-printer"></i> Печать
            </button>
        </div>
    </div>

    {{-- ===== АНАЛИТИЧЕСКАЯ ЗАПИСКА ===== --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card analytics-card" id="analyticsReport">
                <div class="card-body">
                    
                    {{-- Шапка документа --}}
                    <div class="document-header text-center mb-4">
                        <h3 class="mb-1">{{ $analytics['title'] }}</h3>
                        <p class="text-muted mb-0">от {{ $analytics['date'] }}</p>
                    </div>

                    <hr>

                    {{-- Резюме --}}
                    <section class="analytics-section mb-4">
                        <h5 class="section-title">
                            <i class="mdi mdi-file-document-outline"></i> Краткое резюме
                        </h5>
                        <div class="section-content">
                            <p>{!! $analytics['summary'] !!}</p>
                        </div>
                    </section>

                    {{-- Ключевые показатели --}}
                    <section class="analytics-section mb-4">
                        <h5 class="section-title">
                            <i class="mdi mdi-gauge"></i> Ключевые показатели
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="metric-card bg-light">
                                    <div class="metric-value text-primary">
                                        {{ number_format($analytics['raw_data']['total']['hired'], 0, ',', ' ') }}
                                    </div>
                                    <div class="metric-label">Принято</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-light">
                                    <div class="metric-value text-success">
                                        {{ number_format($analytics['raw_data']['total']['trained'], 0, ',', ' ') }}
                                    </div>
                                    <div class="metric-label">Обучено</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-light">
                                    @php
                                        $percent = $analytics['raw_data']['total']['percent'];
                                        $percentClass = $percent >= 90 ? 'text-success' : ($percent >= 70 ? 'text-warning' : 'text-danger');
                                    @endphp
                                    <div class="metric-value {{ $percentClass }}">
                                        {{ $analytics['raw_data']['total']['percent'] }}%
                                    </div>
                                    <div class="metric-label">Процент обучения</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-light">
                                    <div class="metric-value text-danger">
                                        {{ number_format($analytics['raw_data']['total']['not_trained'], 0, ',', ' ') }}
                                    </div>
                                    <div class="metric-label">Не обучено</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Детальный анализ --}}
                    @if(!empty($analytics['details']))
                    <section class="analytics-section mb-4">
                        <h5 class="section-title">
                            <i class="mdi mdi-chart-pie"></i> Детальный анализ
                        </h5>
                        @foreach($analytics['details'] as $detail)
                            <div class="detail-block mb-3">
                                <h6 class="detail-title">{{ $detail['title'] }}</h6>
                                <div class="detail-content">
                                    {!! $detail['content'] !!}
                                </div>
                            </div>
                        @endforeach
                    </section>
                    @endif

                    {{-- Проблемные области --}}
                    @if(!empty($analytics['problems']))
                    <section class="analytics-section mb-4">
                        <h5 class="section-title text-danger">
                            <i class="mdi mdi-alert-circle"></i> Проблемные области
                        </h5>
                        <div class="problems-list">
                            @foreach($analytics['problems'] as $problem)
                                <div class="problem-item {{ $problem['severity']['class'] }}">
                                    <span class="badge badge-{{ $problem['severity']['badge'] }} mr-2">
                                        {{ $problem['percent'] }}%
                                    </span>
                                    {!! $problem['text'] !!}
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    {{-- Лидеры --}}
                    @if(!empty($analytics['leaders']))
                    <section class="analytics-section mb-4">
                        <h5 class="section-title text-success">
                            <i class="mdi mdi-trophy"></i> Лидеры обучения
                        </h5>
                        <div class="leaders-list">
                            @foreach($analytics['leaders'] as $leader)
                                <div class="leader-item">
                                    <span class="badge badge-success mr-2">{{ $leader['percent'] }}%</span>
                                    {!! $leader['text'] !!}
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    {{-- Рекомендации --}}
                    @if(!empty($analytics['recommendations']))
                    <section class="analytics-section mb-4">
                        <h5 class="section-title">
                            <i class="mdi mdi-lightbulb-outline"></i> Рекомендации
                        </h5>
                        <div class="recommendations-list">
                            @foreach($analytics['recommendations'] as $rec)
                                @php
                                    $recClass = $rec['priority'] === 'high' ? 'recommendation-high' : 
                                               ($rec['priority'] === 'medium' ? 'recommendation-medium' : 'recommendation-low');
                                    $recIcon = $rec['priority'] === 'high' ? 'mdi-alert' : 
                                              ($rec['priority'] === 'medium' ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline');
                                @endphp
                                <div class="recommendation-item {{ $recClass }}">
                                    <i class="mdi {{ $recIcon }}"></i>
                                    <span>{!! $rec['text'] !!}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    {{-- Заключение --}}
                    <section class="analytics-section">
                        <h5 class="section-title">
                            <i class="mdi mdi-clipboard-check"></i> Заключение
                        </h5>
                        <div class="conclusion-block">
                            <p>{!! $analytics['conclusion'] !!}</p>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== СКРИПТЫ ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // При изменении Select - перезагружаем страницу с новым параметром
    $('#regionFilter').on('change', function() {
        const regionId = $(this).val();
        
        const url = new URL(window.location.href);
        url.searchParams.set('region_id', regionId);
        
        window.location.href = url.toString();
    });
});
</script>

{{-- ===== СТИЛИ ===== --}}
<style>
    /* Карточка аналитики */
    .analytics-card {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .analytics-card .card-body {
        padding: 2rem;
    }

    /* Шапка документа */
    .document-header h3 {
        color: #343a40;
        font-weight: 600;
    }

    /* Секции */
    .analytics-section {
        margin-bottom: 1.5rem;
    }

    .section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    .section-title i {
        margin-right: 0.5rem;
    }

    .section-content {
        color: #495057;
        line-height: 1.8;
    }

    /* Метрики */
    .metric-card {
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 1rem;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .metric-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
    }

    /* Детали */
    .detail-block {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .detail-title {
        color: #007bff;
        margin-bottom: 0.5rem;
    }

    .detail-content {
        color: #495057;
    }

    /* Проблемы */
    .problem-item {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        background: #fff;
        border-left: 4px solid #dc3545;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .problem-item.text-warning {
        border-left-color: #ffc107;
    }

    /* Лидеры */
    .leader-item {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        background: #fff;
        border-left: 4px solid #28a745;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Рекомендации */
    .recommendation-item {
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-radius: 8px;
        display: flex;
        align-items: flex-start;
    }

    .recommendation-item i {
        margin-right: 0.75rem;
        font-size: 1.25rem;
    }

    .recommendation-high {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .recommendation-medium {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }

    .recommendation-low {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    /* Заключение */
    .conclusion-block {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 1.5rem;
        border-radius: 8px;
    }

    .conclusion-block p {
        margin-bottom: 0;
        font-size: 1.1rem;
    }

    .conclusion-block strong {
        color: #fff;
    }

    /* Badge */
    .badge {
        padding: 0.4em 0.6em;
        font-size: 0.85em;
    }

    .badge-danger {
        background-color: #dc3545;
        color: #fff;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-success {
        background-color: #28a745;
        color: #fff;
    }

    .badge-info {
        background-color: #17a2b8;
        color: #fff;
    }

    /* Печать */
    @media print {
        .card.m-b-30,
        .form-select,
        .btn,
        label {
            display: none !important;
        }

        .analytics-card {
            border: none;
            box-shadow: none;
        }

        .conclusion-block {
            background: #f8f9fa !important;
            color: #212529 !important;
            -webkit-print-color-adjust: exact;
        }
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .metric-value {
            font-size: 1.5rem;
        }
    }
</style>
@endsection
