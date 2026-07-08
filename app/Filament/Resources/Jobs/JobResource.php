<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs;

use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Models\Job;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;
use UnitEnum;

final class JobResource extends Resource
{
    #[Override]
    protected static ?string $model = Job::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Système';

    #[Override]
    protected static ?string $navigationLabel = 'Jobs en attente';

    #[Override]
    protected static ?string $modelLabel = 'job en attente';

    #[Override]
    protected static ?string $pluralModelLabel = 'jobs en attente';

    #[Override]
    protected static ?int $navigationSort = 90;

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdministrator() === true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Job::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->poll('10s')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('displayName')
                    ->label('Job')
                    ->state(fn (Job $record): string => $record->displayName())
                    ->wrap()
                    ->searchable(query: fn ($query, string $search) => $query->where('payload', 'like', "%{$search}%")),
                TextColumn::make('queue')
                    ->label('File')
                    ->badge()
                    ->sortable(),
                TextColumn::make('attempts')
                    ->label('Tentatives')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),
                TextColumn::make('reserved_at')
                    ->label('Statut')
                    ->badge()
                    ->state(fn (Job $record): string => $record->reserved_at !== null ? 'En cours' : 'En attente')
                    ->color(fn (string $state): string => $state === 'En cours' ? 'info' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->state(fn (Job $record): ?string => $record->createdAt()?->format('d/m/Y H:i:s'))
                    ->sortable(),
                TextColumn::make('available_at')
                    ->label('Disponible le')
                    ->state(fn (Job $record): ?string => $record->availableAt()?->format('d/m/Y H:i:s'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('payload')
                    ->label('Payload')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->modalHeading('Contenu du job')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(fn (Job $record): HtmlString => new HtmlString(
                        '<pre class="overflow-x-auto text-xs whitespace-pre-wrap">'
                        .e(json_encode(
                            json_decode((string) $record->payload, true),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                        ))
                        .'</pre>',
                    )),
                DeleteAction::make()
                    ->modalHeading('Supprimer ce job en attente ?')
                    ->modalDescription('Le job ne sera pas exécuté. Cette action est irréversible.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun job en attente')
            ->emptyStateIcon(Heroicon::OutlinedQueueList);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobs::route('/'),
        ];
    }
}
