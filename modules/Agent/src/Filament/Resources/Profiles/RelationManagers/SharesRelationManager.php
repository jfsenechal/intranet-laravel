<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Override;

final class SharesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'shares';

    #[Override]
    protected static ?string $title = 'Partages';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('shared_for')
                ->label('Destinataire (email)')
                ->email()
                ->required()
                ->maxLength(100),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('shared_for')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('shared_for')
                    ->label('Destinataire')
                    ->searchable(),
                TextColumn::make('shared_by')
                    ->label('Partagé par')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['shared_by'] = Auth::user()?->username ?? 'system';

                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
