<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class InvitationRegisterController extends Controller
{
    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->isUsed() || $invitation->isExpired()) {
            abort(403, 'Приглашение недействительно или истекло.');
        }

        return view('auth.invited-register', compact('invitation'));
    }

    public function store(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->isUsed() || $invitation->isExpired()) {
            abort(403, 'Приглашение недействительно или истекло.');
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        $invitation->update([
            'used_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }
}
