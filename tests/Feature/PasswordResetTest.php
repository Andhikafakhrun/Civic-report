<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_sends_notification_for_existing_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'staff@example.com']);

        $response = $this->post('/forgot-password', ['email' => 'staff@example.com']);

        $response->assertSessionHas('info');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_request_shows_same_message_for_nonexistent_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'tidak-ada@example.com']);

        $response->assertSessionHas('info');
        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-lama')]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertRedirect('/login');

        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-lama')]);

        $response = $this->post('/reset-password', [
            'token' => 'token-yang-salah',
            'email' => $user->email,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }
}