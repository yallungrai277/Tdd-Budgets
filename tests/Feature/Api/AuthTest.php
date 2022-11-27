<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return [
            'name' => 'Test name',
            'email' => 'test@gmail.com',
            'password' => Hash::make('Password123#')
        ];
    }

    public function test_a_valid_email_is_required_to_register_user()
    {
        $response1 = $this->post('api/auth/register', array_merge($this->data(), [
            'email' => ''
        ]), $this->apiRequestHeaders());

        $response2 = $this->post('api/auth/register', array_merge($this->data(), [
            'email' => 'asfasd'
        ]), $this->apiRequestHeaders());

        $response1->assertUnprocessable();
        $response1->assertJsonValidationErrors([
            'email'
        ]);

        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrors([
            'email'
        ]);
    }

    public function test_a_name_is_required_to_register_user()
    {
        $response = $this->post('api/auth/register', array_merge($this->data(), [
            'name' => ''
        ]), $this->apiRequestHeaders());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('name');
    }

    public function test_a_user_can_be_registered(): void
    {
        $response = $this->post('api/auth/register', $this->data(), $this->apiRequestHeaders());

        $this->assertDatabaseHas('users', [
            'email' => $this->data()['email']
        ]);

        $response->assertOk();
        $response->assertJson([
            'name' => $this->data()['name'],
            'email' => $this->data()['email']
        ]);

        $response->assertJsonMissing([
            'created_at'
        ]);
        $response->assertJsonStructure([
            'id',
            'name',
            'email'
        ]);
    }

    public function test_a_valid_email_is_required_to_login(): void
    {
        $response1 = $this->post('api/auth/login', array_merge($this->data(), [
            'email' => ''
        ]), $this->apiRequestHeaders());

        $response1->assertUnprocessable();
        $response1->assertJsonValidationErrorFor('email');

        $response2 = $this->post('api/auth/login', array_merge($this->data(), [
            'email' => 'test@123.com'
        ]), $this->apiRequestHeaders());

        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrorFor('email');
    }

    public function test_a_password_is_required_to_login(): void
    {
        $response = $this->post('api/auth/login', array_merge($this->data(), [
            'password' => ''
        ]), $this->apiRequestHeaders());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('password');
    }

    public function test_a_user_response_with_auth_token_is_received_after_logging_in(): void
    {
        $user = User::factory()->create($this->data());
        $this->assertModelExists($user);

        $response = $this->post('api/auth/login', array_merge($this->data(), [
            'password' => "Password123#"
        ]), $this->apiRequestHeaders());

        $response->assertOk();
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'auth_token'
        ]);
    }

    public function test_invalid_bearer_token_cannot_get_user_info()
    {
        $response = $this->get('/api/auth/me', $this->apiRequestHeaders());
        $response->assertUnauthorized();
    }

    public function test_valid_bearer_token_can_get_user_info()
    {
        $user = User::factory()->create();
        $this->actAsAuthenticatedSanctumUser($user);
        $user->auth_token = null;

        $response = $this->get('/api/auth/me');
        $response->assertOk();

        $response->assertExactJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}