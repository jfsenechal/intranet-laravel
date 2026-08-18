<?php

declare(strict_types=1);

namespace AcMarche\App\Services;

use AcMarche\App\Models\Signature;
use Illuminate\Support\Facades\View;

final class SignatureHtmlGenerator
{
    public static function generate(Signature $signature): string
    {
        $logo = $signature->logo;

        return View::make('app::emails.signature', [
            'signature' => $signature,
            'logoUrl' => $logo ? self::logoUrl($logo->value) : null,
            'logoTitle' => $logo?->getTitle() ?? $signature->logo_title,
        ])->render();
    }

    /**
     * Logos are hosted publicly so they stay reachable from mail clients.
     */
    private static function logoUrl(string $fileName): string
    {
        $baseUrl = (string) config('app.signature.logo_base_url');

        return mb_rtrim($baseUrl, '/').'/'.$fileName;
    }
}
