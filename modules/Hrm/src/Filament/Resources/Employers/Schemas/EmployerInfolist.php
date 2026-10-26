<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EmployerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent.name')
                            ->label('Employeur parent'),
                    ]),
            ]);
    }
}
