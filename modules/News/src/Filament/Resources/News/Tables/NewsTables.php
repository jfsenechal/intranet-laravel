<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Tables;

use AcMarche\News\Models\News;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class NewsTables
{
    public static function configure(Table $table): Table
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
                        ->description(fn (News $record): string => Str::limit(
                            mb_trim(html_entity_decode(strip_tags($record->content))),
                            250,
                            ' (...)',
                        ), position: 'below')
                        ->color(Color::Green)
                        ->tooltip(function (TextColumn $column): ?string {
                            $state = $column->getState();

                            if (mb_strlen($state) <= $column->getCharacterLimit()) {
                                return null;
                            }

                            // Only render the tooltip if the column content exceeds the length limit.
                            return $state;
                        }),
                    Split::make([
                        TextColumn::make('category.name')
                            ->label('Catégorie')
                            ->badge()
                            ->color(Color::Green)
                            ->grow(false),
                        TextColumn::make('department')
                            ->label('Service')
                            ->badge()
                            ->grow(false),
                        TextColumn::make('created_at')
                            ->label('Ajouté le')
                            ->size('xs')
                            ->color('gray')
                            ->icon(Heroicon::Clock)
                            ->dateTime('d/m/Y H:i')
                            ->grow(false),
                        TextColumn::make('user_add')
                            ->label('Par')
                            ->size('xs')
                            ->color('gray')
                            ->icon(Heroicon::User)
                            ->placeholder('—'),
                    ])->extraAttributes(['class' => 'gap-2 items-center mt-3']),
                ]),
            ])
            ->filters([
                Filter::make('name')
                    ->label('Intitulé')
                    ->schema([
                        TextInput::make('name')->label('Intitulé'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['name'],
                        fn (Builder $query, string $name): Builder => $query->where('name', 'like', "%{$name}%"),
                    )),
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
                    ->label('Ajouté le')
                    ->columnSpan(2)
                    ->schema([
                        Flex::make([
                            DatePicker::make('created_from')->label('Entre le'),
                            DatePicker::make('created_until')->label('Et le'),
                        ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
