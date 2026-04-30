<?php

declare(strict_types=1);

namespace AcMarche\Pst\Http\Controllers;

use AcMarche\Pst\Filament\Exports\PdfExport;
use AcMarche\Pst\Models\Action;
use App\Http\Controllers\Controller;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class PdfExportController extends Controller
{
    // todo try
    public function exprotTiti(Action $action): PdfBuilder
    {
        return pdf()
            ->view('pdf.action', ['action' => $action])
            ->name('action.pdf');
    }

    public function export(Action $action): RedirectResponse
    {
        $relativePath = PdfExport::exportAction($action);

        $downloadUrl = route('pdf.download', ['path' => $relativePath]);

        $recipient = auth()->user();

        Notification::make()
            ->title('Saved successfully')
            ->info()
            ->sendToDatabase($recipient)->actions([
                FilamentAction::make('download')
                    ->button()
                    ->url($downloadUrl)
                    ->markAsRead(),
            ])
            ->send();

        return back()->with('success', 'PDF exported successfully');
    }

    public function download(string $path): StreamedResponse
    {
        return Storage::disk('public')->download($path, basename($path), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
