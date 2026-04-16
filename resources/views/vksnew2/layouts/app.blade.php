@extends('layouts.admin')  {{-- наследуем общий лейаут портала --}}

@section('title', $pageTitle ?? 'ВКС')

@section('content')
    @yield('vks_content')
@endsection