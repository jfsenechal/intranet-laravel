<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas\CategoryInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Override;

final class ViewCategory extends ViewRecord
{
    #[Override]
    protected static string $resource = CategorieResource::class;

    public function getTitle(): string
    {
        $parentName = $this->record->parent?->name;

        return $parentName !== null
            ? $parentName.' › '.$this->record->name
            : (string) $this->record->name;
    }

    /**
     * Same as the title, but the parent name links to its own view page,
     * like a breadcrumb. The title stays plain text for the browser tab.
     */
    public function getHeading(): string|Htmlable
    {
        $parent = $this->record->parent;

        if ($parent === null) {
            return (string) $this->record->name;
        }

        return new HtmlString(sprintf(
            '<a href="%s" style="color: var(--primary-600)">%s</a> › %s',
            e(self::getUrl(['record' => $parent])),
            e($parent->name),
            e($this->record->name),
        ));
    }

    public function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
