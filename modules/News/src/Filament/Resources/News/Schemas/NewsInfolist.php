<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Schemas;

use AcMarche\News\Enums\DepartmentEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

final class NewsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    Section::make([
                        TextEntry::make('name')
                            ->hiddenLabel()
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large),
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->formatStateUsing(function (?string $state): string {
                                $state ??= '';

                                // Legacy entries are plain text with newlines; newer
                                // entries are already HTML from the rich editor.
                                if (strip_tags($state) === $state) {
                                    return nl2br(e($state));
                                }

                                return $state;
                            })
                            ->columnSpanFull(),
                        ViewEntry::make('medias')
                            ->hiddenLabel()
                            ->view('news::filament.components.media-gallery'),
                    ]),
                    Section::make([
                        TextEntry::make('category.name')
                            ->label('Catégorie')
                            ->badge()
                            ->icon('tabler-folder'),
                        TextEntry::make('department')
                            ->label('Pour qui ?')
                            ->badge()
                            ->formatStateUsing(fn ($state) => self::resolveDepartment($state)?->getLabel() ?? 'Inconnu')
                            ->icon(fn ($state) => self::resolveDepartment($state)?->getIcon() ?? 'tabler-help')
                            ->color(fn ($state) => self::resolveDepartment($state)?->getColor() ?? 'gray'),
                        TextEntry::make('end_date')
                            ->label('Fin de publication')
                            ->date()
                            ->icon('tabler-calendar-stats'),
                    ])->grow(false),
                ])->from('md'),
                Section::make()
                    ->columns(3)
                    ->components([
                        TextEntry::make('user_add')
                            ->label('Auteur')
                            ->icon('tabler-user'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->icon('tabler-clock-plus'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->icon('tabler-clock-edit'),
                    ]),
            ]);
    }

    /**
     * Resolve a department value into its enum instance. The attribute is
     * cast to DepartmentEnum on the model, so state arrives as an enum, but
     * legacy rows may still surface a raw string.
     */
    private static function resolveDepartment(DepartmentEnum|string|null $state): ?DepartmentEnum
    {
        if ($state instanceof DepartmentEnum) {
            return $state;
        }

        return $state !== null ? DepartmentEnum::tryFrom($state) : null;
    }
}
