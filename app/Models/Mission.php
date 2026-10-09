<?php

namespace App\Models;

use App\Enums\MissionStatus;
use App\Support\Countries;
use Database\Factories\MissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    /** @use HasFactory<MissionFactory> */
    use HasFactory;

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
