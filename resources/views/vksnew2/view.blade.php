@extends('vksnew2.layouts.app')
@php $pageTitle = 'ВКС — ' . $vks->title; @endphp

@section('vks_content')
<a href="{{ url()->previous() }}" class="button">← Список заявок</a>

<div id="view_vks" style="margin-top:16px;">
<table border="0" cellpadding="6" cellspacing="0">
    <tr><td class="gray">Название</td>
        <td class="text">{{ $vks->title }}</td></tr>
    <tr><td class="gray">Организатор</td>
        <td class="text">{{ $vks->organ }}</td></tr>
    <tr><td class="gray">Кабинет</td>
        <td class="text">{{ $kab->name ?? '—' }}</td></tr>
    <tr><td class="gray">Дата начала</td>
        <td class="text">{{ date('d.m.Y H:i', $vks->datan) }}</td></tr>
    <tr><td class="gray">Дата конца</td>
        <td class="text">{{ date('d.m.Y H:i', $vks->datak) }}</td></tr>
    <tr><td class="gray">Заявку оставил</td>
        <td class="text">{{ $vks->zakfio }}</td></tr>
    <tr><td valign="top" class="gray">Участники</td>
        <td class="text">{!! nl2br(e($vks->dir ?? '')) !!}&nbsp;</td></tr>
    <tr><td valign="top" class="gray">Комментарий</td>
        <td class="text">{!! nl2br(e($vks->koment ?? '')) !!}&nbsp;</td></tr>

    @if($attachments->count())
    <tr><td class="gray">Файлы</td>
        <td class="text">
            @foreach($attachments as $att)
                <a href="{{ asset('storage/vks/'.$vks->id.'/'.$att->id.'_'.$att->filename) }}">
                    {{ $att->name }}
                </a><br>
            @endforeach
        </td></tr>
    @endif
</table>
</div>
@endsection