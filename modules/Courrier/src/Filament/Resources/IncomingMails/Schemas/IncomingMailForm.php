<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Actions\AnalyzeAttachmentAction;
use AcMarche\Courrier\Filament\Components\DepartmentField;
use AcMarche\Courrier\Models\Category;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Sender;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\RecipientRepository;
use AcMarche\Courrier\Repository\ServiceRepository;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

final class IncomingMailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::getComponents());
    }

    /**
     * @param  array<string, mixed>|null  $imapPreview  IMAP preview context: ['url', 'contentType', 'filename']
     * @param  array{uid: int, index: int, mailbox: string}|null  $imapSource  IMAP coordinates of the document, so
     *                                                                         the AI action can fetch it
     */
    public static function getComponents(?array $imapPreview = null, ?array $imapSource = null): array
    {
        return [
            Grid::make(['default' => 1, 'lg' => 12])
                ->schema([
                    self::getPreviewColumn($imapPreview),
                    self::getFieldsColumn($imapSource),
                ]),
        ];
    }

    public static function forAdvanceSearch(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 12])
                            ->schema([
                                TextInput::make('reference')
                                    ->label('N° / Référence')
                                    ->placeholder('N° ou référence')
                                    ->columnSpan(['default' => 1, 'md' => 2]),
                                TextInput::make('sender')
                                    ->label('Expéditeur')
                                    ->placeholder('Nom de l\'expéditeur')
                                    ->datalist(fn (): array => Sender::query()->orderBy('name')->pluck('name')->all())
                                    ->columnSpan(['default' => 1, 'md' => 5]),
                                TextInput::make('query')
                                    ->label('Recherche par texte')
                                    ->placeholder('Description, contenu…')
                                    ->columnSpan(['default' => 1, 'md' => 5]),
                            ])
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date_from')
                                    ->label('Du')
                                    ->native(false),
                                DatePicker::make('date_to')
                                    ->label('Au')
                                    ->native(false),
                                self::getCategorySelect('category'),
                            ])
                            ->columnSpanFull(),
                        self::getServicesSelect('services', 'Services'),
                        Select::make('destinataires')
                            ->label('Destinataires')
                            ->multiple()
                            ->searchable()
                            ->options(fn (): array => Recipient::query()
                                ->orderBy('last_name')
                                ->get()
                                ->pluck('full_name', 'id')
                                ->all()),
                        Select::make('department')
                            ->label('Département')
                            ->options(fn (): array => self::searchableDepartmentOptions())
                            ->visible(fn (): bool => count(self::searchableDepartmentOptions()) > 1),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    /**
     * Departments the current user may search across. Only worth offering as a
     * filter when they can see more than one; a single-department user is
     * already scoped by the policy clause.
     *
     * @return array<string, string>
     */
    private static function searchableDepartmentOptions(): array
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return [];
        }

        return collect($user->getCourrierViewableDepartments())
            ->mapWithKeys(fn (DepartmentCourrierEnum $department): array => [
                $department->value => $department->label(),
            ])
            ->all();
    }

    /**
     * Left column: document preview that stays visible (sticky) while the
     * user fills in the fields on the right.
     *
     * @param  array<string, mixed>|null  $imapPreview
     */
    private static function getPreviewColumn(?array $imapPreview): Section
    {
        if ($imapPreview !== null) {
            // IMAP flow: preview served from the IMAP server.
            $schema = [
                View::make('courrier::components.attachment-preview')
                    ->viewData([
                        'url' => $imapPreview['url'],
                        'contentType' => $imapPreview['contentType'] ?? '',
                        'filename' => $imapPreview['filename'] ?? '',
                    ]),
            ];
        } else {
            // Manual flow: file upload with existing/client-side preview.
            $schema = [
                FileUpload::make('attachment_file')
                    ->label(
                        fn (?IncomingMail $record
                        ): string => $record instanceof IncomingMail ? 'Remplacer le fichier' : 'Fichier'
                    )
                    ->required(fn (?IncomingMail $record): bool => ! $record instanceof IncomingMail)
                    ->acceptedFileTypes(config('courrier.allowed_mime_types'))
                    ->maxSize(config('courrier.max_file_size'))
                    ->storeFiles(false)
                    ->previewable(false),
                View::make('courrier::components.attachment-preview')
                    ->viewData(fn (?IncomingMail $record): array => self::getExistingAttachmentPreviewData($record))
                    ->visible(fn (?IncomingMail $record): bool => $record?->attachments->isNotEmpty() ?? false),
                View::make('courrier::components.upload-preview'),
            ];
        }

        return Section::make('Aperçu')
            ->schema($schema)
            ->columnSpan(['default' => 1, 'lg' => 7])
            ->extraAttributes(['class' => 'lg:sticky lg:top-24 lg:self-start']);
    }

    /**
     * Right column: all the mail fields, stacked so they read top-to-bottom
     * alongside the preview.
     *
     * @param  array{uid: int, index: int, mailbox: string}|null  $imapSource
     */
    private static function getFieldsColumn(?array $imapSource): Group
    {
        $isCpas = DepartmentScope::getAssignableDepartment() === DepartmentCourrierEnum::CPAS;

        return Group::make()
            ->columnSpan(['default' => 1, 'lg' => 5])
            ->schema([
                Section::make('Informations du courrier')
                    ->afterHeader([
                        // The action is wrapped in a keyed group rather than the
                        // section being keyed: keying the section would prefix
                        // the key of every field it holds.
                        Group::make([AnalyzeAttachmentAction::make($imapSource)])
                            ->key('ai-completion'),
                    ])
                    ->schema([
                        TextInput::make('reference_number')
                            ->label('Numéro')
                            ->required()
                            ->default(fn (): ?string => $isCpas ? (string) IncomingMail::nextCpasReferenceNumber() : null)
                            ->maxLength(255)
                            ->columnSpan(1),
                        DatePicker::make('mail_date')
                            ->label('Date du courrier')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->columnSpan(1),
                        TextInput::make('sender')
                            ->label('Expéditeur')
                            ->required()
                            ->maxLength(255)
                            ->datalist(Sender::query()->pluck('name')->toArray())
                            ->columnSpan(1),
                        Checkbox::make('save_sender')
                            ->label('Enregistrer l\'expéditeur')
                            ->inline()
                            ->columnSpan(1),
                        TextInput::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        self::getCategorySelect('category_id')
                            ->visible(self::canEncodeCategory(...))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Affectation')
                    ->schema([
                        self::getServicesSelect('primary_services', 'Services principaux'),
                        self::getServicesSelect('secondary_services', 'Services secondaires'),
                        Select::make('primary_recipients')
                            ->label('Destinataires principaux')
                            ->options(RecipientRepository::getForOptions())
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('secondary_recipients')
                            ->label('Destinataires secondaires')
                            ->options(RecipientRepository::getForOptions())
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Options')
                    ->schema([
                        Toggle::make('is_registered')
                            ->label('Recommandé ?')
                            ->default(false),
                        Toggle::make('has_acknowledgment')
                            ->label('Accusé de réception ?')
                            ->default(false),
                        Toggle::make('is_notified')
                            ->label('Notifié')
                            ->default(false),
                        DepartmentField::make(),
                    ])
                    ->columns(2),
                Section::make('Suivi')
                    ->schema([
                        Textarea::make('follow_up_note')
                            ->label('Note de suivi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (IncomingMail|array|null $record): bool => $record instanceof IncomingMail),
            ]);
    }

    /**
     * Only the CPAS indexers classify their mail, so they alone are offered the
     * category field. Hiding it also keeps it out of the saved data, so nobody
     * else can clear a category already encoded.
     */
    private static function canEncodeCategory(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasRole(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value);
    }

    /**
     * Category picker, shared by the create/edit form (where it writes the
     * `category_id` column) and the advanced search (where it is a filter).
     */
    private static function getCategorySelect(string $name): Select
    {
        return Select::make($name)
            ->label('Catégorie')
            ->preload()
            ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all());
    }

    /**
     * Service picker. Options are preloaded so the full list is browsable, but
     * typing runs a server-side search that also matches the initials, since
     * that is how services are usually named day to day.
     */
    private static function getServicesSelect(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(ServiceRepository::findAllActiveOrdered())
            ->multiple()
            ->searchable()
            ->preload()
            ->getSearchResultsUsing(
                fn (string $search): array => ServiceRepository::searchByNameOrInitials($search)->all(),
            )
            ->searchPrompt('Rechercher un service par nom ou initiales')
            ->noSearchResultsMessage('Aucun service trouvé.');
    }

    /**
     * @return array{url: string, contentType: string, filename: string}
     */
    private static function getExistingAttachmentPreviewData(?IncomingMail $record): array
    {
        $attachment = $record?->attachments->first();

        if (! $attachment) {
            return ['url' => '', 'contentType' => '', 'filename' => ''];
        }

        return [
            'url' => route('courrier.attachments.preview-stored', $attachment),
            'contentType' => $attachment->mime ?? '',
            'filename' => $attachment->file_name,
        ];
    }
}
