<?php

namespace App\Services\Gmail;

use Closure;
use App\Models\AuthenticatedEmail;

class TokenRefresher
{
    public function forEmail(string $email): Closure
    {
        return function (array $newToken) use ($email) {
            AuthenticatedEmail::where('email', $email)->update([
                'token' => json_encode($newToken),
            ]);
        };
    }
}
