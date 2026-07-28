<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Resources\Employees;

use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Filament\Pages\Index;
use AcMarche\WhoIsWho\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\WhoIsWho\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;

/**
 * Read-only detail page for a directory entry. Listing is handled by the
 * Index, Search and Services pages, so this resource only exposes a view page.
 */
final class EmployeeResource extends Resource
{
    #[Override]
    protected static ?string $model = Employee::class;

    #[Override]
    protected static ?string $recordTitleAttribute = 'last_name';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return 'Agent';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Agents';
    }

    public static function getRecordTitle(?Model $record): string
    {
        if (! $record instanceof Employee) {
            return self::getModelLabel();
        }

        return mb_trim($record->last_name.' '.$record->first_name);
    }

    /**
     * Restricted to active, non-archived agents so an entry that is absent from
     * the directory cannot be reached by guessing a record id.
     *
     * @return Builder<Employee>
     */
    public static function getEloquentQuery(): Builder
    {
        return EmployeeRepository::activeAgentsQuery()
            ->with('activeContracts.direction');
    }

    /**
     * The directory is open to every authenticated user, exactly like the
     * Index, Search and Services pages of this panel. The HRM `EmployeePolicy`
     * is deliberately bypassed: it gates confidential HR records behind HR
     * roles, whereas this page only repeats the professional contact details
     * already listed on the directory pages.
     */
    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canView(Model $record): bool
    {
        return Auth::check();
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    /**
     * The resource has no index page of its own: breadcrumbs and "back" links
     * point at the A → Z directory page instead.
     *
     * @param  array<mixed>  $parameters
     */
    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return Index::getUrl($parameters, $isAbsolute, $panel);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'view' => ViewEmployee::route('/{record}'),
        ];
    }
}
