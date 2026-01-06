<?php

namespace AcMarche\Courrier\Filament\Resources\Services\Schemas;

use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('initials')
                    ->label('Initiales')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),

                Section::make('Membres du service')
                    ->description('Sélectionnez les destinataires qui font partie de ce service')
                    ->schema([
                        Forms\Components\CheckboxList::make('recipients')
                            ->hiddenLabel()
                            ->relationship(
                                titleAttribute: 'last_name',
                                modifyQueryUsing: fn(Builder $query) => RecipientRepository::queryActiveOrderByLastName(
                                    $query
                                )
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn(Recipient $record) => "{$record->first_name} {$record->last_name}"
                            )
                            ->columns(3)
                            ->searchable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
