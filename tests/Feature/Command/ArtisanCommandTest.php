<?php

namespace Tests\Feature\Command;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArtisanCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_app_command_is_successful()
    {
        $this->artisan('app:install')
            ->assertSuccessful();
    }
}