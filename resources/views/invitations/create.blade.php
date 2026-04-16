@extends('layouts.app')

@section('title', 'Новое приглашение')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">Новое приглашение</h1>

            <form method="POST" action="{{ route('invitations.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email приглашённого</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="days" class="form-label">Срок действия в днях (опционально)</label>
                    <input id="days"
                           type="number"
                           name="days"
                           value="{{ old('days') }}"
                           min="1" max="365"
                           class="form-control @error('days') is-invalid @enderror">
                    <div class="form-text">
                        Если оставить пустым — приглашение будет бессрочным.
                    </div>
                    @error('days')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Создать приглашение
                </button>
            </form>
        </div>
    </div>
@endsection
