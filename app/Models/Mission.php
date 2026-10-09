<?php

namespace App\Models;

use App\Enums\MissionStatus;
use App\Models\Concerns\RendersMarkdown;
use App\Support\Countries;
use Database\Factories\MissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mission extends Model
{
    /** @use HasFactory<MissionFactory> */
    use HasFactory, RendersMarkdown;

    /** Budget minimal accepté, en FCFA. */
    public const MIN_BUDGET = 5000;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'budget_fcfa',
        'country',
        'deadline',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MissionStatus::class,
            'budget_fcfa' => 'integer',
            'deadline' => 'date',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function markdownFields(): array
    {
        return ['description' => 'description_html'];
    }

    /**
     * Paiement Mobile Money simulé (séquestre).
     *
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Avis du client sur le freelance (une fois la mission terminée).
     *
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Recherche plein texte en français sur le titre et la description (index GIN, voir la migration).
     *
     * @param  Builder<Mission>  $query
     */
    public function scopeSearch(Builder $query, string $terms): void
    {
        $query->whereRaw("to_tsvector('french', title || ' ' || description) @@ websearch_to_tsquery('french', ?)", [$terms]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->orderBy('name');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @param  Builder<Mission>  $query
     */
    public function scopeStatus(Builder $query, MissionStatus $status): void
    {
        $query->where('status', $status);
    }

    public function isOpen(): bool
    {
        return $this->status === MissionStatus::Open;
    }

    public function isRemote(): bool
    {
        return $this->country === null;
    }

    public function locationLabel(): string
    {
        return $this->isRemote() ? 'Télétravail' : (string) Countries::name($this->country);
    }

    public function flag(): string
    {
        return Countries::flag($this->country);
    }
}
