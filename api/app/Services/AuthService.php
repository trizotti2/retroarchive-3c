<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private CartService $carts)
    {
    }

    public function register(Request $request, array $data): array
    {
        abort_unless($request->hasSession(), 400, 'Abra a loja pelo endereço do frontend.');
        $guestId = $request->session()->get('cart.guest_id');

        $result = DB::transaction(function () use ($data, $guestId) {
            $user = User::create($data)->refresh();

            return [
                'user' => $user,
                'adjustments' => $this->carts->mergeGuest($guestId, $user->id),
            ];
        }, 3);

        Auth::guard('web')->login($result['user']);
        $this->finishSession($request);

        return $result;
    }

    public function login(Request $request, array $credentials): array
    {
        abort_unless($request->hasSession(), 400, 'Abra a loja pelo endereço do frontend.');
        $guestId = $request->session()->get('cart.guest_id');
        $adjustments = [];
        $guard = Auth::guard('web');

        if (! $guard->attemptWhen($credentials, function (User $user) use ($guestId, &$adjustments): bool {
            $adjustments = $this->carts->mergeGuest($guestId, $user->id);

            return true;
        })) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha inválidos.']);
        }

        $this->finishSession($request);

        return ['user' => $guard->user(), 'adjustments' => $adjustments];
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function finishSession(Request $request): void
    {
        $request->session()->forget('cart.guest_id');
        $request->session()->regenerate(true);
    }
}
