<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ModuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    Grid::make()
                        ->schema([
                            TextEntry::make('description')
                                ->label('Description')
                                ->hiddenLabel()
                                ->columnSpanFull(),
                            TextEntry::make('description_role')
                                ->label('Description rôle')
                                ->columnSpanFull(),
                            TextEntry::make('roles.name')
                                ->label('Rôles')
                                ->badge()
                                ->columnSpanFull(),
                        ]),
                    Grid::make(1)
                        ->schema([
                            Section::make('Etat')
                                ->label(null)
                                ->schema([
                                    IconEntry::make('is_public')
                                        ->label('Accessible à tous')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('danger'),
                                    IconEntry::make('is_external')
                                        ->label('Url externe')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('danger'),
                                ]),
                        ])
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),

            ]);
    }
}
