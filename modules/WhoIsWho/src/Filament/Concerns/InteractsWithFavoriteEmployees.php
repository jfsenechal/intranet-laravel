<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Concerns;

use AcMarche\WhoIsWho\Repository\FavoriteEmployeeRepository;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Star toggle shared by every directory page rendering
 * `who-is-who::filament.components.employee-card`.
 */
trait InteractsWithFavoriteEmployees
{
    /**
     * Resolved once per request. Protected on purpose: Livewire does not persist
     * it between requests, so a toggle on another page is always picked up.
     *
     * @var list<int>|null
     */
    protected ?array $favoriteEmployeeIds = null;

    /**
     * Favorites are personal, so they are hidden from the guests who browse the
     * directory without signing in.
     */
    public function canUseFavoriteEmployees(): bool
    {
        return Auth::check();
    }

    /**
     * @return list<int>
     */
    public function favoriteEmployeeIds(): array
    {
        return $this->favoriteEmployeeIds ??= FavoriteEmployeeRepository::favoriteIds();
    }

    public function isFavoriteEmployee(int $employeeId): bool
    {
        return in_array($employeeId, $this->favoriteEmployeeIds(), true);
    }

    public function toggleFavoriteEmployee(int $employeeId): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $isFavorite = $user->toggleFavoriteEmployee($employeeId);
        $this->favoriteEmployeeIds = null;

        Notification::make()
            ->title($isFavorite ? 'Ajouté à mes collègues favoris' : 'Retiré de mes collègues favoris')
            ->success()
            ->send();
    }
}
