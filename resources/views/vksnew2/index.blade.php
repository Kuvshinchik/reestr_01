@extends('vksnew2.layouts.app')
@php $pageTitle = 'Список заявок ВКС'; @endphp

@section('vks_content')
<div id="menu">
    <ul>
        <li><a class="button" href="{{ route('vksnew2.index') }}">Главная</a></li>
        <li><a class="button" href="{{ route('vksnew2.add') }}">Добавить заявку</a></li>
        <li><a class="button" href="{{ route('vksnew2.recipient') }}">Список email рассылки</a></li>
        <li><a class="button" href="{{ route('vksnew2.closestatus') }}">Закрыть заявки</a></li>
    </ul>
</div>

{{-- Быстрые фильтры --}}
<div class="filterbar" style="margin:10px 0 14px 0;">
    <div class="quick buttons-row">
        <a class="button quickbtn {{ ($year==date('Y') && $month==date('n')) ? 'active' : '' }}"
           href="{{ route('vksnew2.index', ['y'=>date('Y'),'m'=>date('n')]) }}">Текущий месяц</a>
        <a class="button quickbtn {{ ($year==date('Y') && !$month) ? 'active' : '' }}"
           href="{{ route('vksnew2.index', ['y'=>date('Y')]) }}">Этот год</a>
        <a class="button quickbtn {{ ($year==(date('Y')-1) && !$month) ? 'active' : '' }}"
           href="{{ route('vksnew2.index', ['y'=>date('Y')-1]) }}">Прошлый год</a>
    </div>

    <div><strong>Годы:</strong>
        @foreach($years as $y)
            <a href="{{ route('vksnew2.index', ['y'=>$y->y]) }}"
               @if((int)$y->y === $year) style="font-weight:bold;" @endif>
                {{ $y->y }} ({{ $y->cnt }})
            </a>
        @endforeach
    </div>

    @if($year && $months->count())
    <div><strong>Месяцы:</strong>
        @foreach($months as $m)
            <a href="{{ route('vksnew2.index', ['y'=>$year,'m'=>$m->m]) }}"
               @if((int)$m->m === $month) style="font-weight:bold;" @endif>
                {{ $m->m }} ({{ $m->cnt }})
            </a>
        @endforeach
    </div>
    @endif
</div>

@if($errors->any())
    <div style="color:red; margin:8px 0;">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

<table border="1" width="100%">
<tr>
    <th>#</th><th>Добавлена</th><th>Название</th><th>Организатор</th>
    <th>Заявитель</th><th>Начало</th><th>Конец</th><th>Кабинет</th>
    <th>Статус</th><th>Удалить</th>
</tr>
@forelse($vksList as $row)
<tr>
    <td>{{ $row->id }}</td>
    <td>{{ date('d.m.Y H:i', $row->datadob) }}</td>
    <td><a href="{{ route('vksnew2.view', $row->id) }}">{{ $row->title }}</a></td>
    <td>{{ $row->organ }}</td>
    <td>{{ $row->zakfio }}</td>
    <td>{{ $row->datan_str }}</td>
    <td>{{ $row->datak_str }}</td>
    <td>{{ $row->kab }}</td>
    <td>{{ $row->status->name ?? '—' }}</td>
    <td>
        @if($canDelete)
        <form method="post" action="{{ route('vksnew2.delete', $row->id) }}">
            @csrf
            <button type="submit" onclick="return confirm('Удалить заявку №{{ $row->id }}?')">
                Удалить
            </button>
        </form>
        @endif
    </td>
</tr>
@empty
    <tr><td colspan="10" style="text-align:center">Заявок нет</td></tr>
@endforelse
</table>

{{ $vksList->withQueryString()->links() }}
@endsection