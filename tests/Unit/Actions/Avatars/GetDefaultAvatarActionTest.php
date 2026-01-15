<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Avatars;

use Strava\Actions\Avatars\GetDefaultAvatarAction;
use Tests\TestCase;

class GetDefaultAvatarActionTest extends TestCase
{
    public function testReturnsSvgIdenticonForUserId(): void
    {
        $action = new GetDefaultAvatarAction();

        $svg = $action->execute(123);

        $this->assertIsString($svg);
        $this->assertStringContainsString("<svg", $svg);
        $this->assertStringContainsString("</svg>", $svg);
    }

    public function testIdenticonIsDeterministicForSameUserId(): void
    {
        $action = new GetDefaultAvatarAction();

        $svg1 = $action->execute(42);
        $svg2 = $action->execute(42);

        $this->assertSame($svg1, $svg2);
    }

    public function testIdenticonDiffersForDifferentUserIds(): void
    {
        $action = new GetDefaultAvatarAction();

        $svg1 = $action->execute(1);
        $svg2 = $action->execute(2);

        $this->assertNotSame($svg1, $svg2);
    }
}
