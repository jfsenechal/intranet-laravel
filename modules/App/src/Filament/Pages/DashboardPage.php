<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\App\Handler\FavoriteModuleHandler;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Search\MeiliSearcher;
use AcMarche\Document\Models\Document;
use AcMarche\Hrm\Models\Employee;
use AcMarche\News\Models\News;
use AcMarche\Security\Models\Module;
use AcMarche\WhoIsWho\Repository\FavoriteEmployeeRepository;
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

    /**
     * @var Collection<int, IncomingMail>
     */
    public Collection $myCourriers;

    /**
     * @var Collection<int, Employee>
     */
    public Collection $favoriteEmployees;

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
        $this->favoriteModules = FavoriteModuleHandler::getFavoriteModules();

        $this->favoriteEmployees = FavoriteEmployeeRepository::favorites();

        $this->latestNews = News::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $this->latestDocuments = Document::query()
            ->with('category')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $this->myCourriers = $this->recentMails();
    }

    /**
     * The mail of the last 15 days the current user receives, resolved through
     * the search index because the equivalent SQL query is too slow.
     *
     * @return Collection<int, IncomingMail>
     */
    private function recentMails(): Collection
    {
        $user = Auth::user();
        if ($user === null) {
            return new Collection();
        }

        $mailIds = app(MeiliSearcher::class)->myMailIds($user, now()->subDays(15), 10);
        if ($mailIds === []) {
            return new Collection();
        }

        return IncomingMail::query()
            ->whereIn('id', $mailIds)
            ->latest('mail_date')
            ->get();
    }
}
