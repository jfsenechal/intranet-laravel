<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\Security\Repository\LdapRepository;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;

final class EmailsListPage extends Page
{
    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::AtSymbol;

    #[Override]
    protected static ?string $navigationLabel = 'Mails des services';

    #[Override]
    protected string $view = 'app::filament.pages.emails-list';

    public function getTitle(): string
    {
        return 'Adresses mails des services';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        $data = [];
        foreach (LdapRepository::services() as $service) {
            $data[] = [
                'mail' => $service->getFirstAttribute('mail'),
                'proxyaddresses' => [],
                'description' => $service->getFirstAttribute('description'),
            ];

        }
        foreach (LdapRepository::lists() as $list) {
            if (! $list->getAttribute('proxyaddresses')) {
                continue;
            }
            $data[] = [
                'mail' => $list->getFirstAttribute('mail'),
                'proxyaddresses' => $list->getAttribute('proxyaddresses'),
                'description' => $list->getFirstAttribute('description'),
            ];
        }

        usort($data, fn (array $a, array $b): int => strcasecmp((string) $a['mail'], (string) $b['mail']));

        return [
            'data' => $data,
        ];
    }
}
