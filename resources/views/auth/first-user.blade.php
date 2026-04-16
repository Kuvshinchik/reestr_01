@extends('layouts.app')

@section('title', 'Создание первого пользователя')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">Создание первого пользователя</h1>

            <div class="alert alert-info">
                Эта форма будет доступна только до создания первого пользователя.
            </div>

            <form method="POST" action="{{ route('first-user.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input id="name" type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           required autofocus>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input id="password" type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                    <input id="password_confirmation" type="password"
                           name="password_confirmation"
                           class="form-control"
                           required>
                </div>

                <button type="submit" class="btn btn-success">
                    Создать пользователя
                </button>
            </form>
        </div>
    </div>
@endsection
