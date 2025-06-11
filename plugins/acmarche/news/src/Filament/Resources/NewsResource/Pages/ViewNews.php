<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Pages;

use AcMarche\News\Constant\DepartmentEnum;
use AcMarche\News\Filament\Resources\NewsResource;
use AcMarche\News\Models\News;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ViewNews extends ViewRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('end_date')
                    ->icon('tabler-mail')
                    ->dateTime(),
                TextEntry::make('department')
                    ->formatStateUsing(fn($state) => DepartmentEnum::tryFrom($state)?->getLabel() ?? 'Unknown')
                    ->icon(
                        fn($state) => DepartmentEnum::tryFrom($state)?->getIcon() ?? 'heroicon-m-question-mark-circle'
                    )
                    ->color(fn($state) => DepartmentEnum::tryFrom($state)?->getColor() ?? 'gray')
                    ->icon('tabler-mail'),
                TextEntry::make('content')
                    ->label(false)
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                ImageEntry::make('medias')
                    ->disk('uploads/news'),
                Fieldset::make('actions')
                    ->label('Actions liés')
                    ->schema([
                        RepeatableEntry::make('actions')
                            ->label(false)
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom')
                                    ->columnSpanFull()
                                    ->url(
                                        fn(News $record): string => NewsResource::getUrl(
                                            'view',
                                            ['record' => $record]
                                        )
                                    ),
                            ]),
                    ]),
            ]);
    }
}
