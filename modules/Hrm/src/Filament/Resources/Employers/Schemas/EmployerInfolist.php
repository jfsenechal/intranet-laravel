<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Schemas;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use AcMarche\Hrm\Models\Employer;
use Filament\Infolists\Components\RepeatableEntry;
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
                            ->label('Employeur parent')
                            ->placeholder('—'),
                        RepeatableEntry::make('children')
                            ->label('Employeurs enfants')
                            ->placeholder('Aucun')
                            ->contained(false)
                            ->schema([
                                TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->url(fn (Employer $record): string => EmployerResource::getUrl('view', ['record' => $record]))
                                    ->color('primary'),
                            ]),
                    ]),
            ]);
    }
}
