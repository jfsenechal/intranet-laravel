<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Odd\Tables;

use AcMarche\Pst\Filament\Resources\Odd\OddResource;
use AcMarche\Pst\Models\Odd;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OddTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated(false)
            ->recordUrl(fn (Odd $record): string => OddResource::getUrl('view', [$record]))
            ->columns([
                Stack::make([
                    ImageColumn::make('odd_image')
                        ->defaultImageUrl(fn (Odd $record): string => url(sprintf(
                            '/images/odds/F_SDG_Icons-01-%02d-300x300.jpg',
                            $record->id
                        )))
                        ->state(fn (Odd $record): string => url(sprintf(
                            '/images/odds/F_SDG_Icons-01-%02d-300x300.jpg',
                            $record->id
                        )))
                        ->visibility('public')
                        ->imageSize(120)
                        ->extraImgAttributes([
                            'class' => 'rounded-lg shadow-md mx-auto',
                        ]),
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->alignment(Alignment::Center)
                        ->extraAttributes(['class' => 'mt-3']),
                    TextColumn::make('description')
                        ->limit(100)
                        ->alignment(Alignment::Center)
                        ->color('gray')
                        ->size(TextSize::Small)
                        ->extraAttributes(['class' => 'mt-1']),
                    TextColumn::make('actions_for_department_count')
                        ->counts('actionsForDepartment')
                        ->badge()
                        ->color('success')
                        ->formatStateUsing(fn (int $state): string => $state.' '.trans_choice('action|actions', $state))
                        ->alignment(Alignment::Center)
                        ->extraAttributes(['class' => 'mt-3']),
                ])->space(1),
            ])
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'lg' => 4,
                'xl' => 5,
            ])
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([

            ]);
    }
}
