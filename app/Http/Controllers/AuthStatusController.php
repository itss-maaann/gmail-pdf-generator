<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedEmail;
use Illuminate\Support\Facades\Log;
use App\Services\Gmail\GmailService;

class AuthStatusController extends Controller
{
    public function check(Request $request, GmailService $gmailService)
    {
        $email = $request->query('email');

        $record = AuthenticatedEmail::where('email', $email)->first();

        if (!$record || empty($record->token)) {
            return response()->json(['authenticated' => false]);
        }

        try {
            $client = $gmailService->getClient([
                'token' => json_decode($record->token, true),
                'on_refresh' => function ($newToken) use ($email) {
                    AuthenticatedEmail::where('email', $email)->update([
                        'token' => json_encode($newToken)
                    ]);
                }
            ]);

            if ($client->isAccessTokenExpired() && !$client->getRefreshToken()) {
                AuthenticatedEmail::where('email', $email)->update(['token' => null]);
                return response()->json(['authenticated' => false]);
            }

            return response()->json(['authenticated' => true]);
        } catch (\Throwable $e) {
            Log::error("Auth check failed for $email: " . $e->getMessage());
            AuthenticatedEmail::where('email', $email)->update(['token' => null]);
            return response()->json(['authenticated' => false]);
        }
    }
}
