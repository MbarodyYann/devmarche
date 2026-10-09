<?php

namespace App\Http\Controllers;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * /sitemap.xml dynamique : pages publiques, missions ouvertes, questions et profils.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('missions.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('questions.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('devs.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ]);

        Mission::query()->where('status', MissionStatus::Open)->latest()->get(['id', 'updated_at'])
            ->each(fn (Mission $m) => $urls->push(['loc' => route('missions.show', $m), 'lastmod' => $m->updated_at, 'priority' => '0.8']));

        Question::query()->latest()->get(['id', 'updated_at'])
            ->each(fn (Question $q) => $urls->push(['loc' => route('questions.show', $q), 'lastmod' => $q->updated_at, 'priority' => '0.7']));

        User::query()->whereHas('profile')->orderBy('id')->get(['id', 'updated_at'])
            ->each(fn (User $u) => $urls->push(['loc' => route('devs.show', $u), 'lastmod' => $u->updated_at, 'priority' => '0.6']));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** robots.txt : tout est indexable sauf les formulaires privés et la connexion démo. */
    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Disallow: /demo/',
            'Disallow: /missions/publier',
            'Disallow: /questions/poser',
            'Disallow: /devs/*/modifier',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]))->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
