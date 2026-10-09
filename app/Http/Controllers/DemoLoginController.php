<?php

namespace App\Http\Controllers;

use App\Enums\MissionStatus;
use App\Models\User;
use App\Support\Demo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Connexion démo en un clic pour le jury (APP_DEMO=true seulement).
 */
class DemoLoginController extends Controller
{
    public function __invoke(Request $request, string $persona): RedirectResponse
    {
        abort_unless(Demo::enabled() && isset(Demo::PERSONAS[$persona]), 404);

        $user = User::query()->where('email', Demo::PERSONAS[$persona]['email'])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        // Chacun arrive là où la démo commence : Aïssatou sur sa mission ouverte (elle compare
        // les candidats), Moussa sur les missions qui correspondent à ses compétences.
        $mission = $user->missions()->where('status', MissionStatus::Open)->has('applications')->latest()->first();

        // Pas de message flash : le bandeau du mode démo indique déjà qui est connecté.
        return redirect($mission ? route('missions.show', $mission) : route('missions.index', ['pour_moi' => 1]));
    }
}
