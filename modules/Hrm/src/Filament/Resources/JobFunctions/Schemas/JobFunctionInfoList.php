<?php

namespace AcMarche\Hrm\Filament\Resources\JobFunctions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobFunctionInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                    ]),
            ]);
    }
}
