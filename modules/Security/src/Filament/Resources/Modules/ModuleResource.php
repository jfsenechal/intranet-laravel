<?php

namespace AcMarche\Security\Filament\Resources\Modules;

use AcMarche\Security\Constant\NavigationGroupEnum;
use AcMarche\Security\Filament\Resources\Modules\Pages\CreateModule;
use AcMarche\Security\Filament\Resources\Modules\Pages\EditModule;
use AcMarche\Security\Filament\Resources\Modules\Pages\ListModule;
use AcMarche\Security\Filament\Resources\Modules\Pages\ViewModule;
use AcMarche\Security\Filament\Resources\Modules\RelationManagers\RoleRelationManager;
use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleForm;
use AcMarche\Security\Filament\Resources\Modules\Tables\ModuleTables;
use AcMarche\Security\Models\Module;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroupEnum::SETTINGS->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return ModuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModuleTables::table($table);
    }

    public static function getRelations(): array
    {
        return [
            RoleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModule::route('/'),
            'create' => CreateModule::route('/create'),
            'view' => ViewModule::route('/{record}'),
            'edit' => EditModule::route('/{record}/edit'),
        ];
    }
}
