<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Strava\Actions\Auth\GenerateResetCodeAction;
use Tests\TestCase;

class GenerateResetCodeActionTest extends TestCase
{
    use RefreshDatabase;

    public function testGenerates6DigitCodeAndStoresHashedToken(): void
    {
        $action = new GenerateResetCodeAction();

        $code = $action->execute("user@gmail.com");

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $row = DB::table("password_reset_tokens")->where("email", "user@gmail.com")->first();

        $this->assertNotNull($row);
        $this->assertTrue(Hash::check($code, (string)$row->token));
        $this->assertNotNull($row->created_at);
    }

    public function testUpdateOrInsertOverwritesTokenForSameEmail(): void
    {
        $action = new GenerateResetCodeAction();

        $code1 = $action->execute("user@gmail.com");
        $row1 = DB::table("password_reset_tokens")->where("email", "user@gmail.com")->first();

        $this->assertNotNull($row1);
        $token1 = (string)$row1->token;

        $code2 = $action->execute("user@gmail.com");
        $row2 = DB::table("password_reset_tokens")->where("email", "user@gmail.com")->first();

        $this->assertNotNull($row2);
        $token2 = (string)$row2->token;

        $this->assertSame(1, DB::table("password_reset_tokens")->where("email", "user@gmail.com")->count());

        $this->assertTrue(Hash::check($code1, $token1));
        $this->assertTrue(Hash::check($code2, $token2));

        $this->assertNotSame($token1, $token2);
    }
}
