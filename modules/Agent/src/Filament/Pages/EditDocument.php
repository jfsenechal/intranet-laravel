<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Pages;

use AcMarche\Agent\Documentation\Documentation;
use AcMarche\Agent\Filament\Schemas\DocumentForm;
use AcMarche\Agent\KnowledgeBaseAccess;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Override;

/**
 * @property-read Schema $form
 */
final class EditDocument extends Page
{
    use RestrictsFileUploadsToSchemaComponents;

    public string $document = '';

    /**
     * Content hash at mount time, compared on save so an editor never
     * silently overwrites a change made by someone else in the meantime.
     */
    public string $originalHash = '';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Override]
    protected static ?string $slug = 'docs/{document}/edit';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected string $view = 'knowledge-base::filament.pages.document-form';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canWrite(auth()->user());
    }

    public function mount(string $document, Documentation $documentation): void
    {
        abort_unless($documentation->exists($document), 404);

        $this->document = $document;
        $this->originalHash = $documentation->hash($document);

        $this->form->fill(['content' => $documentation->get($document)]);
    }

    public function getTitle(): string
    {
        return 'Modifier : '.app(Documentation::class)->title($this->document);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                DocumentForm::contentField(),
            ])
            ->statePath('data');
    }

    public function save(Documentation $documentation): void
    {
        abort_unless(KnowledgeBaseAccess::canWrite(auth()->user()), 403);

        $state = $this->form->getState();

        if (! $documentation->exists($this->document) || $documentation->hash($this->document) !== $this->originalHash) {
            Notification::make()
                ->title('Modification concurrente')
                ->body("La page a été modifiée ou supprimée par quelqu'un d'autre depuis l'ouverture. Rechargez avant d'enregistrer.")
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $documentation->put($this->document, $state['content']);

        DocumentForm::warnAboutSecrets($state['content']);

        Notification::make()
            ->title('Page enregistrée')
            ->success()
            ->send();

        $this->redirect(ShowDocument::getUrl(['document' => $this->document]));
    }

    public function getCancelUrl(): string
    {
        return ShowDocument::getUrl(['document' => $this->document]);
    }
}
