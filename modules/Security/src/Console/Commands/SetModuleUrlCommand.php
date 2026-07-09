<?php

declare(strict_types=1);

namespace AcMarche\Security\Console\Commands;

use AcMarche\ActivityManager\Filament\Resources\Activities\ActivityResource as ActivityManagerResource;
use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\AldermenAgenda\Filament\Resources\Event\EventResource;
use AcMarche\App\Filament\Pages\ClaimRequestPage;
use AcMarche\App\Filament\Pages\EmailsListPage;
use AcMarche\App\Filament\Pages\TeleworkPage;
use AcMarche\App\Filament\Pages\VacationPage;
use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use AcMarche\College\Filament\Resources\Notifications\Pages\CreateNotification;
use AcMarche\Conseil\Filament\Resources\Agendas\AgendaResource;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\CpasLibrary\Filament\Pages\LibraryIndex;
use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\GuichetHdv\Filament\Pages\TicketsOfTheDay;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use AcMarche\QrCode\Filament\Resources\QrCodes\QrCodeResource;
use AcMarche\Security\Filament\Resources\Users\UserResource;
use AcMarche\Security\Models\Module;
use AcMarche\SportsActivities\Filament\Resources\Activities\ActivityResource as ActivitySportResource;
use AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource;
use AcMarche\Telecommunication\Filament\Resources\Telephones\TelephoneResource;
use AcMarche\WhoIsWho\Filament\Pages\Search as WhoIsWhoSearch;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SetModuleUrlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'intranet:module-set-url {--dry-run : Show what would change without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Persist the resolved Filament URL of each internal module into the modules.url column';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $updated = 0;
        $externalSkipped = 0;

        /** @var list<Module> $withoutUrl */
        $withoutUrl = [];

        foreach (Module::query()->orderBy('id')->get() as $module) {
            // External modules already hold their real destination URL, leave them untouched.
            if ($module->is_external) {
                $externalSkipped++;

                continue;
            }

            $url = $this->resolveUrl($module);

            if ($url === null) {
                // No Filament destination yet: clear the legacy route name so the module
                // is reported as not migrated.
                $withoutUrl[] = $module;
                $url = '';
            }

            if ($module->url !== $url) {
                if (! $dryRun) {
                    $module->update(['url' => $url]);
                }

                $updated++;
            }
        }

        $this->newLine();
        $this->info($dryRun ? '✓ Dry run complete (no changes written).' : '✓ Module URLs updated.');
        $this->line("  Updated: {$updated}");
        $this->line("  External (skipped): {$externalSkipped}");
        $this->line('  Without URL: '.count($withoutUrl));

        if ($withoutUrl !== []) {
            $this->newLine();
            $this->warn('Modules without a URL:');
            $this->table(
                ['ID', 'Name'],
                array_map(fn (Module $module): array => [$module->id, $module->name], $withoutUrl),
            );
        }

        return SfCommand::SUCCESS;
    }

    private function resolveUrl(Module $module): ?string
    {
        return match ($module->id) {
            3 => EventResource::getUrl('index', panel: 'aldermen-agenda-panel'),
            4 => OffenseResource::getUrl('index', panel: 'offenses-panel'),
            6 => EmployeeResource::getUrl('index', panel: 'hrm-panel'),
            9 => DocumentResource::getUrl('index', panel: 'document-panel'),
            10 => TelephoneResource::getUrl('index', panel: 'telecommunication-panel'),
            13 => TripResource::getUrl('index', panel: 'mileage-panel'),
            15 => NewsResource::getUrl('index', panel: 'news-panel'),
            16 => IncomingMailResource::getUrl('index', panel: 'courrier-panel'),
            17 => UserResource::getUrl('index', panel: 'security-panel'),
            18 => CaseFileResource::getUrl('index', panel: 'mediation-panel'),
            19 => CreateNotification::getUrl(panel: 'college-panel'),
            20 => ActivitySportResource::getUrl('index', panel: 'sports-activities-panel'),
            21 => SignatureResource::getUrl('index', panel: 'app-panel'),
            22 => 'https://agenda.marche.be',
            25 => AgendaResource::getUrl('index', panel: 'conseil-panel'),
            26 => VacationPage::getUrl(panel: 'app-panel'),
            33 => EmailsListPage::getUrl(panel: 'app-panel'),
            36 => ClaimRequestPage::getUrl(panel: 'app-panel'),
            39 => WeekResource::getUrl(panel: 'meal-delivery-panel'),
            40 => ProfileResource::getUrl('index', panel: 'agent-panel'),
            41 => ActivityManagerResource::getUrl('index', panel: 'activity-manager-panel'),
            42 => WhoIsWhoSearch::getUrl(panel: 'who-is-who-panel'),
            44 => PublicationResource::getUrl('index', panel: 'publication-panel'),
            50 => TeleworkPage::getUrl(panel: 'app-panel'),
            52 => LibraryIndex::getUrl(panel: 'cpas-library-panel'),
            56 => QrCodeResource::getUrl('index', panel: 'qrcode-panel'),
            58 => ActionPstResource::getUrl('index', panel: 'pst-panel'),
            59 => TicketsOfTheDay::getUrl(panel: 'guichet-hdv-panel'),
            60 => IncidentResource::getUrl('index', panel: 'street-watch-panel'),
            61 => AddressBookResource::getUrl('index', panel: 'mailing-list-panel'),
            default => null,
        };
    }
}
