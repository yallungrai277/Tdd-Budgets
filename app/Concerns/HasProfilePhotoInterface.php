<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;

interface HasProfilePhotoInterface
{
    public function getProfilePhotoFolder(): string;

    public function profilePhotoUrl(): string;

    public function uploadProfilePhoto(UploadedFile $file): string;

    public function removeProfilePhoto(string $fileName): void;
}