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

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['authenticated' => false]);
        }

        try {
            $client = $gmailService->getClientForEmail($email);

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
