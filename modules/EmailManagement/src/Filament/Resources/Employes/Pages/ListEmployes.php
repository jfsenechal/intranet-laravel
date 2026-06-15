<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Filament\Resources\Employes\Tables\EmployesTable;
use AcMarche\EmailManagement\Models\Employe;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

final class ListEmployes extends ListRecords
{
    protected static string $resource = EmployeResource::class;

    protected static ?string $navigationLabel = 'citoyens';

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' citoyens';
    }

    public function table(Table $table): Table
    {
        return EmployesTable::configure($table);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un citoyen')
                ->icon(Heroicon::Plus),
            Action::make('generateAllTokens')
                ->label('Générer tous les jetons')
                ->icon(Heroicon::Key)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Générer les jetons manquants')
                ->modalDescription('Un jeton personnel sera généré pour tous les citoyens qui n\'en possèdent pas encore.')
                ->action(function (): void {
                    $count = Employe::whereNull('auth_token')->count();

                    Employe::whereNull('auth_token')
                        ->each(fn (Employe $citoyen) => $citoyen->update(['auth_token' => Str::random(64)]));

                    Notification::make()
                        ->title("{$count} jetons générés avec succès")
                        ->success()
                        ->send();
                }),
        ];
    }
}
