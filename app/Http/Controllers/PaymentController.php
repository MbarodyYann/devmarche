<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Mission;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Retenir ce dev » en deux étapes, en SIMULATION : 1) opérateur et numéro, 2) code de confirmation
 * à 4 chiffres affiché à l'écran (aucun SMS, aucun service externe, aucun argent réel).
 */
class PaymentController extends Controller
{
    public function create(Mission $mission, Application $application): View
    {
        Gate::authorize('accept', [$mission, $application]);
        $application->load('user.profile');

        return view('payments.create', [
            'mission' => $mission,
            'application' => $application,
            'providers' => config('payments.providers'),
            'pending' => session($this->key($application)),
        ]);
    }

    public function store(Request $request, Mission $mission, Application $application): RedirectResponse
    {
        Gate::authorize('accept', [$mission, $application]);

        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(config('payments.providers')))],
            'phone' => ['required', 'string', 'regex:/^[0-9 +.\-]{8,20}$/'],
        ], [
            'provider.required' => 'Choisissez votre opérateur Mobile Money.',
            'provider.in' => 'Choisissez un opérateur de la liste.',
            'phone.required' => 'Indiquez votre numéro Mobile Money.',
            'phone.regex' => 'Le numéro doit contenir au moins 8 chiffres, par exemple 77 123 45 67.',
        ]);

        session([$this->key($application) => $data + ['code' => (string) random_int(1000, 9999)]]);

        return redirect()->route('payments.confirm', [$mission, $application]);
    }

    public function confirm(Mission $mission, Application $application): View|RedirectResponse
    {
        Gate::authorize('accept', [$mission, $application]);

        if (! $pending = session($this->key($application))) {
            return redirect()->route('payments.create', [$mission, $application]);
        }

        $application->load('user');

        return view('payments.confirm', [
            'mission' => $mission,
            'application' => $application,
            'pending' => $pending,
            'providerLabel' => config('payments.providers')[$pending['provider']],
            'maskedPhone' => Payment::mask($pending['phone']),
        ]);
    }

    public function verify(Request $request, Mission $mission, Application $application, PaymentService $payments): RedirectResponse
    {
        Gate::authorize('accept', [$mission, $application]);

        if (! $pending = session($this->key($application))) {
            return redirect()->route('payments.create', [$mission, $application]);
        }

        $request->validate(['code' => ['required', 'digits:4']], [
            'code.required' => 'Saisissez le code de confirmation à 4 chiffres.',
            'code.digits' => 'Le code de confirmation contient 4 chiffres.',
        ]);

        if ($request->input('code') !== $pending['code']) {
            return back()->withErrors(['code' => 'Ce code ne correspond pas. Recopiez le code affiché.']);
        }

        $payments->secure($mission, $application, $pending['provider'], $pending['phone']);
        session()->forget($this->key($application));

        return redirect()->route('missions.show', $mission)
            ->with('status', 'Fonds sécurisés. '.$application->user()->value('name').' est prévenu : la mission peut commencer.');
    }

    private function key(Application $application): string
    {
        return 'payments.pending.'.$application->id;
    }
}
