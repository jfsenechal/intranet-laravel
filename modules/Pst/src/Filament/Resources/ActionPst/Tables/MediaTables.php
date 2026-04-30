<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Tables;

use AcMarche\Pst\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

final class MediaTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('download')
                    ->label('Téléchargement')
                    ->state('Télécharger')
                    ->icon('tabler-download')
                    ->action(fn (Media $media) => Storage::disk('public')->download($media->file_name)),
                TextColumn::make('size')
                    ->label('Taille')
                    ->suffix('Ko'),
                TextColumn::make('mime_type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
