<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Mission;
use App\Services\ReviewService;
use App\Support\French;
use Illuminate\Http\RedirectResponse;

class MissionReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Mission $mission, ReviewService $reviews): RedirectResponse
    {
        $reviews->submit($mission, $request->user(), $request->validated());

        return redirect()->route('missions.show', $mission)
            ->with('status', 'Avis publié. Il apparaît sur le profil '.French::de($mission->freelancer()->value('name')).'.');
    }
}
