<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Override;
use UnitEnum;

/**
 * A screen recording of the AI-assisted encoding, from the scan arriving in the
 * mailbox to the courrier proposed for verification.
 *
 * The feature is easier to show than to describe, and the mail room is not the
 * audience that reads {@see \AcMarche\Courrier\Filament\Actions\AnalyzeAttachmentAction},
 * so the demonstration lives in the panel next to the pages it demonstrates.
 */
final class AiDemoVideo extends Page
{
    /**
     * Served straight from `public/`, not from the storage disk: the recording
     * ships with the module and never changes at runtime.
     */
    public const string VIDEO_PATH = 'videos/demo-ia-indicateur.mp4';

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-player-play';

    #[Override]
    protected static ?int $navigationSort = 6;

    #[Override]
    protected static ?string $navigationLabel = 'Démonstration IA';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.ai-demo-video';

    public function getTitle(): string
    {
        return 'Démonstration de l\'encodage assisté par IA';
    }

    public function getSubheading(): string
    {
        return 'Du scan reçu dans la boite mail au courrier proposé à la vérification.';
    }

    public function getVideoUrl(): string
    {
        return asset(self::VIDEO_PATH);
    }
}
