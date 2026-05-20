<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Exports;

use AcMarche\SportsActivities\Models\Activity;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class ActivityGroupsPdfExport
{
    public static function download(Activity $activity): StreamedResponse
    {
        $activity->load(['groups.registrations.member']);

        $filename = 'activite-'.$activity->id.'-groupes.pdf';

        $pdf = pdf()
            ->view('sports-activities::pdf.activity-groups', [
                'activity' => $activity,
            ])
            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            })
            ->name($filename)
            ->download();

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf->toResponse(request())->getContent();
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
