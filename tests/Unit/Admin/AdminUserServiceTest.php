<?php

namespace Tests\Unit\Admin;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\Users\AdminUserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    public function test_create_cleans_avatar_when_role_sync_fails(): void
    {
        Storage::fake('public');
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $adminRole = new Role(['slug' => 'admin']);
        $adminRole->id = 1;
        $roles = $this->createMock(RoleRepositoryInterface::class);
        $roles->expects($this->once())->method('lockBySlug')->with('admin')->willReturn($adminRole);

        $user = new User;
        $user->id = 10;
        $users = $this->createMock(UserRepositoryInterface::class);
        $users->expects($this->once())->method('countAdmins')->willReturn(0);
        $users->expects($this->once())->method('create')->willReturn($user);
        $users->expects($this->once())
            ->method('syncRoles')
            ->willThrowException(new RuntimeException('Role sync failed'));

        $service = new AdminUserService($roles, $users);

        try {
            $service->create([
                'name' => 'Admin',
                'email' => 'admin@example.test',
                'password' => 'secret123',
                'role_id' => 1,
            ], UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg'));
            $this->fail('Expected role sync failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('Role sync failed', $e->getMessage());
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
