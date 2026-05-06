<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\ActionRoadmapEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\ActionTypeEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Enums\YesOrNoEnum;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class ActionForm
{
    public static function configure(Schema $schema, Model|OperationalObjective|null $owner): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Wizard::make([
                    Step::make('project')
                        ->label('Projet')
                        ->schema(
                            self::fieldsProject($owner),
                        ),
                    Step::make('team')
                        ->label('Equipes')
                        ->schema(self::fieldsTeam())
                        ->visible(
                            fn (
                                Action|Model|null $record = null,
                                ?string $operation = null
                            ): bool => $operation === 'create' || ($record !== null && Gate::check(
                                'teams-edit',
                                [
                                    $record,
                                    $operation,
                                ]
                            ))
                        ),
                    Step::make('info')
                        ->label('Informations')
                        ->schema(
                            self::fieldsDescription(),
                        ),
                    Step::make('odd')
                        ->label('Odds')
                        ->schema(
                            self::fieldsOdd(),
                        ),
                    Step::make('financing')
                        ->label('Financement')
                        ->schema(
                            self::fieldsFinancing(),
                        ),
                ])
                    ->skippable()
                    ->nextAction(
                        fn (Action $action): Action => $action
                            ->label('Suivant')
                            ->color('success'),
                    )->previousAction(
                        fn (Action $action): Action => $action
                            ->label('Précédent')
                            ->color('secondary'),
                    )
                    ->submitAction(view('pst::components.btn_add')),
            ]);
    }

    public static function fieldsReminder(): array
    {
        return
            [
                Select::make('recipients')
                    ->label('Destinataires')
                    ->options(fn () => User::query()
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn ($user): array => [$user->username => "{$user->last_name} {$user->first_name}"]))
                    ->multiple()
                    ->searchable()
                    ->required(),
                TextInput::make('subject')
                    ->label('Sujet')
                    ->required(),
                Textarea::make('content')
                    ->label('Contenu')
                    ->required(),
            ];
    }

    private static function fieldsProject(Model|OperationalObjective|null $owner): array
    {
        return [
            Section::make('Identification')
                ->columns(2)
                ->schema([
                    Flex::make([
                        TextInput::make('name')
                            ->label('Intitulé')
                            ->required()
                            ->readOnly(
                                fn (?string $operation = null): bool => $operation === 'edit' && ! auth()->user()->hasRole(
                                    RolesEnum::ADMIN->value
                                )
                            )
                            ->maxLength(255),
                        ToggleButtons::make('validated')
                            ->label('Validée')
                            ->options(YesOrNoEnum::class)
                            ->inline()
                            ->default(fn (): ?int => UserRepository::departmentSelected() === DepartmentEnum::CPAS->value || auth()->user()->hasRole(RolesEnum::ADMIN->value) ? YesOrNoEnum::YES->value : null)
                            ->visible(fn () => auth()->user()->hasRole(RolesEnum::ADMIN->value))
                            ->grow(false),
                    ])
                        ->grow(true)
                        ->columnSpanFull(),
                    Select::make('operational_objective_id')
                        ->label('Objectif opérationnel')
                        ->relationship(
                            name: 'operationalObjective',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->where(function (Builder $query): void {
                                    $query->forSelectedDepartment()
                                        ->orWhereNull('department');
                                })
                                ->orderBy('name', 'asc')
                        )
                        ->searchable(['name'])
                        ->disabled(
                            fn (?string $operation = null): bool => $operation === 'edit' && ! auth()->user()->hasRole(
                                RolesEnum::ADMIN->value
                            )
                        )
                        ->preload()
                        ->required()
                        ->visible(fn (): bool => ! $owner instanceof Model)
                        ->columnSpanFull(),
                ]),
            Section::make('Progression')
                ->columns(3)
                ->schema([
                    Select::make('state')
                        ->label('État d\'avancement')
                        ->required()
                        ->options(ActionStateEnum::class)
                        ->suffixIcon('tabler-ladder'),
                    TextInput::make('state_percentage')
                        ->label('Pourcentage')
                        ->suffixIcon('tabler-percentage')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(100),
                    ToggleButtons::make('type')
                        ->label('Type')
                        ->default(ActionTypeEnum::PST->value)
                        ->options(ActionTypeEnum::class)
                        ->disabled(
                            fn (?string $operation = null): bool => $operation === 'edit' && ! auth()->user()->hasRole(
                                RolesEnum::ADMIN->value
                            )
                        )
                        ->inline(),
                ]),
            Section::make('Options')
                ->columns(4)
                ->schema([
                    ToggleButtons::make('roadmap')
                        ->label('Feuille de route')
                        ->options(ActionRoadmapEnum::class)
                        ->visible(fn () => auth()->user()->hasRole(RolesEnum::ADMIN->value))
                        ->inline(),
                    ToggleButtons::make('scope')
                        ->label('Volet')
                        ->options(ActionScopeEnum::class)
                        ->required()
                        ->inline(),
                    ToggleButtons::make('synergy')
                        ->label(ActionSynergyEnum::getTitle())
                        ->helperText(ActionSynergyEnum::getDescription())
                        ->options(ActionSynergyEnum::class)
                        ->required()
                        ->inline(),
                    DatePicker::make('due_date')
                        ->label('Date d\'échéance')
                        ->suffixIcon('tabler-calendar-stats'),
                ]),
            RichEditor::make('description'),
        ];
    }

    private static function fieldsTeam(): array
    {
        return [
            Fieldset::make('Mandataires et agents')
                ->schema([
                    Select::make('action_mandatory')
                        ->label('Mandataires')
                        ->relationship(
                            name: 'mandataries',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->whereHas(
                                    'roles',
                                    fn (Builder $query) => $query->where('name', RolesEnum::MANDATAIRE->value)
                                )
                                ->orderBy('last_name')
                                ->orderBy('first_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record): string => "{$record->first_name} {$record->last_name}"
                        )
                        ->searchable(['first_name', 'last_name'])
                        ->multiple()
                        ->preload(),
                    Select::make('action_users')
                        ->label('Agents pilotes')
                        ->helperText('Les agents pilotes ont le droit de modifier l\'action.')
                        ->relationship(
                            name: 'users',
                            modifyQueryUsing: fn (Builder $query) => $query->forSelectedDepartment()->orderBy('last_name')
                                ->orderBy('first_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record): string => "{$record->first_name} {$record->last_name}"
                        )
                        ->searchable(['first_name', 'last_name'])
                        ->multiple(),
                ])
                ->columns(3),
            Fieldset::make('Services porteurs et partenaires externes')
                ->schema([
                    Select::make('action_service_leader')
                        ->label('Services porteurs')
                        ->helperText('L\'agent membre des services porteurs, peut modifier l\'action.')
                        ->relationship(name: 'leaderServices', titleAttribute: 'name')
                        ->preload()
                        ->multiple()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                        ]),
                    Select::make('action_service_partner')
                        ->label('Services partenaires')
                        ->relationship(name: 'partnerServices', titleAttribute: 'name')
                        ->preload()
                        ->multiple()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                        ]),
                ])
                ->columns(2),
            Select::make('partners')
                ->label('Partenaires externes')
                ->relationship(name: 'partners', titleAttribute: 'name')
                ->multiple()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required(),
                ]),
            Select::make('action_related')
                ->label('Actions liés')
                ->relationship(
                    name: 'linkedActions',
                    titleAttribute: 'name',
                )
                ->searchable(['actions.id', 'actions.name'])
                ->getOptionLabelFromRecordUsing(
                    fn (Model $record): string => "{$record->id}. {$record->name}"
                )
                ->multiple(),
        ];
    }

    private static function fieldsFinancing(): array
    {
        return [
            Textarea::make('budget_estimate')
                ->label('Budget estimé'),

            Textarea::make('financing_mode')
                ->label('Mode de financement'),
        ];
    }

    private static function fieldsDescription(): array
    {
        return [
            Textarea::make('evaluation_indicator')
                ->label('Indicateur d\'évaluation'),
            Textarea::make('work_plan')
                ->label('Plan de travail'),
        ];
    }

    private static function fieldsOdd(): array
    {
        return [
            Select::make('odds')
                ->label('Odds')
                ->relationship(name: 'odds')
                ->getOptionLabelFromRecordUsing(
                    fn (Model $record): string => "{$record->id}. {$record->name}"
                )
                ->multiple()
                ->preload(),
        ];
    }
}
