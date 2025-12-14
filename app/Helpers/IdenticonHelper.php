<?php

declare(strict_types=1);

namespace Strava\Helpers;

use Identicon\Identicon;
use Illuminate\Support\Facades\Storage;

class IdenticonHelper
{
    private Identicon $identicon;

    public function __construct()
    {
        $this->identicon = new Identicon();
    }

    public static function url(string|int $name): string
    {
        return url("/api/profiles/{$name}/avatar");
    }

    public function create(string|int $filename, string $data): string
    {
        $image = $this->identicon->getImageData($data, 300);

        return $this->save($filename, $image);
    }

    private function save(string|int $name, string $imageData): string
    {
        $path = $name . ".png";

        Storage::disk("avatars")->put($path, $imageData);

        return Storage::disk("avatars")->url($path);
    }
}
