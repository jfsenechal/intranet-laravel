<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Pages;

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Document\Models\Document;
use AcMarche\News\Models\News;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Collection;
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

    public Collection $ownedCourriers;

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

        $this->latestNews = News::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $this->latestDocuments = Document::query()
            ->latest('created_at')
            ->limit(5)
            ->get();

        $this->ownedCourriers = IncomingMail::query()
            ->where('user_add', $username)
            ->latest('created_at')
            ->limit(5)
            ->get();
    }
}
