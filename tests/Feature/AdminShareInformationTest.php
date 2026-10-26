<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminShareInformationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp_number')->unique();
            $table->string('password');
            $table->string('role');
            $table->boolean('whatsapp_opt_in')->default(false);
            $table->timestamps();
        });
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function test_admin_can_send_information_to_selected_users_only(): void
    {
        $admin = $this->createUser('Admin', UserRole::ADMIN);
        $selected = $this->createUser('Dipilih', UserRole::USER);
        $notSelected = $this->createUser('Tidak dipilih', UserRole::USER);

        $response = $this->actingAs($admin)->post(route('admin.share-information'), [
            'recipient_mode' => 'selected',
            'title' => 'Pembaruan pesisir',
            'message' => 'Informasi terbaru untuk warga.',
            'user_ids' => [$selected->id],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $selected->id,
            'title' => 'Pembaruan pesisir',
            'message' => 'Informasi terbaru untuk warga.',
        ]);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $notSelected->id]);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $admin->id]);
    }

    public function test_send_to_all_includes_every_user_and_excludes_admins(): void
    {
        $admin = $this->createUser('Admin', UserRole::ADMIN);
        $this->createUser('Penerima A', UserRole::USER);
        $this->createUser('Penerima B', UserRole::USER);

        $response = $this->actingAs($admin)->post(route('admin.share-information'), [
            'recipient_mode' => 'all',
            'title' => 'Pemberitahuan',
            'message' => 'Pesan untuk semua user.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame(2, UserNotification::query()->count());
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $admin->id]);
    }

    public function test_admin_cannot_send_a_notification_to_another_admin(): void
    {
        $admin = $this->createUser('Admin', UserRole::ADMIN);
        $otherAdmin = $this->createUser('Admin lain', UserRole::ADMIN);

        $response = $this->actingAs($admin)->from(route('admin.dashboard'))->post(route('admin.share-information'), [
            'recipient_mode' => 'selected',
            'title' => 'Pemberitahuan',
            'message' => 'Pesan yang tidak boleh terkirim.',
            'user_ids' => [$otherAdmin->id],
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('user_ids');
        $this->assertSame(0, UserNotification::query()->count());
    }

    public function test_user_can_only_read_their_own_admin_notification(): void
    {
        $user = $this->createUser('Penerima', UserRole::USER);
        $otherUser = $this->createUser('Penerima lain', UserRole::USER);
        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'title' => 'Info',
            'message' => 'Isi informasi.',
        ]);
        $otherNotification = UserNotification::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Info lain',
            'message' => 'Informasi untuk akun lain.',
        ]);

        $this->actingAs($user)
            ->patch(route('alerts.messages.read', $notification))
            ->assertSessionHasNoErrors();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)
            ->patch(route('alerts.messages.read', $otherNotification))
            ->assertNotFound();
        $this->assertNull($otherNotification->fresh()->read_at);
    }

    private function createUser(string $name, UserRole $role): User
    {
        return User::query()->create([
            'name' => $name,
            'whatsapp_number' => '08'.str_pad((string) random_int(1, 999999999), 10, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role' => $role,
            'whatsapp_opt_in' => false,
        ]);
    }
}
