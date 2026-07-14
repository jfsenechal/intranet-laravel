<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\VisibleWhenEmployeeIsViewable;
use AcMarche\Hrm\Filament\Resources\Internships\Schemas\InternshipForm;
use AcMarche\Hrm\Filament\Resources\Internships\Schemas\InternshipInfolist;
use AcMarche\Hrm\Filament\Resources\Internships\Tables\InternshipTables;
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
use Illuminate\Support\Facades\Storage;
use Override;

final class InternshipsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;
    use VisibleWhenEmployeeIsViewable;

    #[Override]
    protected static string $relationship = 'internships';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Stages';
    }

    public function form(Schema $schema): Schema
    {
        return InternshipForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return InternshipInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return InternshipTables::configure($table);
    }

    public function content(Schema $schema): Schema
    {
        /** @var Employee $employee */
        $employee = $this->getOwnerRecord();

        return $schema->components([
            $this->getTabsContentComponent(),
            RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_BEFORE),
            Section::make('Stagiaire')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(3)
                ->schema([
                    TextEntry::make('intern_type')
                        ->label('Demande de stage')
                        ->state($employee->intern_type)
                        ->placeholder('—'),
                    TextEntry::make('diploma_level_simplified')
                        ->label('Niveau de diplôme')
                        ->state($employee->diploma_level_simplified)
                        ->placeholder('—'),
                    TextEntry::make('candidate_file_name')
                        ->label('Document du stagiaire')
                        ->state($employee->candidate_file_name)
                        ->placeholder('—')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                        ->url(
                            fn (?string $state): ?string => $state ? Storage::disk('local')->temporaryUrl(
                                $state,
                                now()->addMinutes(5)
                            ) : null
                        )
                        ->openUrlInNewTab(),
                ]),
            Section::make('Etudiant')
                ->icon('heroicon-o-academic-cap')
                ->columns(2)
                ->schema([
                    TextEntry::make('diploma_nature')
                        ->label('Nature du diplôme')
                        ->state($employee->diploma_nature)
                        ->placeholder('—'),
                    TextEntry::make('diploma_level')
                        ->label('Niveau de diplôme')
                        ->state($employee->diploma_level)
                        ->placeholder('—'),
                ]),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_AFTER),
        ]);
    }
}
