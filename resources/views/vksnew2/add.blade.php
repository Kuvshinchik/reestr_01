@extends('vksnew2.layouts.app')
@php $pageTitle = 'Новая заявка ВКС'; @endphp

@section('vks_content')
<a href="{{ route('vksnew2.index') }}" class="button">← Список заявок</a><br><br>

@if($errors->any())
    <div style="color:red; margin:10px 0;">
        @foreach($errors->all() as $e)<div>⚠ {{ $e }}</div>@endforeach
    </div>
@endif

<div style="text-align:center">
<form action="{{ route('vksnew2.store') }}" method="post" enctype="multipart/form-data">
@csrf
<table border="0" cellspacing="4">
    <tr>
        <td>Название *</td>
        <td><input type="text" name="title" value="{{ old('title') }}" style="width:300px"></td>
    </tr>
    <tr>
        <td>Организатор *</td>
        <td><input type="text" name="organ" value="{{ old('organ') }}" style="width:300px"></td>
    </tr>
    <tr>
        <td>Дата *</td>
        <td><input type="text" name="date" placeholder="дд.мм.гггг" value="{{ old('date') }}"></td>
    </tr>
    <tr>
        <td>Кабинет *</td>
        <td>
            <select name="kab">
                @foreach($kabs as $kab)
                    <option value="{{ $kab->id }}" {{ old('kab')==$kab->id ? 'selected':'' }}>
                        {{ $kab->name }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <td>Время начала *</td>
        <td><input type="text" name="time_start" placeholder="чч:мм" value="{{ old('time_start') }}"></td>
    </tr>
    <tr>
        <td>Время окончания *</td>
        <td><input type="text" name="time_end" placeholder="чч:мм" value="{{ old('time_end') }}"></td>
    </tr>
    <tr>
        <td>Комментарий</td>
        <td><textarea name="comment" rows="4" style="width:300px">{{ old('comment') }}</textarea></td>
    </tr>
    <tr>
        <td>Файлы</td>
        <td>
            <input type="file" name="files[]"><br>
            <input type="file" name="files[]"><br>
            <input type="file" name="files[]">
        </td>
    </tr>
    <tr>
        <td></td>
        <td><input type="submit" value="Добавить заявку"></td>
    </tr>
</table>
</form>
</div>
@endsection