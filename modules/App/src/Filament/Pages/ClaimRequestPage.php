<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\App\Filament\Schemas\ClaimRequestForm;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Override;

final class ClaimRequestPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-home-modern';

    #[Override]
    protected static ?string $navigationLabel = 'Déclaration de créance';

    #[Override]
    protected string $view = 'app::filament.pages.claim-request';

    public function getTitle(): string
    {
        return 'Générer une déclaration de créance';
    }

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        $data = [];
        $data['last_name'] = $user->last_name;
        $data['first_name'] = $user->first_name;

        if ($personalInformation = PersonalInformationRepository::getByCurrentUser()->first()) {
            $data['iban'] = $personalInformation->iban;
            $data['street'] = $personalInformation->street;
            $data['postal_code'] = $personalInformation->postal_code;
            $data['city'] = $personalInformation->city;
        }

        $this->form->fill($data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return ClaimRequestForm::configure($schema)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        /**
         * generate a pdf with text belows
         */
        Notification::make()
            ->title('Enregistré')
            ->success()
            ->send();
    }

    /**
<div class="container">

    <div class="card">
        <h3 class="card-header"> DECLARATION DE CREANCE</h3>
    </div>

    <div class="card-body">
        <br/><br/>
        Je soussigné, <strong>{{ data.nom | upper }} {{ data.prenom }}</strong><br/>
        <br/> domicilié {{ data.rue }} à {{ data.code_postal }} {{ data.localite }}
        <br/>
        <br/>
        déclare qu’il est dû par la Ville de Marche-en-Famenne<br/><br/>
        <h3 class="text-center">
            la somme de {{ data.montant|number_format(2, ',', '.') }} €.<br/><br/>
        </h3>
        pour {{ data.description | nl2br }}<br/><br/>
        <br/><br/>

        A verser au compte n° {{ data.iban }}<br/><br/>

        Certifié sincère et véritable à la somme de <strong>{{ data.montant_fr }} euros</strong>

        <br/><br/>
        <br/><br/><br/><br/>
        <p class="text-right" style="margin-right: 70px;"> Fait à Marche-en-Famenne,
            le {{ data.date | date('d-m-Y') }}</p>
        <br/><br/>
        <br/><br/><br/><br/><br/><br/>
        <p class="text-right" style="margin-right: 70px;"> Signature</p>
        <br/><br/>
    </div>

</div>
     */
}
