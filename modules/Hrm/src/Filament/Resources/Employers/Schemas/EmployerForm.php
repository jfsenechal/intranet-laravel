<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmployerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->label('Employeur parent')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
