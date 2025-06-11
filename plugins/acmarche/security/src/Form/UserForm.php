<?php


namespace AcMarche\Security\Form;

use Filament\Schemas\Schema;

class UserForm
{
    public static function createForm(Schema $form): Schema
    {
        return $form
            ->schema([]);
    }

}
