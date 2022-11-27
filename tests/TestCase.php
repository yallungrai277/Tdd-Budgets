<?php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, InteractsWithExceptionHandling;

    protected function actAsAuthenticatedUser(User $user)
    {
        /** @var User */
        $this->actingAs($user);
        return $this;
    }

    protected function apiRequestHeaders($headers = []): array
    {
        return array_merge([
            'accept' => 'application/json'
        ], $headers);
    }

    protected function actAsAuthenticatedSanctumUser(User $user, array $abilites = ['*']): User
    {
        /** @var User */
        return Sanctum::actingAs(
            $user,
            $abilites
        );
    }
}