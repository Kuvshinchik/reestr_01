@extends('layouts.app')

@section('title', 'Приглашения')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Приглашения</h1>
        <a href="{{ route('invitations.create') }}" class="btn btn-primary">
            Создать приглашение
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($invitations->count())
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Email</th>
                    <th>Ссылка</th>
                    <th>Истекает</th>
                    <th>Использовано</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($invitations as $invitation)
                    <tr>
                        <td>{{ $invitation->email }}</td>
                        <td>
                            <a href="{{ route('register.invited', $invitation->token) }}" target="_blank">
                                {{ route('register.invited', $invitation->token) }}
                            </a>
                        </td>
                        <td>
                            @if($invitation->expires_at)
                                {{ $invitation->expires_at->format('d.m.Y H:i') }}
                            @else
                                Бессрочно
                            @endif
                        </td>
                        <td>
                            @if($invitation->used_at)
                                {{ $invitation->used_at->format('d.m.Y H:i') }}
                            @else
                                Нет
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $invitations->links() }}
    @else
        <p class="text-muted">Приглашений пока нет.</p>
    @endif
@endsection
