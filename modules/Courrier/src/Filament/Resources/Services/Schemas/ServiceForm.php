<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Schemas;

use AcMarche\Courrier\Filament\Components\DepartmentField;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('initials')
                    ->label('Initiales')
                    ->maxLength(255),
                ...DepartmentField::make(),
                Section::make('Membres du service')
                    ->description('Sélectionnez les destinataires qui font partie de ce service')
                    ->schema([
                        CheckboxList::make('recipients')
                            ->hiddenLabel()
                            ->relationship(
                                titleAttribute: 'last_name',
                                modifyQueryUsing: fn (Builder $query): Builder => RecipientRepository::queryOrderByLastName(
                                    $query
                                )
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Recipient $record): string => "{$record->first_name} {$record->last_name}"
                            )
                            ->columns(3)
                            ->searchable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
