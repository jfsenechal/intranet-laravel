<?php

declare(strict_types=1);

namespace AcMarche\Security\Handler;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\AldermenAgenda\Filament\Resources\Event\EventResource;
use AcMarche\App\Filament\Pages\ClaimRequestPage;
use AcMarche\App\Filament\Pages\EmailsListPage;
use AcMarche\App\Filament\Pages\TeleworkPage;
use AcMarche\App\Filament\Pages\VacationPage;
use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use AcMarche\QrCode\Filament\Resources\QrCodes\QrCodeResource;
use AcMarche\Security\Filament\Resources\Users\UserResource;
use AcMarche\Security\Models\Module;
use AcMarche\WhoIsWho\Filament\Pages\Index as WhoIsWhoIndex;
use Illuminate\Support\Collection;

final class MigrationHandler
{
    public const array modules_to_skip = [1, 2, 23, 49];

    public static function urlModule(Module $module): ?string
    {
        $resource = self::findTheResource($module);
        if ($resource) {
            return $resource;
        }

        return null;
    }

    public static function findTheResource(Module $module): ?string
    {
        return match ($module->id) {
            3 => EventResource::getUrl('index', panel: 'aldermen-agenda-panel'),
            6 => EmployeeResource::getUrl('index', panel: 'hrm-panel'),
            9 => DocumentResource::getUrl('index', panel: 'document-panel'),
            13 => TripResource::getUrl('index', panel: 'mileage-panel'),
            15 => NewsResource::getUrl('index', panel: 'news'),
            16 => IncomingMailResource::getUrl('index', panel: 'courrier-panel'),
            17 => UserResource::getUrl('index', panel: 'security-panel'),
            18 => ComplainantResource::getUrl('index', panel: 'mediation-panel'),
            21 => SignatureResource::getUrl('index', panel: 'app-panel'),
            22 => 'https://agenda.marche.be',
            26 => VacationPage::getUrl(panel: 'app-panel'),
            33 => EmailsListPage::getUrl(panel: 'app-panel'),
            36 => ClaimRequestPage::getUrl(panel: 'app-panel'),
            39 => OrderResource::getUrl(panel: 'meal-delivery-panel'),
            40 => ProfileResource::getUrl('index', panel: 'agent-panel'),
            42 => WhoIsWhoIndex::getUrl(panel: 'who-is-who-panel'),
            44 => PublicationResource::getUrl('index', panel: 'publication-panel'),
            50 => TeleworkPage::getUrl(panel: 'app-panel'),
            56 => QrCodeResource::getUrl('index', panel: 'qrcode-panel'),
            58 => ActionPstResource::getUrl('index', panel: 'pst-panel'),
            default => null,
        };
    }

    /**
     * Get all modules sorted by name ASC, with migration status resolved.
     *
     * @return Collection<int,Module>
     */
    public static function getAllModules(): Collection
    {
        return Module::query()
            ->whereNotIn('id', self::modules_to_skip)
            ->orderBy('name')
            ->get()
            ->each(function (Module $module): void {
                if ($module->is_external) {
                    $module->migrated = true;

                    return;
                }
                if ($url = self::urlModule($module)) {
                    $module->url = $url;
                    $module->migrated = true;
                } else {
                    $module->migrated = false;
                }
            });
    }
}
