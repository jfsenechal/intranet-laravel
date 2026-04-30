<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Schemas;

use AcMarche\Hrm\Enums\EvaluationResultEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Évaluation')
                    ->columns(2)
                    ->schema([
                        Select::make('direction_id')
                            ->label('Direction')
                            ->relationship('direction', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('result')
                            ->label('Résultat')
                            ->options(EvaluationResultEnum::class)
                            ->enum(EvaluationResultEnum::class)
                            ->required(),
                    ]),
                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('evaluation_date')
                            ->label('Date évaluation')
                            ->helperText('Validé par le Collège, BP ou CA')
                            ->required(),
                        DatePicker::make('next_evaluation_date')
                            ->label('Prochaine évaluation'),
                        DatePicker::make('validation_date')
                            ->label('Date de validation'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Notes')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
                Section::make('Documents')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('file1_name')
                            ->label('Document 1')
                            ->disk('public')
                            ->visibility('public')
                            ->directory(config('hrm.uploads.evaluations')),
                        FileUpload::make('file2_name')
                            ->label('Document 2')
                            ->disk('public')
                            ->visibility('public')
                            ->directory(config('hrm.uploads.evaluations')),
                    ]),
            ]);
    }
}
