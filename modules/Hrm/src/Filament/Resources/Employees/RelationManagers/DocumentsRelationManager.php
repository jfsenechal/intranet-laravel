<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns\ReadOnlyUnlessGrhAdmin;
use AcMarche\Hrm\Filament\Resources\HrDocuments\Schemas\HrDocumentForm;
use AcMarche\Hrm\Filament\Resources\HrDocuments\Schemas\HrDocumentInfolist;
use AcMarche\Hrm\Filament\Resources\HrDocuments\Tables\HrDocumentTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class DocumentsRelationManager extends RelationManager
{
    use ReadOnlyUnlessGrhAdmin;

    #[Override]
    protected static string $relationship = 'documents';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Documents';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(HrDocumentForm::getSchema());
    }

    public function infolist(Schema $schema): Schema
    {
        return HrDocumentInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return HrDocumentTables::configure($table);
    }
}
