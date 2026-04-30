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
            'logoUrl' => $logo ? asset('vendor/app/images/logos/'.$logo->value) : null,
            'logoTitle' => $logo?->getTitle() ?? $signature->logo_title,
        ])->render();
    }
}
