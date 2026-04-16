@extends('vksnew2.layouts.app')
@php $pageTitle = 'Список рассылки ВКС'; @endphp

@section('vks_content')
<a href="{{ route('vksnew2.index') }}" class="button">← Список заявок</a><br><br>

<form action="{{ route('vksnew2.recipient.save') }}" method="post">
@csrf
<textarea name="recipient_list" rows="20" cols="50">{{ $list }}</textarea>
<br><br>
<input type="submit" value="Сохранить">
</form>
@endsection