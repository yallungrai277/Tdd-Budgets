<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class UserProfilePhotoTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_profile_photo_can_be_uploaded()
    {
        Storage::fake();
        $user  = User::factory()->create();
        $fileName1 = $user->uploadProfilePhoto(UploadedFile::fake()->image('test1.png'));

        Storage::assertExists($user->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $fileName1);
        $this->assertSame($user->profile_photo, $fileName1);

        $fileName2 = $user->uploadProfilePhoto(UploadedFile::fake()->image('test1.png'));
        Storage::assertMissing($user->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $fileName1);
        $this->assertNotSame($user->profile_photo, $fileName1);

        Storage::assertExists($user->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $fileName2);
        $this->assertSame($user->profile_photo, $fileName2);
    }
}