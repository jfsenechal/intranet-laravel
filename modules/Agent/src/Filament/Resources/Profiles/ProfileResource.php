<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles;

use AcMarche\Agent\Filament\Resources\Profiles\Pages\CreateProfile;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\EditProfile;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ListProfiles;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ViewProfile;
use AcMarche\Agent\Filament\Resources\Profiles\RelationManagers\ExternalApplicationsRelationManager;
use AcMarche\Agent\Filament\Resources\Profiles\RelationManagers\FoldersRelationManager;
use AcMarche\Agent\Filament\Resources\Profiles\RelationManagers\HistoriesRelationManager;
use AcMarche\Agent\Filament\Resources\Profiles\RelationManagers\SharesRelationManager;
use AcMarche\Agent\Filament\Resources\Profiles\Schemas\ProfileForm;
use AcMarche\Agent\Filament\Resources\Profiles\Schemas\ProfileInfolist;
use AcMarche\Agent\Filament\Resources\Profiles\Tables\ProfileTables;
use AcMarche\Agent\Models\Profile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ProfileResource extends Resource
{
    #[Override]
    protected static ?string $model = Profile::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $recordTitleAttribute = 'username';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-identification';
    }

    public static function getNavigationLabel(): string
    {
        return 'Profils informatiques';
    }

    public static function getModelLabel(): string
    {
        return 'profil';
    }

    public static function getPluralModelLabel(): string
    {
        return 'profils';
    }

    public static function form(Schema $schema): Schema
    {
        return ProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfileTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExternalApplicationsRelationManager::class,
            FoldersRelationManager::class,
            SharesRelationManager::class,
            HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfiles::route('/'),
            'create' => CreateProfile::route('/create'),
            'view' => ViewProfile::route('/{record}'),
            'edit' => EditProfile::route('/{record}/edit'),
        ];
    }
}
