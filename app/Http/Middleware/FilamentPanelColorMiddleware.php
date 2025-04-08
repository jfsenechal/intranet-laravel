<?php

namespace App\Http\Middleware;

use App\Repository\FilamentColorRepository;
use Closure;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilamentPanelColorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $colors = FilamentColorRepository::userColor();
        FilamentColor::register($colors);

        return $next($request);
    }
}
