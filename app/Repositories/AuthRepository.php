<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\PersonalAccessTokenResult;

use Modules\Admin\Entities\Staff;

class AuthRepository
{
    public function login(array $data): array
    {
        $user = $this->getUserByEmail($data['email']);
        if (!$user) {
            return [];
        }

        if (!$this->isValidPassword($user, $data)) {
            return [];
        }

        $tokenInstance = $this->createAuthToken($user);
        $data = $this->getAuthData($user, $tokenInstance);
        return $data;
    }

    public function register(array $data): array
    {
        $user = User::create($this->prepareDataForRegister($data));

        $staff = new Staff();
        $staff->email = $data["email"];
        $staff->first_name = $data["name"];
        $staff->last_name = $data["name"];
        $staff->user_id = $user->id;

        $staff->save();

        if (!$user) {
            throw new Exception("Sorry, User did not register, please try again", 500);
        }

        $tokenInstance = $this->createAuthToken($user);
        return $this->getAuthData($user, $tokenInstance);
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->where('active', 1)
            ->with('roles.permissions', 'permissions')
            ->first();
    }

    public function isValidPassword(User $user, array $data): bool
    {
        return Hash::check($data['password'], $user->password);
    }

    public function createAuthToken(User $user): PersonalAccessTokenResult
    {
        return $user->createToken('authToken');
    }

    public function getAuthData(User $user, PersonalAccessTokenResult $tokenInstance)
    {
        return [
            'user' => $user,
            'access_token' => $tokenInstance->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenInstance->token->expires_at)->toDateTimeString(),
        ];
    }


    public function prepareDataForRegister(array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [];
        }
        $user->password = Hash::make($newPassword);
        $user->save();
        return $user->toArray();
    }

    public function resetPasswordToDefault(User $user): array
    {
        $defaultPasswordPath = storage_path('app/passworddefault.txt');

        if (!file_exists($defaultPasswordPath)) {
            throw new Exception("File passworddefault.txt không tồn tại", 500);
        }
        $defaultPassword = trim(file_get_contents($defaultPasswordPath));
        $user->password = Hash::make($defaultPassword);
        $user->save();

        return $user->toArray();
    }

    public function getUserProfile()
    {
        $user = Auth::guard()->user()->load(
            'staff',
            'staff.department',
            'staff.user',
            'staff.user.roles',
            'staff.user.roles.permissions',
            'staff.user.permissions'
        );
        return $user;
    }
}
