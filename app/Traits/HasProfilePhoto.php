<?php

namespace App\Traits;

use App\Exceptions\FileUploadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasProfilePhoto
{
    public function uploadProfilePhoto(UploadedFile $file): string
    {
        if (!is_null($this->profile_photo)) {
            $this->removeProfilePhoto($this->profile_photo);
        }

        if (!Storage::putFileAs($this->getProfilePhotoFolder(), $file, $fileName = $file->hashName())) {
            throw new FileUploadException('Could not upload file', 500);
        }

        $this->profile_photo = $fileName;
        $this->save();
        return $fileName;
    }

    public function removeProfilePhoto(string $profilePhotoName): void
    {
        if (Storage::exists($path = $this->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $profilePhotoName)) {
            Storage::delete($path);
            $this->profile_photo = null;
            $this->save();
        }
    }

    public function profilePhotoUrl(): string
    {
        if (is_null($this->profile_photo)) return null;
        return Storage::url($this->getProfilePhotoFolder() . DIRECTORY_SEPARATOR . $this->profile_photo);
    }
}