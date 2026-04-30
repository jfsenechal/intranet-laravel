<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class OperationalObjectiveForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Identification')
                    ->icon('tabler-target')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Intitulé')
                            ->placeholder('Saisissez l\'intitulé de l\'objectif opérationnel')
                            ->prefixIcon('tabler-file-text')
                            ->maxLength(255),
                        Select::make('strategic_objective_id')
                            ->relationship(
                                'strategicObjective',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where(function (Builder $query): void {
                                        $query->forSelectedDepartment()
                                            ->orWhereNull('department');
                                    })
                                    ->orderBy('name')
                            )
                            ->label('Objectif Stratégique')
                            ->prefixIcon('tabler-hierarchy')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Configuration')
                    ->icon('tabler-settings')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ToggleButtons::make('department')
                                    ->label('Département')
                                    ->required()
                                    ->default(UserRepository::departmentSelected())
                                    ->options(DepartmentEnum::class)
                                    ->enum(DepartmentEnum::class)
                                    ->grouped(),
                                ToggleButtons::make('scope')
                                    ->label('Volet')
                                    ->required()
                                    ->options(ActionScopeEnum::class)
                                    ->grouped(),
                            ]),
                    ]),
            ]);
    }
}
