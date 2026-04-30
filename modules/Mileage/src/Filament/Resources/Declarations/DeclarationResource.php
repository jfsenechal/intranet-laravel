<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations;

use AcMarche\Mileage\Filament\Pages\AllDeclarations;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\CreateDeclaration;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\EditDeclaration;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\ListDeclarations;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\ViewDeclaration;
use AcMarche\Mileage\Filament\Resources\Declarations\Schemas\DeclarationForm;
use AcMarche\Mileage\Filament\Resources\Declarations\Tables\DeclarationTables;
use AcMarche\Mileage\Models\Declaration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class DeclarationResource extends Resource
{
    #[Override]
    protected static ?string $model = Declaration::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-duplicate';
    }

    public static function getNavigationLabel(): string
    {
        return 'Mes déclarations';
    }

    public static function form(Schema $schema): Schema
    {
        return DeclarationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeclarationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeclarations::route('/'),
            'create' => CreateDeclaration::route('/create'),
            'view' => ViewDeclaration::route('/{record}/view'),
            'edit' => EditDeclaration::route('/{record}/edit'),
            'all' => AllDeclarations::route('/asall/declarations'),
        ];
    }
}
