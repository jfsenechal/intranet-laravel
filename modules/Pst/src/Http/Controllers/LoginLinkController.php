<?php

declare(strict_types=1);

namespace AcMarche\Pst\Http\Controllers;

use App\Models\User;
use Exception;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class LoginLinkController extends Controller
{
    public function __invoke(Request $request, string $uuid): RedirectResponse
    {
        $guard = Filament::auth();
        if ($guard->check()) {
            return redirect()->intended('admin');
        }
        try {
            $user = User::query()->where('uuid', $uuid)->first();
            if (! $user instanceof FilamentUser) {
                Log::warning('user not found '.$uuid);

                return redirect('admin/login');
            }
            $guard->login($user, true);
        } catch (Exception $e) {
            Log::warning('user fail '.$uuid.' '.$e->getMessage());

            return redirect('login');
        }

        return redirect()->intended('admin');
    }
}
