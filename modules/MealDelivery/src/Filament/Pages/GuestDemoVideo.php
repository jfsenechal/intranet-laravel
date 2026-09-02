<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Pages;

use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use App\Models\User;
use Filament\Pages\Page;
use UnitEnum;

/**
 * A screen recording of the guest meal workflow, from booking a reservation on
 * an order to the "Repas invités" block coming out on the printed sheets.
 *
 * The feature replaces a handwritten habit, so it is easier to show than to
 * describe, and the demonstration sits in the panel next to the pages it shows.
 */
final class GuestDemoVideo extends Page
{
    use MealDeliveryAuthorization;

    /**
     * Served straight from `public/`, not from the storage disk: the recording
     * ships with the application and never changes at runtime.
     */
    public const string VIDEO_PATH = 'videos/demo-rpas-invite.mp4';

    protected static ?string $slug = 'guest-demo-video';

    protected static ?int $navigationSort = 10;

    protected static string|UnitEnum|null $navigationGroup = 'Invités';

    protected string $view = 'meal-delivery::filament.pages.guest-demo-video';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-play-circle';
    }

    public static function getNavigationLabel(): string
    {
        return 'Démonstration';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function getTitle(): string
    {
        return 'Démonstration des repas invités';
    }

    public function getSubheading(): string
    {
        return 'De la réservation encodée sur une commande au bloc « Repas invités » imprimé sur les feuilles.';
    }

    public function getVideoUrl(): string
    {
        return asset(self::VIDEO_PATH);
    }
}
