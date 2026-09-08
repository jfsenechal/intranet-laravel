<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Pages;

use AcMarche\Ad\Enums\RolesEnum;
use AcMarche\Ad\Services\SubscriptionService;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Override;

/**
 * The agents who may subscribe but have no professional email encoded in the HRM.
 *
 * They can only be reached — and can only subscribe — on their private address, so the
 * list tells the administrator who to contact, or what to fix in the HRM record.
 */
final class EmployeesWithoutProfessionalEmail extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    #[Override]
    protected static ?string $navigationLabel = 'Agents sans email pro';

    #[Override]
    protected static ?int $navigationSort = 60;

    #[Override]
    protected string $view = 'ad::filament.pages.employees-without-professional-email';

    /**
     * The rows expose private addresses, so the page stays with the administrators.
     */
    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdministrator() || $user->hasOneOfThisRoles([RolesEnum::ROLE_AD_ADMIN->value]);
    }

    public function getTitle(): string
    {
        return 'Agents sans email professionnel';
    }

    public function getSubheading(): ?string
    {
        return 'Agents sous contrat actif dont aucun email professionnel n\'est encodé, '
            .'mais qui disposent d\'une adresse privée.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(app(SubscriptionService::class)->eligibleEmployeesWithoutProfessionalEmailQuery())
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading('Aucun agent sans email professionnel')
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('private_email')
                    ->label('Email privé')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
