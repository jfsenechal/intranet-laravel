<?php

declare(strict_types=1);

namespace AcMarche\Pst\Http\Controllers;

use AcMarche\Pst\Auth\MagicLoginLink;
use App\Models\User;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;

final class MagicLoginLinkController extends Controller
{
    public function create(Request $request, User $user)
    {
        $user->notify(new MagicLoginLink(Password::createToken($user)));

        return response()->json(['message' => 'Magic login link sent.']);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        if (! Password::tokenExists($user, $request->token)) {
            abort(404);
        }

        Auth::login($user);

        Password::deleteToken($user);

        return redirect()->intended('dashboard');
    }
}
