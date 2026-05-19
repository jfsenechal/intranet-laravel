<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Tables;

use AcMarche\Ad\Models\ClassifiedAd;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class ClassifiedAdTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('category')->where('archive', '!=', '1'))
            ->columns([
                Stack::make([
                    ImageColumn::make('cover')
                        ->state(fn (ClassifiedAd $record): ?string => self::firstImage($record))
                        ->visibility('public')
                        ->disk('public')
                        ->height(160)
                        ->extraImgAttributes([
                            'class' => 'w-full rounded-t-lg object-cover',
                        ]),
                    TextColumn::make('name')
                        ->label('Intitulé')
                        ->limit(120)
                        ->weight('bold')
                        ->size('md')
                        ->description(fn (ClassifiedAd $record): string => Str::limit($record->content, 250, ' (...)'),
                            position: 'below')
                        ->color(Color::Green)
                        ->tooltip(function (TextColumn $column): ?string {
                            $state = $column->getState();

                            if (mb_strlen($state) <= $column->getCharacterLimit()) {
                                return null;
                            }

                            // Only render the tooltip if the column content exceeds the length limit.
                            return $state;
                        }),
                    TextColumn::make('category.name')
                        ->label('Catégorie')
                        ->badge()
                        ->color('gray'),
                ])->space(2),
            ])
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                TernaryFilter::make('archive')
                    ->label('Archivé')
                    ->boolean()
                    ->trueLabel('Archivés seulement')
                    ->falseLabel('Non archivés seulement')
                    ->native(false),
                Filter::make('created_at')
                    ->label('Ajouté le')->schema([
                        Flex::make([
                            DatePicker::make('created_from')->label('Entre le'),
                            DatePicker::make('created_until')->label('Et le'),
                        ]),
                    ])->query(fn (Builder $query, array $data): Builder => $query
                    ->when(
                        $data['created_from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                    )),
            ])
            ->filtersFormWidth(Width::FourExtraLarge)
            ->recordActions([
                ViewAction::make()
                    ->visible(false),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function firstImage(ClassifiedAd $record): ?string
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];

        foreach ((array) ($record->medias ?? []) as $media) {
            if (! is_string($media) || $media === '') {
                continue;
            }

            $extension = mb_strtolower(pathinfo($media, PATHINFO_EXTENSION));

            if (in_array($extension, $imageExtensions, true)) {
                return $media;
            }
        }

        return null;
    }
}
