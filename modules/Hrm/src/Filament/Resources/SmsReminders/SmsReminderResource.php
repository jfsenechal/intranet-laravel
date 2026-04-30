<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders;

use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\CreateSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\EditSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ListSmsReminders;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\SendSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\SmsHistory;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ViewSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas\SmsReminderForm;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas\SmsReminderInfolist;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Tables\SmsReminderTables;
use AcMarche\Hrm\Models\SmsReminder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class SmsReminderResource extends Resource
{
    #[Override]
    protected static ?string $model = SmsReminder::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $recordTitleAttribute = 'phone_number';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-device-phone-mobile';
    }

    public static function getNavigationLabel(): string
    {
        return 'Rappels SMS';
    }

    public static function getModelLabel(): string
    {
        return 'Rappel SMS';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Rappels SMS';
    }

    public static function form(Schema $schema): Schema
    {
        return SmsReminderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SmsReminderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsReminderTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmsReminders::route('/'),
            'create' => CreateSmsReminder::route('/create'),
            'history' => SmsHistory::route('/history'),
            'send' => SendSmsReminder::route('/send'),
            'view' => ViewSmsReminder::route('/{record}/view'),
            'edit' => EditSmsReminder::route('/{record}/edit'),
        ];
    }
}
