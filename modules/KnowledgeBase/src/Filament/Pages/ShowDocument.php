<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Filament\Pages;

use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\KnowledgeBaseAccess;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;

final class ShowDocument extends Page
{
    public string $document = '';

    #[Override]
    protected static ?string $slug = 'docs/{document}';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected string $view = 'knowledge-base::filament.pages.show-document';

    public static function canAccess(): bool
    {
        return KnowledgeBaseAccess::canRead(auth()->user());
    }

    public function mount(string $document, Documentation $documentation): void
    {
        abort_unless($documentation->exists($document), 404);

        $this->document = $document;
    }

    public function getTitle(): string
    {
        return app(Documentation::class)->title($this->document);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        $canWrite = KnowledgeBaseAccess::canWrite(auth()->user());

        return [
            Action::make('edit')
                ->label('Modifier')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->visible($canWrite)
                ->url(fn (): string => EditDocument::getUrl(['document' => $this->document])),
            Action::make('delete')
                ->label('Supprimer')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible($canWrite)
                ->requiresConfirmation()
                ->modalDescription('La page sera définitivement supprimée. Les images restent sur le disque.')
                ->action(function (Documentation $documentation): void {
                    abort_unless(KnowledgeBaseAccess::canWrite(auth()->user()), 403);

                    $documentation->delete($this->document);

                    Notification::make()
                        ->title('Page supprimée')
                        ->success()
                        ->send();

                    $this->redirect(ListDocuments::getUrl());
                }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        $documentation = app(Documentation::class);

        return [
            'html' => $documentation->render($this->document),
            'navigation' => $documentation->navigation(),
            'updatedAt' => $documentation->lastModified($this->document),
            'vaultUrl' => config('knowledge-base.vault_url'),
        ];
    }
}
