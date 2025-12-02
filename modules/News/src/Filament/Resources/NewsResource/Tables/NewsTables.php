<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Tables;

use AcMarche\News\Filament\Resources\NewsResource;
use AcMarche\News\Models\News;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class NewsTables
{
    public static function configure(Table $table)
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('category')->where('archive', '!=', '1'))
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label('Intitulé')
                        ->limit(120)
                        ->weight('bold')
                        ->size('md')
                        ->description(fn (News $record): string => Str::limit($record->content, 250, ' (...)'), position: 'below')
                        ->color(Color::Green)
                        ->url(fn (News $record) => NewsResource::getUrl('view', ['record' => $record->id]))
                        ->tooltip(function (TextColumn $column): ?string {
                            $state = $column->getState();

                            if (mb_strlen($state) <= $column->getCharacterLimit()) {
                                return null;
                            }

                            // Only render the tooltip if the column content exceeds the length limit.
                            return $state;
                        }),
                ]),
                TextColumn::make('category.name')
                    ->label('Catégorie'),
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
                Tables\Filters\Filter::make('created_at')
                    ->label('Ajouté le')->schema([
                        Flex::make([
                            DatePicker::make('created_from')->label('Entre le'),
                            DatePicker::make('created_until')->label('Et le'),
                        ]),
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)->filtersFormWidth(Width::FourExtraLarge)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
