<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Participation;

use AcMarche\AldermenAgenda\Filament\Resources\Participation\Pages\CreateParticipation;
use AcMarche\AldermenAgenda\Filament\Resources\Participation\Pages\EditParticipation;
use AcMarche\AldermenAgenda\Filament\Resources\Participation\Pages\ListParticipations;
use AcMarche\AldermenAgenda\Filament\Resources\Participation\Pages\ViewParticipation;
use AcMarche\AldermenAgenda\Filament\Resources\Participation\Schemas\ParticipationForm;
use AcMarche\AldermenAgenda\Filament\Resources\Participation\Tables\ParticipationTables;
use AcMarche\AldermenAgenda\Models\Participation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ParticipationResource extends Resource
{
    #[Override]
    protected static ?string $model = Participation::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-check-circle';
    }

    public static function getNavigationLabel(): string
    {
        return 'Participations';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Agenda Échevin';
    }

    public static function form(Schema $schema): Schema
    {
        return ParticipationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParticipationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParticipations::route('/'),
            'create' => CreateParticipation::route('/create'),
            'edit' => EditParticipation::route('/{record}/edit'),
            'view' => ViewParticipation::route('/{record}'),
        ];
    }
}
