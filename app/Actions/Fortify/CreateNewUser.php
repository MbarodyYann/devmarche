<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\Countries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Crée le compte et son profil (pays et ville sont demandés dès l'inscription).
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['email'] = isset($input['email']) ? mb_strtolower(trim($input['email'])) : null;

        Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'country' => ['required', 'string', Rule::in(Countries::codes())],
            'city' => ['required', 'string', 'max:80'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => trim($input['name']),
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            $user->profile()->create([
                'country' => $input['country'],
                'city' => trim($input['city']),
            ]);

            return $user;
        });
    }
}
