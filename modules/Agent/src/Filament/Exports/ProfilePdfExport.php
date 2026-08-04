<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Exports;

use AcMarche\Agent\Models\Folder;
use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Models\Module;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

/**
 * Builds the two PDF documents of a profile: the "résumé de la fiche" handed
 * over on demand and the welcome letter attached to the welcome mail.
 */
final class ProfilePdfExport
{
    public static function downloadResume(Profile $profile): StreamedResponse
    {
        $filename = self::filename($profile);

        $pdf = self::builder('agent::pdf.resume', self::viewData($profile))
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

    public static function welcomeAttachment(Profile $profile, string $password, ?string $notes): Attachment
    {
        return self::builder('agent::pdf.welcome', [
            ...self::viewData($profile),
            'password' => $password,
            'notes' => $notes,
        ])
            ->name(self::filename($profile))
            ->toMailAttachment();
    }

    public static function filename(Profile $profile): string
    {
        $slug = Str::slug($profile->fullName());

        return ($slug !== '' ? $slug : Str::slug((string) $profile->username)).'.pdf';
    }

    /**
     * @return array{profile: Profile, employee: Employee|null, email: string|null, modules: Collection<int, Module>, folderPaths: list<string>}
     */
    private static function viewData(Profile $profile): array
    {
        $profile->loadMissing(['hardware', 'phone', 'externalApplications', 'folders', 'user']);

        return [
            'profile' => $profile,
            'employee' => self::employee($profile),
            'email' => $profile->user?->email,
            'modules' => self::modules($profile),
            'folderPaths' => self::folderPaths($profile),
        ];
    }

    private static function employee(Profile $profile): ?Employee
    {
        if ($profile->employee_id === null) {
            return null;
        }

        return Employee::query()
            ->with(['activeContracts.direction', 'activeContracts.service'])
            ->find($profile->employee_id);
    }

    /**
     * @return Collection<int, Module>
     */
    private static function modules(Profile $profile): Collection
    {
        $ids = array_filter((array) ($profile->modules ?? []));

        if ($ids === []) {
            return collect();
        }

        return Module::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * Full breadcrumb of every folder granted to the profile, e.g. "Data / Urbanisme / Permis".
     *
     * @return list<string>
     */
    private static function folderPaths(Profile $profile): array
    {
        $all = Folder::query()->get(['id', 'parent_id', 'name'])->keyBy('id');

        $paths = $profile->folders
            ->map(function (Folder $folder) use ($all): string {
                $segments = [];
                $cursor = $all->get($folder->id) ?? $folder;

                while ($cursor instanceof Folder) {
                    array_unshift($segments, $cursor->name);
                    $cursor = $cursor->parent_id !== null ? $all->get($cursor->parent_id) : null;
                }

                return implode(' / ', $segments);
            })
            ->sort()
            ->values()
            ->all();

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function builder(string $view, array $data): PdfBuilder
    {
        return pdf()
            ->view($view, $data)
            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            });
    }
}
