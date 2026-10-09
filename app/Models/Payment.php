<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Paiement Mobile Money SIMULÉ avec séquestre : les fonds sont « sécurisés » à l'attribution,
 * libérés au dev à la clôture, remboursés en cas d'annulation. Ne passe que par PaymentService.
 */
class Payment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'phone',
        'amount_fcfa',
        'status',
        'reference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_fcfa' => 'integer',
            'status' => PaymentStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Mission, $this>
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function providerLabel(): string
    {
        return config('payments.providers')[$this->provider] ?? $this->provider;
    }

    /** « 77 ••• •• 45 » : seuls les 2 premiers et les 2 derniers chiffres restent visibles. */
    public function maskedPhone(): string
    {
        return self::mask($this->phone);
    }

    public static function mask(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return strlen($digits) < 6 ? '•••' : substr($digits, 0, 2).' ••• •• '.substr($digits, -2);
    }

    /** Référence simulée « DM-XXXXXX », unique. */
    public static function newReference(): string
    {
        do {
            $reference = 'DM-'.Str::upper(Str::random(6));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
