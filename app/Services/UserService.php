<?php

namespace App\Services;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UserService
{
    private const DEFAULT_AVATAR = 'avatars/default.jpg';

    public function updateUser(object $request, User $user): User
    {
        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $this->handleAvatarUpload($request->file('avatar'), $user);
        }

        // Prepare and clean data
        $data = $this->prepareUpdateData($request->validated());

        // Update user attributes
        $user->fill($data);

        // Handle country relationship
        if ($request->country) {
            $this->setUserCountry($request->country, $user);
        }

        $user->save();

        // Load relationships if any are defined in $with property of User model
        return $user->fresh();
    }

    private function handleAvatarUpload(UploadedFile $avatar, User $user): void
    {
        // Delete old avatar if it exists and isn't the default
        $this->deleteOldAvatar($user->avatar_path);

        // Store new avatar
        $avatarPath = $avatar->store('avatars', 'public');
        $user->avatar_path = $avatarPath;
    }

    private function deleteOldAvatar(?string $avatarPath): void
    {
        if ($avatarPath &&
            $avatarPath !== self::DEFAULT_AVATAR &&
            Storage::disk('public')->exists($avatarPath)
        ) {
            Storage::disk('public')->delete($avatarPath);
        }
    }

    private function prepareUpdateData(array $data): array
    {
        return Arr::except($data, ['avatar', 'country']);
    }

    private function setUserCountry(string $countryName, User $user): void
    {
        $country = Country::where('name', $countryName)->first();
        $user->country_id = $country?->id;
    }
}
