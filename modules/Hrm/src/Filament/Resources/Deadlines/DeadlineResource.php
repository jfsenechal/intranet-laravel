<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines;

use AcMarche\Hrm\Filament\Resources\Deadlines\Schemas\DeadlineForm;
use AcMarche\Hrm\Filament\Resources\Deadlines\Schemas\DeadlineInfolist;
use AcMarche\Hrm\Filament\Resources\Deadlines\Tables\DeadlineTables;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Policies\DeadlinePolicy;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class DeadlineResource extends Resource
{
    #[Override]
    protected static ?string $model = Deadline::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return 'Echéances';
    }

    public static function getModelLabel(): string
    {
        return 'Echéance';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Echéances';
    }

    public static function form(Schema $schema): Schema
    {
        return DeadlineForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeadlineInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeadlineTables::configure($table);
    }

    /**
     * @return Builder<Deadline>
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user instanceof User) {
            return app(DeadlinePolicy::class)->scopeVisibleTo($query, $user);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeadlines::route('/'),
            'create' => Pages\CreateDeadline::route('/create'),
            'view' => Pages\ViewDeadline::route('/{record}/view'),
            'edit' => Pages\EditDeadline::route('/{record}/edit'),
        ];
    }
}
