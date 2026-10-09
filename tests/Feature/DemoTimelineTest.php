<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\DB;

/*
| Cohérence temporelle du jeu de démonstration (le jury le remarquerait) :
| rien avant le lancement (29 sept.), rien dans le futur, et chaque chose après ce dont elle dépend.
*/

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function countWhere(string $sql): int
{
    return (int) DB::selectOne("select count(*) as n from ($sql) t")->n;
}

test('rien n\'est daté d\'avant le lancement du 29 septembre, ni dans le futur', function () {
    $launch = config('devmarche.launched_at');

    foreach (['users', 'profiles', 'missions', 'applications', 'questions', 'answers', 'votes', 'reputation_events'] as $table) {
        expect(DB::table($table)->where('created_at', '<', $launch)->count())->toBe(0, "$table avant le lancement")
            ->and(DB::table($table)->where('created_at', '>', now())->count())->toBe(0, "$table dans le futur");
    }

    expect(DB::table('missions')->where('completed_at', '>', now())->count())->toBe(0)
        ->and(DB::table('users')->where('last_seen_at', '>', now())->count())->toBe(0);
});

test('chaque contenu est postérieur à l\'inscription de son auteur', function () {
    expect(countWhere('select m.id from missions m join users u on u.id = m.client_id where m.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select a.id from applications a join users u on u.id = a.user_id where a.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select q.id from questions q join users u on u.id = q.user_id where q.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select a.id from answers a join users u on u.id = a.user_id where a.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select v.id from votes v join users u on u.id = v.user_id where v.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select e.id from reputation_events e join users u on u.id = e.user_id where e.created_at < u.created_at'))->toBe(0)
        ->and(countWhere('select u.id from users u where u.last_seen_at < u.created_at'))->toBe(0);
});

test('l\'ordre des étapes est respecté : candidature, attribution, fin ; question, réponse, vote', function () {
    expect(countWhere('select a.id from applications a join missions m on m.id = a.mission_id where a.created_at < m.created_at'))->toBe(0)
        ->and(countWhere("select a.id from applications a join missions m on m.id = a.mission_id where a.status = 'accepted' and m.assigned_at < a.created_at"))->toBe(0)
        ->and(countWhere('select id from missions where completed_at < assigned_at'))->toBe(0)
        ->and(countWhere('select a.id from answers a join questions q on q.id = a.question_id where a.created_at < q.created_at'))->toBe(0)
        ->and(countWhere("select v.id from votes v join answers a on v.votable_type = 'answer' and a.id = v.votable_id where v.created_at < a.created_at"))->toBe(0)
        ->and(countWhere("select v.id from votes v join questions q on v.votable_type = 'question' and q.id = v.votable_id where v.created_at < q.created_at"))->toBe(0);
});

test('les membres actifs ne sont pas « Nouveau », les nouveaux venus sans activité le sont', function () {
    $moussa = User::where('email', DevSeeder::DEMO_DEV_EMAIL)->sole();
    expect($moussa->isNewMember())->toBeFalse()
        ->and($moussa->level()?->label())->toBe('Confirmé');

    foreach (DevSeeder::NEWCOMERS as $name) {
        $user = User::where('name', $name)->sole();
        if ($user->reputation < 100) {
            expect($user->isNewMember())->toBeTrue("$name devrait être Nouveau");
        }
    }
});
