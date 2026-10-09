<?php

namespace App\Models;

use App\Enums\DevLevel;
use App\Enums\MissionStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'reputation' => 'integer',
            'is_admin' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return HasOne<Profile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->orderBy('name');
    }

    /**
     * Missions publiées (en tant que client).
     *
     * @return HasMany<Mission, $this>
     */
    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class, 'client_id');
    }

    /**
     * Missions confiées à ce dev (en tant que freelance).
     *
     * @return HasMany<Mission, $this>
     */
    public function assignedMissions(): HasMany
    {
        return $this->hasMany(Mission::class, 'freelancer_id');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return HasMany<Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return HasMany<ReputationEvent, $this>
     */
    public function reputationEvents(): HasMany
    {
        return $this->hasMany(ReputationEvent::class);
    }

    /**
     * Nombre de missions terminées en tant que freelance. Utilise la valeur précalculée par
     * scopeWithLevel() quand elle est là (listes), sinon une requête de comptage.
     */
    public function completedMissionsCount(): int
    {
        return (int) ($this->attributes['completed_missions_count']
            ?? $this->assignedMissions()->where('status', MissionStatus::Completed)->count());
    }

    /**
     * Précharge ce qu'il faut pour calculer le niveau sans requête par membre.
     *
     * @param  Builder<User>  $query
     */
    public function scopeWithLevel(Builder $query): void
    {
        $query->withCount([
            'assignedMissions as completed_missions_count' => fn (Builder $q) => $q->where('status', MissionStatus::Completed),
            'answers as dev_answers_count',
            'applications as dev_applications_count',
        ]);
    }

    /** Activité de dev : au moins une réponse ou une candidature (un client pur n'en a pas). */
    public function hasDevActivity(): bool
    {
        if (array_key_exists('dev_answers_count', $this->attributes)) {
            return $this->attributes['dev_answers_count'] + $this->attributes['dev_applications_count'] > 0;
        }

        return $this->answers()->exists() || $this->applications()->exists();
    }

    /** Niveau affiché : seulement pour un membre qui a une activité de dev. */
    public function level(): ?DevLevel
    {
        return $this->hasDevActivity() ? DevLevel::for($this) : null;
    }

    /** « Nouveau » (badge « soyez sympa » des Q&R) : moins de 14 jours et pas encore Confirmé (voir DevLevel). */
    public function isNewMember(): bool
    {
        return DevLevel::for($this) === DevLevel::Newcomer;
    }

    /**
     * Dernière activité lisible : « Actif aujourd'hui », « Actif hier », « Actif il y a 3 j ».
     * Rien au-delà de 30 jours, ni si l'on ne sait pas.
     */
    public function activityLabel(): ?string
    {
        if ($this->last_seen_at === null) {
            return null;
        }

        $days = (int) $this->last_seen_at->copy()->startOfDay()->diffInDays(now()->startOfDay());

        return match (true) {
            $days <= 0 => 'Actif aujourd\'hui',
            $days === 1 => 'Actif hier',
            $days <= 30 => "Actif il y a $days j",
            default => null,
        };
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
