<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes;

use AcMarche\Hrm\Filament\Resources\Processes\Pages\CreateProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\EditProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\ListProcesses;
use AcMarche\Hrm\Filament\Resources\Processes\Schemas\ProcessForm;
use AcMarche\Hrm\Filament\Resources\Processes\Tables\ProcessesTable;
use AcMarche\Hrm\Models\Process;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ProcessResource extends Resource
{
    #[Override]
    protected static ?string $model = Process::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Processus';

    #[Override]
    protected static ?string $modelLabel = 'processus';

    #[Override]
    protected static ?string $pluralModelLabel = 'processus';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProcessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcessesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProcesses::route('/'),
            'create' => CreateProcess::route('/create'),
            'edit' => EditProcess::route('/{record}/edit'),
        ];
    }
}
