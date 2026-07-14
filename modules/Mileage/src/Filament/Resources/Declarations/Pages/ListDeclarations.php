<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Repository\DeclarationRepository;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ListDeclarations extends ListRecords
{
    #[Override]
    protected static string $resource = DeclarationResource::class;

    public function getTitle(): string
    {
        return 'Mes déclarations';
    }

    public function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => DeclarationRepository::getByUser($query));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle déclaration')
                ->icon('tabler-plus'),
        ];
    }
}
