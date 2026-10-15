<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Filament\Pages;

use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\KnowledgeBaseAccess;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Override;

/**
 * Edits documentation.md, the sidebar index (laravel/docs format).
 *
 * @property-read Schema $form
 */
final class EditNavigation extends Page
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Override]
    protected static ?string $slug = 'navigation';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected string $view = 'knowledge-base::filament.pages.document-form';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canWrite(auth()->user());
    }

    public function mount(Documentation $documentation): void
    {
        $this->form->fill(['source' => $documentation->navigationSource()]);
    }

    public function getTitle(): string
    {
        return 'Modifier le sommaire';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Textarea::make('source')
                    ->label('documentation.md')
                    ->helperText('Format : "- ## Section" puis, en dessous, "    - [Titre](slug)"')
                    ->rows(25)
                    ->maxLength(100000)
                    ->extraInputAttributes(['class' => 'font-mono']),
                TextEntry::make('unlisted')
                    ->label('Pages absentes du sommaire')
                    ->state(fn (): array => app(Documentation::class)->unlistedSlugs())
                    ->badge()
                    ->placeholder('Toutes les pages sont listées'),
            ])
            ->statePath('data');
    }

    public function save(Documentation $documentation): void
    {
        abort_unless(KnowledgeBaseAccess::canWrite(auth()->user()), 403);

        $state = $this->form->getState();

        $documentation->putNavigation((string) ($state['source'] ?? ''));

        Notification::make()
            ->title('Sommaire enregistré')
            ->success()
            ->send();

        $this->redirect(ListDocuments::getUrl());
    }

    public function getCancelUrl(): string
    {
        return ListDocuments::getUrl();
    }
}
