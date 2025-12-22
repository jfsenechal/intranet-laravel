<?php

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Declarations\Schemas\DeclarationForm;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

final class EditDeclaration extends EditRecord
{
    protected static string $resource = DeclarationResource::class;

    public function form(Schema $schema): Schema
    {
        return DeclarationForm::editFormForAdmin($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
