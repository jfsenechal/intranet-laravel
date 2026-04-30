<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes;

use AcMarche\QrCode\Filament\Resources\QrCodes\Pages\EditQrCode;
use AcMarche\QrCode\Filament\Resources\QrCodes\Pages\ListQrCodes;
use AcMarche\QrCode\Filament\Resources\QrCodes\Pages\ViewQrCode;
use AcMarche\QrCode\Filament\Resources\QrCodes\Schemas\QrCodeForm;
use AcMarche\QrCode\Filament\Resources\QrCodes\Schemas\QrCodeInfolist;
use AcMarche\QrCode\Filament\Resources\QrCodes\Tables\QrCodeTable;
use AcMarche\QrCode\Models\QrCode;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class QrCodeResource extends Resource
{
    #[Override]
    protected static ?string $model = QrCode::class;

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $modelLabel = 'QR Code';

    #[Override]
    protected static ?string $pluralModelLabel = 'QR Codes';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationLabel(): string
    {
        return 'Mes QR codes';
    }

    public static function form(Schema $schema): Schema
    {
        return QrCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QrCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QrCodeTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQrCodes::route('/'),
            'view' => ViewQrCode::route('/{record}/view'),
            'edit' => EditQrCode::route('/{record}/edit'),
        ];
    }
}
