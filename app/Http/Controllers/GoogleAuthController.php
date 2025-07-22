<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Gmail\GmailService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\AuthenticatedEmail;
use Exception;

class GoogleAuthController extends Controller
{
    public function __construct(protected GmailService $gmailService) {}

    public function redirect(Request $request)
    {
        $email = $request->query('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failRedirect('Invalid or missing email.');
        }

        try {
            $client = $this->gmailService->getClient();
            Session::put('oauth_email', $email);

            return redirect()->away($client->createAuthUrl());
        } catch (Exception $e) {
            Log::error("OAuth redirect error: " . $e->getMessage());
            return $this->failRedirect('Unable to initiate Google authentication.');
        }
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return $this->failRedirect('Access denied. Please grant Gmail access to continue.');
        }

        try {
            $client = $this->gmailService->getClient();

            if (!$request->has('code')) {
                return $this->failRedirect('Missing authorization code from Google.');
            }

            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (isset($token['error'])) {
                Log::warning("Failed to get token from Google: " . json_encode($token));
                return $this->failRedirect('Failed to retrieve access token.');
            }

            $email = Session::pull('oauth_email');

            if (!$email) {
                return $this->failRedirect('Session expired. Please try again.');
            }

            AuthenticatedEmail::updateOrCreate(
                ['email' => $email],
                ['token' => json_encode($token)]
            );

            return redirect('/')->with('success', "$email authenticated successfully.");
        } catch (Exception $e) {
            Log::error("OAuth callback error: " . $e->getMessage());
            return $this->failRedirect('Unexpected error during authentication. Please try again.');
        }
    }

    protected function failRedirect(string $message)
    {
        return redirect('/')->with('error', $message);
    }
}
