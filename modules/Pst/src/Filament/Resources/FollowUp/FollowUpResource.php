<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\FollowUp;

use AcMarche\Pst\Filament\Resources\FollowUp\Pages\CreateFollowUp;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\EditFollowUp;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\ListFollowUps;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\ViewFollowUp;
use AcMarche\Pst\Filament\Resources\FollowUp\Schemas\FollowUpForm;
use AcMarche\Pst\Filament\Resources\FollowUp\Tables\FollowUpTables;
use AcMarche\Pst\Models\FollowUp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class FollowUpResource extends Resource
{
    #[Override]
    protected static ?string $model = FollowUp::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return FollowUpForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FollowUpTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFollowUps::route('/'),
            'create' => CreateFollowUp::route('/create'),
            'view' => ViewFollowUp::route('/{record}'),
            'edit' => EditFollowUp::route('/{record}/edit'),
        ];
    }
}
