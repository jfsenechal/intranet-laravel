<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament;

use AcMarche\Mileage\Factory\DeclarationFactory;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use Exception;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

final class CreateDeclarationAction
{
    public static function make(): BulkAction
    {
        return
            BulkAction::make('create-declaration')
                ->label('Déclarer mes déplacements')
                ->icon('tabler-confetti')
                ->schema([
                    Select::make('budget_article_id')
                        ->label('Article budgétaire')
                        ->options(BudgetArticle::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (Collection $records, array $data): void {
                    $budgetArticle = BudgetArticle::find($data['budget_article_id']);

                    $personalInformation = PersonalInformationRepository::getByCurrentUser()->first();
                    if (! $personalInformation) {
                        throw new Exception('Remplissez vos données personnelles');
                    }
                    try {
                        $declarations = DeclarationFactory::handleTrips(
                            $records,
                            auth()->user(),
                            $personalInformation,
                            $budgetArticle
                        );
                        Notification::make()
                            ->title('Déclaration(s) crée(s)')
                            ->body(
                                $declarations->count().' déclaration(s) créée(s) avec '.$records->count(
                                ).' déplacement(s)'
                            )
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->deselectRecordsAfterCompletion();
    }
}
