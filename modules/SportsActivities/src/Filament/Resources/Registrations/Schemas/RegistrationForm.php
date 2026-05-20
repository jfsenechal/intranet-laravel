<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Schemas;

use AcMarche\SportsActivities\Models\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inscription')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('member_id')
                                ->label('Sportif')
                                ->relationship(
                                    name: 'member',
                                    titleAttribute: 'last_name',
                                    modifyQueryUsing: fn ($query) => $query->orderBy('last_name'),
                                )
                                ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->last_name} {$record->first_name}")
                                ->searchable(['last_name', 'first_name'])
                                ->preload()
                                ->required(),
                            Select::make('activity_id')
                                ->label('Activité')
                                ->relationship('activity', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('group_id', null)),
                            Select::make('group_id')
                                ->label('Groupe')
                                ->required()
                                ->options(function (Get $get): array {
                                    $activityId = $get('activity_id');
                                    if (! $activityId) {
                                        return [];
                                    }

                                    return Group::query()
                                        ->where('activity_id', $activityId)
                                        ->get()
                                        ->mapWithKeys(fn (Group $g): array => [
                                            $g->id => "{$g->day} — {$g->time} — {$g->location}",
                                        ])
                                        ->toArray();
                                })
                                ->searchable(),
                            TextInput::make('price')->label('Prix')->numeric(),
                            TextInput::make('user')->label('Créé par')->required()->maxLength(255),
                            Textarea::make('comment')->label('Remarque')->rows(3)->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
