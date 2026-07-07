<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    /**
     * Map of legacy FontAwesome icon classes to Filament-compatible Heroicon names.
     *
     * @var array<string, string>
     */
    private array $iconMap = [
        'fa-solid fa-universal-access' => 'heroicon-o-users',
        'fa-solid fa-desktop' => 'heroicon-o-computer-desktop',
        'fa-regular fa-handshake' => 'heroicon-o-hand-raised',
        'fa-solid fa-hand-holding-heart' => 'heroicon-o-heart',
        'fa-solid fa-exchange-alt' => 'heroicon-o-arrows-right-left',
        'fa-solid fa-globe-africa' => 'heroicon-o-globe-europe-africa',
        'fa-solid fa-cloud-sun' => 'heroicon-o-bolt',
        'fa-solid fa-euro-sign' => 'heroicon-o-currency-euro',
        'fa-solid fa-house-user' => 'heroicon-o-home-modern',
        'fa-solid fa-home' => 'heroicon-o-home',
        'fa-brands fa-slideshare' => 'heroicon-o-users',
        'fa-solid fa-blind' => 'heroicon-o-user',
        'fa-solid fa-ambulance' => 'heroicon-o-plus-circle',
        'fa-brands fa-accessible-icon' => 'heroicon-o-user',
        'fa-solid fa-expand-arrows-alt' => 'heroicon-o-arrows-pointing-out',
        'fa-solid fa-globe' => 'heroicon-o-globe-alt',
        'fa-solid fa-people-arrows' => 'heroicon-o-user-group',
        'fa-solid fa-file-contract' => 'heroicon-o-document-text',
        'fa-solid fa-comments-dollar' => 'heroicon-o-banknotes',
        'fa-solid fa-theater-masks' => 'heroicon-o-ticket',
        'fa-solid fa-peace' => 'heroicon-o-sparkles',
        'fa-regular fa-list-alt' => 'heroicon-o-list-bullet',
        'fa-regular fa-address-book' => 'heroicon-o-book-open',
        'fa-regular fa-file-archive' => 'heroicon-o-archive-box',
        'fa-solid fa-beer' => 'heroicon-o-calendar-days',
        'fa-regular fa-bookmark' => 'heroicon-o-bookmark',
        'fa-solid fa-fire-extinguisher' => 'heroicon-o-fire',
        'fa-brands fa-shopify' => 'heroicon-o-shopping-cart',
        'fa-solid fa-clipboard-list' => 'heroicon-o-clipboard-document-list',
        'fa-solid fa-balance-scale' => 'heroicon-o-scale',
    ];

    public function up(): void
    {
        foreach ($this->iconMap as $fontAwesome => $heroicon) {
            DB::connection('maria-cpas-library')
                ->table('categories')
                ->where('icon', $fontAwesome)
                ->update(['icon' => $heroicon]);
        }

        // Null out any remaining legacy FontAwesome values so Filament never
        // tries to resolve an unknown SVG and throws SvgNotFound.
        DB::connection('maria-cpas-library')
            ->table('categories')
            ->where('icon', 'like', 'fa-%')
            ->update(['icon' => null]);
    }

    public function down(): void
    {
        foreach ($this->iconMap as $fontAwesome => $heroicon) {
            DB::connection('maria-cpas-library')
                ->table('categories')
                ->where('icon', $heroicon)
                ->update(['icon' => $fontAwesome]);
        }
    }
};
