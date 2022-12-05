<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setup(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_profile_page_is_displayed()
    {
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->user->refresh();

        $this->assertSame('Test User', $this->user->name);
        $this->assertSame('test@example.com', $this->user->email);
        $this->assertNull($this->user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $this->user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($this->user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account()
    {
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($this->user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($this->user->fresh());
    }

    public function test_user_can_upload_profile_photo()
    {
        $this->withExceptionHandling();
        Storage::fake();
        $response = $this
            ->actAsAuthenticatedUser($this->user)
            ->from('/profile')
            ->patch('/profile/photo', [
                'photo' => UploadedFile::fake()->image('image1.jpg')
            ]);

        $response
            ->assertRedirect('/profile')
            ->assertSessionDoesntHaveErrors();

        Storage::assertExists($this->user->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $this->user->profile_photo);
    }
}