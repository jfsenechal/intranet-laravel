<?php

declare(strict_types=1);

namespace AcMarche\Ad\Http\Controllers;

use AcMarche\Ad\Services\SubscriptionException;
use AcMarche\Ad\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class SubscriptionController extends Controller
{
    public function show(): View
    {
        return view('ad::public.subscription');
    }

    public function subscribe(Request $request, SubscriptionService $service): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $subscriber = $service->subscribe($data['email']);
        } catch (SubscriptionException $subscriptionException) {
            return back()
                ->withInput()
                ->with('error', $subscriptionException->getMessage());
        }

        return back()->with('success', sprintf(
            'Merci %s %s, vous êtes abonné aux nouvelles annonces.',
            $subscriber->first_name,
            $subscriber->last_name,
        ));
    }

    public function unsubscribe(Request $request, SubscriptionService $service): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        if (! $service->unsubscribe($data['email'])) {
            return back()
                ->withInput()
                ->with('error', "Cette adresse n'est pas abonnée.");
        }

        return back()->with('success', 'Désabonnement effectué.');
    }
}
