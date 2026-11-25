<?php

declare(strict_types=1);

namespace AcMarche\Ad\Http\Controllers;

use AcMarche\Ad\Ai\ClassifiedAdModerator;
use AcMarche\Ad\Events\ClassifiedAdProcessed;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Models\Category;
use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Ad\Services\SubscriptionService;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Lets people without an intranet account post an ad, provided the email they
 * give belongs to an eligible employee in the HR database (an active "Agent"
 * under contract). Authenticated users are sent to the panel form.
 */
final class PublicClassifiedAdController extends Controller
{
    public const string SESSION_KEY = 'ad.guest_email';

    private const string DISK = 'public';

    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function show(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(ClassifiedAdResource::getUrl('create', panel: 'ad-panel'));
        }

        return view('ad::public.classified-ad-create', [
            'employee' => $this->identifiedEmployee($request),
            'email' => $request->session()->get(self::SESSION_KEY),
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function identify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $employee = $this->subscriptionService->findEligibleEmployee($data['email']);

        if (! $employee instanceof Employee) {
            return back()
                ->withInput()
                ->with('error', "Cet email n'est pas reconnu. Merci de contacter le service RH.");
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, mb_strtolower(mb_trim($data['email'])));

        return to_route('ad.public.create');
    }

    public function forget(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return to_route('ad.public.create');
    }

    public function store(Request $request, ClassifiedAdModerator $moderator): RedirectResponse
    {
        if (! $this->identifiedEmployee($request) instanceof Employee) {
            return to_route('ad.public.create')
                ->with('error', 'Merci de vous identifier avec votre email avant de déposer une annonce.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:'.Category::class.',id'],
            'end_date' => ['required', 'date', 'after_or_equal:today'],
            'medias' => ['nullable', 'array', 'max:5'],
            'medias.*' => ['file', 'max:10240'],
        ]);

        $mediaPaths = array_map(
            fn (UploadedFile $file): string => (string) $file->store(config('ad.uploads.medias'), self::DISK),
            $request->file('medias', []),
        );
        $htmlContent = '<p>'.nl2br(e($data['content'])).'</p>';

        $verdict = $moderator->moderate($data['name'], $htmlContent, $mediaPaths);

        if (! $verdict->isCompliant) {
            Storage::disk(self::DISK)->delete($mediaPaths);

            return back()
                ->withInput()
                ->with('error', 'Annonce refusée : elle ne respecte pas les règles de bonne conduite.')
                ->with('moderation_reasons', $verdict->reasons);
        }

        $classifiedAd = ClassifiedAd::query()->create([
            'name' => $data['name'],
            'content' => $htmlContent,
            'category_id' => $data['category_id'],
            'end_date' => $data['end_date'],
            'medias' => $mediaPaths,
            'user_add' => $request->session()->get(self::SESSION_KEY),
        ]);

        event(new ClassifiedAdProcessed($classifiedAd));

        return to_route('ad.public.create')->with('success', 'Merci, votre annonce a été publiée.');
    }

    /**
     * The employee identified in this session, as long as they are still eligible.
     */
    private function identifiedEmployee(Request $request): ?Employee
    {
        $email = $request->session()->get(self::SESSION_KEY);

        if (! is_string($email)) {
            return null;
        }

        $employee = $this->subscriptionService->findEligibleEmployee($email);

        if (! $employee instanceof Employee) {
            $request->session()->forget(self::SESSION_KEY);
        }

        return $employee;
    }
}
