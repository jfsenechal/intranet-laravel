<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Schemas;

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

final class EmployeForm
{
    /**
     * @return array<int, Section>
     */
    public static function forEmail(): array
    {
        return [
            Section::make('Adresse')
                ->columns()
                ->components([
                    TextInput::make('mail')
                        ->label('Adresse mail')
                        ->required()
                        ->maxLength(200)
                        ->rule('regex:/^[A-Za-z0-9._%+-]+$/')
                        ->validationMessages([
                            'regex' => "L'adresse ne peut pas contenir de @ : le domaine se choisit à côté.",
                        ])
                        ->helperText('Sans le domaine, par exemple prenom.nom'),
                    Select::make('extension')
                        ->label('Domaine')
                        ->options(EmailExtensionEnum::class)
                        ->default(EmailExtensionEnum::EXTENSION_AC)
                        ->selectablePlaceholder(false)
                        ->required(),
                    Checkbox::make('force')
                        ->label('Forcer')
                        ->helperText(
                            "Enregistrer même si l'adresse est déjà utilisée par une autre fiche de l'annuaire."
                        )
                        ->columnSpanFull(),
                ]),
        ];
    }
}
