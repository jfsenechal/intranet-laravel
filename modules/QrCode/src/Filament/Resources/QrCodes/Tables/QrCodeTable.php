<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Tables;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Models\QrCode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class QrCodeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable(),
                ColorColumn::make('color')
                    ->label('Couleur'),
                TextColumn::make('format')
                    ->label('Format'),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options(QrCodeActionEnum::class),
            ])
            ->recordActions([
                ViewAction::make()->icon('tabler-eye'),
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (QrCode $record) => self::download($record)),
                EditAction::make()->icon('tabler-edit'),
                DeleteAction::make()->icon('tabler-trash'),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function download(QrCode $record): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $generator = app(\AcMarche\QrCode\Service\QrCodeGenerator::class);
        $content = $generator->render($record);
        $mime = $generator->mimeType($record);
        $extension = $generator->extension($record);
        $filename = \Illuminate\Support\Str::slug($record->name ?? 'qrcode').'.'.$extension;

        return response()->streamDownload(
            fn () => print $content,
            $filename,
            ['Content-Type' => $mime],
        );
    }
}
