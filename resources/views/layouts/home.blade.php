@extends('layouts.admin')

@section('title', 'Дашборды ДЖВ')

@section('content')
    <div class="mb-4">
        <h1 class="h3">Главная</h1>
        <p>Вы вошли в систему как {{ auth()->user()->name }}.</p>
    </div>
<div class="mb-4">
    <a href="{{ route('invitations.index') }}" class="btn btn-primary">
        Управление приглашениями
    </a>
	</div>
<div class="mb-4">	
	<a href={{'dashboard'}} class="btn btn-primary">
        ДЖВ
    </a>
	</div>
@endsection
