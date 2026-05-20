<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Exports;

use AcMarche\SportsActivities\Models\Group;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class GroupRegistrationsPdfExport
{
    public static function download(Group $group): StreamedResponse
    {
        $group->load(['activity', 'registrations.member']);

        $filename = 'groupe-'.$group->id.'-inscriptions.pdf';

        $pdf = pdf()
            ->view('sports-activities::pdf.group-registrations', [
                'group' => $group,
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
