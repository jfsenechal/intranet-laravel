<?php

namespace AcMarche\Security\Filament\Resources;

use AcMarche\Security\Constant\NavigationGroupEnum;
use AcMarche\Security\Filament\Resources\ModuleResource\Pages\CreateModule;
use AcMarche\Security\Filament\Resources\ModuleResource\Pages\EditModule;
use AcMarche\Security\Filament\Resources\ModuleResource\Pages\ListModule;
use AcMarche\Security\Filament\Resources\ModuleResource\Pages\ViewModule;
use AcMarche\Security\Filament\Resources\ModuleResource\RelationManagers\RoleRelationManager;
use AcMarche\Security\Form\ModuleForm;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Tables\ModuleTables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroupEnum::SETTINGS->getLabel();
    }

    public static function form(Form $form): Form
    {
        return ModuleForm::createForm($form);
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
