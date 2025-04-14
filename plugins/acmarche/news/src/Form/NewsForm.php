<?php

namespace AcMarche\News\Form;

use AcMarche\News\Constant\DepartmentEnum;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;

class NewsForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Titre')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\Select::make('department')
                    ->label('Département')
                    ->default(DepartmentEnum::COMMON->value)
                    ->options(DepartmentEnum::class)
                    ->required()
                    ->suffixIcon('tabler-ladder'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Date de fin de publication')
                    ->default(Carbon::make('now')->add('2 weeks'))
                    ->required()
                    ->suffixIcon('tabler-calendar-stats'),
                Forms\Components\RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('medias')
                    ->label('Pièces jointes')
                    ->required()
                    ->maxFiles(3)
                    ->disk('public')
                    ->directory('uploads/news')
                    //->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    //->preserveFilenames()
                    ->multiple()
                    ->previewable(false)
                    ->downloadable()
                    ->maxSize(10240),
            ]);
    }
}
