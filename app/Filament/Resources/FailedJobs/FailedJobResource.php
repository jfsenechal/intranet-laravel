<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs;

use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;
use UnitEnum;

final class FailedJobResource extends Resource
{
    #[Override]
    protected static ?string $model = FailedJob::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Système';

    #[Override]
    protected static ?string $navigationLabel = 'Jobs échoués';

    #[Override]
    protected static ?string $modelLabel = 'job échoué';

    #[Override]
    protected static ?string $pluralModelLabel = 'jobs échoués';

    #[Override]
    protected static ?int $navigationSort = 91;

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdministrator() === true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = FailedJob::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return FailedJob::query()->exists() ? 'danger' : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('displayName')
                    ->label('Job')
                    ->state(fn (FailedJob $record): string => $record->displayName())
                    ->wrap()
                    ->searchable(query: fn ($query, string $search) => $query->where('payload', 'like', "%{$search}%")),
                TextColumn::make('queue')
                    ->label('File')
                    ->badge(),
                TextColumn::make('connection')
                    ->label('Connexion')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('exception')
                    ->label('Erreur')
                    ->state(fn (FailedJob $record): string => $record->exceptionSummary())
                    ->color('danger')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('failed_at')
                    ->label('Échec le')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('exception')
                    ->label('Erreur')
                    ->icon(Heroicon::OutlinedBugAnt)
                    ->color('danger')
                    ->modalHeading('Trace de l\'exception')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(fn (FailedJob $record): HtmlString => new HtmlString(
                        '<pre class="overflow-x-auto text-xs whitespace-pre-wrap">'
                        .e($record->exception)
                        .'</pre>',
                    )),
                Action::make('retry')
                    ->label('Relancer')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Relancer ce job ?')
                    ->modalDescription('Le job sera remis en file d\'attente pour être réexécuté.')
                    ->action(function (FailedJob $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()
                            ->title('Job remis en file d\'attente')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading('Supprimer ce job échoué ?')
                    ->modalDescription('L\'enregistrement sera supprimé définitivement.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retrySelected')
                        ->label('Relancer la sélection')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            Artisan::call('queue:retry', ['id' => $records->pluck('uuid')->all()]);

                            Notification::make()
                                ->title('Jobs sélectionnés remis en file d\'attente')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun job échoué')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
        ];
    }
}
