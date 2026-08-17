<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations;

use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use AcMarche\Hrm\Filament\Resources\Evaluations\Schemas\EvaluationInfolist;
use AcMarche\Hrm\Models\Evaluation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EvaluationResource extends Resource
{
    #[Override]
    protected static ?string $model = Evaluation::class;

    /**
     * Evaluations are always reached through their employee, never listed on their own.
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return 'Évaluation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Évaluations';
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewEvaluation::route('/{record}/view'),
        ];
    }

    /**
     * Evaluations have no index page; send breadcrumbs and default routing to the employees list.
     */
    #[Override]
    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return EmployeeResource::getUrl('index', [], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }
}
