<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use AcMarche\ActivityManager\Filament\Resources\Members\Schemas\MemberForm;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedule;
use AcMarche\ActivityManager\Models\Member;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class MembersRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'members';

    #[Override]
    protected static ?string $title = 'Inscrits';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewSchedule::class;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return self::$title.' ('.$ownerRecord->members()->count().')';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Inscrire un membre')
                    ->icon(Heroicon::Plus)
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->icon(Heroicon::Eye)
                    ->url(fn (Member $record): string => MembersResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
                DetachAction::make()
                    ->label('Désinscrire')
                    ->icon(Heroicon::XMark),
            ])
            ->recordUrl(fn (Member $record): string => MembersResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Désinscrire la sélection')
                        ->icon(Heroicon::XMark),
                ]),
            ]);
    }
}
