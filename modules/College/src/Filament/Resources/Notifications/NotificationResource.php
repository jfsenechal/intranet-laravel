<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications;

use AcMarche\College\Filament\Resources\Notifications\Pages\CreateNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\EditNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\ListNotifications;
use AcMarche\College\Filament\Resources\Notifications\Pages\ViewNotification;
use AcMarche\College\Filament\Resources\Notifications\Schemas\NotificationForm;
use AcMarche\College\Filament\Resources\Notifications\Tables\NotificationsTable;
use AcMarche\College\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class NotificationResource extends Resource
{
    #[Override]
    protected static ?string $model = Notification::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Notifications';

    #[Override]
    protected static ?string $modelLabel = 'notification';

    #[Override]
    protected static ?string $pluralModelLabel = 'notifications';

    #[Override]
    protected static ?string $recordTitleAttribute = 'file_name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'file_name',
            'mime',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return NotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'view' => ViewNotification::route('/{record}'),
            'edit' => EditNotification::route('/{record}/edit'),
        ];
    }
}
