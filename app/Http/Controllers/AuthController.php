<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Generate a JWT token for user authentication.
     */
    public function getToken(Request $request): JsonResponse
    {
        $privateKey = json_decode(base64_decode(config('powersync.private_key')));

        $payload = [
            'sub' => $request->query('user_id', ''),
            'iss' => config('app.url'),
            'aud' => config('powersync.url'),
            'iat' => time(),
            'exp' => time() + 36000,
        ];

        $headers = [
            'alg' => 'RS256',
            'kid' => config('powersync.kid'),
        ];

        // Encode headers in the JWT string
        $jwt = JWT::encode($payload, $privateKey, 'RS256', config('powersync.kid'), $headers);

        Log::info('getToken');

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
        $publicKey = json_decode(base64_decode(config('powersync.public_key')));

        Log::info('getKeys');

        return response()->json([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => $publicKey->kid,
                    'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($publicKey->n)), '='),
                    'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($publicKey->e)), '='),
                ],
            ],
        ]);
    }

}
