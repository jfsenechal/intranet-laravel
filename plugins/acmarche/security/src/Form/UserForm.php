<?php


namespace AcMarche\Security\Form;

use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

}
