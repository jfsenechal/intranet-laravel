<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\App\Handler\FavoriteModuleHandler;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Document\Models\Document;
use AcMarche\News\Models\News;
use AcMarche\Security\Models\Module;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Override;

final class DashboardPage extends BaseDashboard
{
    /**
     * @var list<array{title: string, url: string}>
     */
    public const array RSS_FEEDS = [
        ['title' => 'RTBF Info', 'url' => 'https://rss.rtbf.be/article/rss/highlight_rtbfinfo_homepage.xml'],
        ['title' => 'Le Soir', 'url' => 'https://www.lesoir.be/arc/outboundfeeds/rss/?outputType=xml'],
        ['title' => 'L\'Avenir Luxembourg', 'url' => 'https://www.lavenir.net/arc/outboundfeeds/rss/category/regions/luxembourg/?outputType=xml'],
        ['title' => 'Moniteur Belge', 'url' => 'https://www.ejustice.just.fgov.be/cgi/rss_summary.pl'],
        ['title' => 'Ville de Marche-en-Famenne', 'url' => 'https://www.marche.be/feed/'],
    ];

    public Collection $latestNews;

    public Collection $latestDocuments;

    public Collection $myCourriers;

    /**
     * @var SupportCollection<int, Module>
     */
    public SupportCollection $favoriteModules;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-home';

    #[Override]
    protected static ?string $navigationLabel = 'Accueil';

    #[Override]
    protected static ?int $navigationSort = -10;

    #[Override]
    protected string $view = 'app::filament.pages.dashboard';

    public function getTitle(): string
    {
        return 'Tableau de bord';
    }

    public function mount(): void
    {
        $username = Auth::user()?->username;

        $this->favoriteModules = FavoriteModuleHandler::getFavoriteModules();

        $this->latestNews = News::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $this->latestDocuments = Document::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $serviceIds = Recipient::query()
            ->where('recipients.username', $username)
            ->get()
            ->flatMap(fn (Recipient $recipient): SupportCollection => $recipient->services()->pluck('courrier_services.id'))
            ->unique()
            ->values();

        $this->myCourriers = IncomingMail::query()
            ->where(function ($query) use ($username, $serviceIds): void {
                $query->whereHas(
                    'recipients',
                    fn ($recipientQuery) => $recipientQuery->where('recipients.username', $username),
                );

                if ($serviceIds->isNotEmpty()) {
                    $query->orWhereHas(
                        'services',
                        fn ($serviceQuery) => $serviceQuery->whereIn('courrier_services.id', $serviceIds),
                    );
                }
            })
            ->latest('created_at')
            ->limit(15)
            ->get();
    }
}
