<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Filament\Pages;

use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\Filament\Schemas\DocumentForm;
use AcMarche\KnowledgeBase\KnowledgeBaseAccess;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Override;

/**
 * @property-read Schema $form
 */
final class CreateDocument extends Page
{
    use RestrictsFileUploadsToSchemaComponents;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Override]
    protected static ?string $slug = 'create';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected string $view = 'knowledge-base::filament.pages.document-form';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canWrite(auth()->user());
    }

    /**
     * New pages start from resources/stubs/new-page.md, a laravel/docs style
     * page (title, link list menu, named anchors) that editors adapt.
     */
    public function mount(): void
    {
        $this->form->fill(['content' => file_get_contents(dirname(__DIR__, 3).'/resources/stubs/new-page.md')]);
    }

    public function getTitle(): string
    {
        return 'Nouvelle page';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('slug')
                    ->label('Identifiant (URL)')
                    ->helperText('Minuscules, chiffres et tirets — ex. vpn-forticlient')
                    ->required()
                    ->maxLength(Documentation::SLUG_MAX_LENGTH)
                    ->regex(Documentation::SLUG_PATTERN)
                    ->notIn(Documentation::RESERVED_SLUGS)
                    ->validationMessages([
                        'regex' => 'Utilisez uniquement des minuscules, des chiffres et des tirets simples.',
                        'not_in' => 'Cet identifiant est réservé.',
                    ])
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if (is_string($value) && app(Documentation::class)->exists($value)) {
                                $fail('Une page avec cet identifiant existe déjà.');
                            }
                        },
                    ]),
                DocumentForm::contentField(),
            ])
            ->statePath('data');
    }

    public function save(Documentation $documentation): void
    {
        abort_unless(KnowledgeBaseAccess::canWrite(auth()->user()), 403);

        $state = $this->form->getState();

        $documentation->put($state['slug'], $state['content']);

        DocumentForm::warnAboutSecrets($state['content']);

        Notification::make()
            ->title('Page créée')
            ->success()
            ->send();

        $this->redirect(ShowDocument::getUrl(['document' => $state['slug']]));
    }

    public function getCancelUrl(): string
    {
        return ListDocuments::getUrl();
    }
}
