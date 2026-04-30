<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Declarations\Schemas\DeclarationForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Override;

final class EditDeclaration extends EditRecord
{
    #[Override]
    protected static string $resource = DeclarationResource::class;

    public function form(Schema $schema): Schema
    {
        return DeclarationForm::editFormForAdmin($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
