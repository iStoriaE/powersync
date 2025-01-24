<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    /**
     * Generate a JWT token for user authentication.
     */
    public function getToken(Request $request): JsonResponse
    {
        $privateKey = File::get(storage_path(config('powersync.private_key')));

        $payload = [
            'sub' => $request->query('user_id', ''),
            'iss' => config('app.url'),
            'aud' => config('powersync.url'),
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $headers = [
            'alg' => 'RS256',
            'kid' => config('powersync.kid'),
        ];

        // Encode headers in the JWT string
        $jwt = JWT::encode($payload, $privateKey, 'RS256', config('powersync.kid'), $headers);

        return response()->json([
            'powersync_url' => config('powersync.url'),
            'token' => $jwt,
            'endPoint' => $payload['aud'],
            'expiresAt' => $payload['exp'],
        ]);
    }

    /**
     * Provide the public key for PowerSync to authenticate JWTs.
     */
    public function getKeys(): JsonResponse
    {
        $publicKey = File::get(storage_path(config('powersync.public_key')));
        $keyDetails = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));

        return response()->json([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => config('powersync.kid'),
                    'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyDetails['rsa']['n'])), '='),
                    'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyDetails['rsa']['e'])), '='),
                ],
            ],
        ]);
    }

}
