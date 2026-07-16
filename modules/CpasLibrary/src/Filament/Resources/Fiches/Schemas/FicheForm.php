<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Schemas;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class FicheForm
{
    /**
     * Uploads land on a `local` disk, where an unrestricted file type would allow a
     * `.php` upload to be stored with its client extension and executed. Keep this
     * list to formats that cannot be interpreted as code by the web server.
     *
     * @var list<string>
     */
    private const ACCEPTED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/png',
        'image/jpeg',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'slug',
                                Str::slug($state ?? ''),
                            )),
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        // Type is chosen by the user from the "Nouvelle fiche" dropdown
                        // (pre-filled from the query string in CreateFiche); it is carried
                        // in the form but hidden rather than editable here.
                        Hidden::make('type')
                            ->default(FicheTypeEnum::DEFAULT->value),
                        Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Contenu')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        FileUpload::make('fileName')
                            ->label('Fichier')
                            ->disk('cpas-library')
                            ->visibility('private')
                            ->acceptedFileTypes(self::ACCEPTED_MIME_TYPES)
                            ->maxSize(51200)
                            ->helperText('50 Mo maximum. PDF, Word, Excel, PowerPoint ou image.')
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('fileSize', $state->getSize());
                                    $set('mimeType', $state->getMimeType());

                                    return;
                                }

                                if ($state === null) {
                                    $set('fileSize', null);
                                    $set('mimeType', null);
                                }
                            })
                            ->columnSpanFull(),
                        Hidden::make('fileSize'),
                        Hidden::make('mimeType'),
                    ]),

                Section::make('Rappel')
                    ->schema([
                        DatePicker::make('date_rappel')
                            ->label('Date de rappel')
                            ->helperText('Un mail sera envoyé aux utilisateurs ayant accès à la librairie à cette date choisie'),
                    ]),

                Section::make('Absence')
                    ->key('absence-section')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => self::isType($get, FicheTypeEnum::ABSENCE))
                    ->schema([
                        DatePicker::make('date_begin')
                            ->label('Date de début')
                            ->required()
                            ->beforeOrEqual('date_end'),
                        DatePicker::make('date_end')
                            ->label('Date de fin')
                            ->required()
                            ->afterOrEqual('date_begin'),
                    ]),

                Section::make('Législation')
                    ->key('legislation-section')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => self::isType($get, FicheTypeEnum::LEGISLATION))
                    ->schema([
                        TextInput::make('type_document')
                            ->label('Type de document')
                            ->helperText('(circulaire - arrêté - loi…)')
                            ->maxLength(255),
                        TextInput::make('source')
                            ->label('Source')
                            ->helperText('SPP - SPW - CWB…')
                            ->maxLength(255),
                        DatePicker::make('date_promulgation')
                            ->label('Date de promulgation'),
                        DatePicker::make('date_publication')
                            ->label('Date de publication'),
                    ]),
            ]);
    }

    /**
     * Filament casts the `type` Select state to a FicheTypeEnum instance, but a
     * record loaded from the database exposes it as a raw string. Normalise both
     * before comparing so the conditional sections resolve in every context.
     */
    private static function isType(Get $get, FicheTypeEnum $type): bool
    {
        $state = $get('type');

        if ($state instanceof FicheTypeEnum) {
            return $state === $type;
        }

        return $state === $type->value;
    }
}
