<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class AvatarService
{
    public const SIZE = 256;

    public function __construct(private readonly ImageManager $images) {}

    /**
     * Recadre l'image en carré de 256 px, l'encode en WebP et renvoie son chemin sur le disque public.
     */
    public function store(UploadedFile $file): string
    {
        $encoded = $this->images
            ->read($file->getRealPath())
            ->cover(self::SIZE, self::SIZE)
            ->toWebp(quality: 80);

        $path = 'avatars/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
