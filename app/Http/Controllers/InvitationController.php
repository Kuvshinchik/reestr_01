<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvitationController extends Controller
{
    // список приглашений
    public function index()
    {
        $invitations = Invitation::latest()->paginate(20);

        return view('invitations.index', compact('invitations'));
    }

    // форма создания
    public function create()
    {
        return view('invitations.create');
    }

    // сохранение приглашения
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:invitations,email'],
            'days'  => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $expiresAt = $request->filled('days')
            ? Carbon::now()->addDays($request->integer('days'))
            : null;

        $invitation = Invitation::create([
            'email'      => $request->email,
            'token'      => Str::random(40),
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('invitations.index')
            ->with('status', 'Приглашение создано: ' . route('register.invited', $invitation->token));
    }
}
