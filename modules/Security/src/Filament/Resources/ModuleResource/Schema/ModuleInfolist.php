<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Schema;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ModuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextEntry::make('name')
                    ->label('Nom')
                    ->icon('heroicon-o-rectangle-stack'),
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
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextEntry::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->columnSpanFull(),
            ]);
    }
}
