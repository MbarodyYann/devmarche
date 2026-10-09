<?php

namespace App\Http\Controllers;

use App\Services\PulseService;
use Illuminate\View\View;

class PulseController extends Controller
{
    public function __invoke(PulseService $pulse): View
    {
        return view('pulse', ['pulse' => $pulse->snapshot()]);
    }
}
