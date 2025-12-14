<?php

declare(strict_types=1);

namespace Strava\Actions\Avatars;

use Identicon\Generator\SvgGenerator;
use Identicon\Identicon;

class GetDefaultAvatarAction
{
    public function execute(int $userId): ?string
    {
        $identicon = new Identicon(new SvgGenerator());

        return $identicon->getImageData((string)$userId, 300);
    }
}
