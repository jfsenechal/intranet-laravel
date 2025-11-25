<?php

namespace AcMarche\Publication\Filament\Resources;

use AcMarche\Publication\Filament\Resources\PublicationResource\Pages;
use AcMarche\Publication\Models\Publication;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Publications';

    protected static ?string $modelLabel = 'Publication';

    protected static ?string $pluralModelLabel = 'Publications';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make(
                    [
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('wpCategoryId')
                                    ->label('WP Category ID')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('expire_date')
                            ->label('Expire Date'),
                    ]
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('expire_date')
                    ->label('Expire Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdAt')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updatedAt')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'view' => Pages\ViewPublication::route('/{record}'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
        ];
    }
}
