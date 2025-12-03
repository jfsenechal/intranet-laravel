<?php

namespace AcMarche\Mileage\Filament\Resources;

use AcMarche\Mileage\Filament\Resources\DeclarationResource\Pages;
use AcMarche\Mileage\Filament\Resources\DeclarationResource\Schema\DeclarationForm;
use AcMarche\Mileage\Filament\Resources\DeclarationResource\Tables\DeclarationTables;
use AcMarche\Mileage\Models\Declaration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DeclarationResource extends Resource
{
    protected static ?string $model = Declaration::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-duplicate';
    }

    public static function getNavigationLabel(): string
    {
        return 'Déclarations';
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
            'index' => Pages\ListDeclarations::route('/'),
            'create' => Pages\CreateDeclaration::route('/create'),
            'view' => Pages\ViewDeclaration::route('/{record}/view'),
            'edit' => Pages\EditDeclaration::route('/{record}/edit'),
        ];
    }
}
