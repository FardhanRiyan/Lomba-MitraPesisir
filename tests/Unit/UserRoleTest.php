<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_hanya_role_admin_dan_user_yang_tersedia(): void
    {
        $this->assertSame(['ADMIN', 'USER'], UserRole::values());
    }
}
