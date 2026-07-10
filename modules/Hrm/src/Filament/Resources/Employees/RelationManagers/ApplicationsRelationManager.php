<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Applications\Schemas\ApplicationForm;
use AcMarche\Hrm\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use AcMarche\Hrm\Filament\Resources\Applications\Tables\ApplicationTables;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Models\Employee;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ApplicationsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;

    #[Override]
    protected static string $relationship = 'applications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Candidatures';
    }

    public function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ApplicationTables::configure($table);
    }

    public function content(Schema $schema): Schema
    {
        /** @var Employee $employee */
        $employee = $this->getOwnerRecord();

        return $schema->components([
            $this->getTabsContentComponent(),
            RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_BEFORE),
            Section::make('Candidat')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    TextEntry::make('diploma_level')
                        ->label('Niveau de diplôme')
                        ->state($employee->diploma_level)
                        ->placeholder('—'),
                    TextEntry::make('diploma_nature')
                        ->label('Nature du diplôme')
                        ->state($employee->diploma_nature)
                        ->placeholder('—'),
                ]),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_AFTER),
        ]);
    }
}
