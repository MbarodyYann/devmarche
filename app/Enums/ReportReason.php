<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Offensive = 'offensive';
    case OffTopic = 'off_topic';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam ou publicité',
            self::Offensive => 'Propos offensants',
            self::OffTopic => 'Hors sujet',
            self::Other => 'Autre',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $r) => [$r->value => $r->label()])->all();
    }
}
