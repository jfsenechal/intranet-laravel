<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures;

use AcMarche\App\Filament\Resources\Signatures\Pages\CreateSignature;
use AcMarche\App\Filament\Resources\Signatures\Pages\EditSignature;
use AcMarche\App\Filament\Resources\Signatures\Pages\ListSignatures;
use AcMarche\App\Filament\Resources\Signatures\Pages\ViewSignature;
use AcMarche\App\Filament\Resources\Signatures\Schemas\SignatureForm;
use AcMarche\App\Filament\Resources\Signatures\Schemas\SignatureInfolist;
use AcMarche\App\Filament\Resources\Signatures\Tables\SignatureTables;
use AcMarche\App\Models\Signature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class SignatureResource extends Resource
{
    #[Override]
    protected static ?string $model = Signature::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-identification';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Mon profil';

    public static function getNavigationLabel(): string
    {
        return 'Ma signature mail';
    }

    public static function getModelLabel(): string
    {
        return 'Signature mail';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Signatures';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('username', Auth::user()->username);
    }

    public static function canCreate(): bool
    {
        return ! Signature::query()->where('username', Auth::user()->username)->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return SignatureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SignatureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SignatureTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSignatures::route('/'),
            'create' => CreateSignature::route('/create'),
            'view' => ViewSignature::route('/{record}/view'),
            'edit' => EditSignature::route('/{record}/edit'),
        ];
    }
}
