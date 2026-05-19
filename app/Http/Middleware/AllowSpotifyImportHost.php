<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AllowSpotifyImportHost
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->header('X-Forwarded-Host') ?: $request->getHost();
        $allowed = config('services.spotify.import_api_host');
        $apiKey = config('services.spotify.import_api_key');

        // Allow if correct API key provided
        if ($apiKey && $request->header('X-Import-Api-Key') === $apiKey) {
            return $next($request);
        }

        // Allow if host matches allowed host (support subdomains)
        if ($allowed && (Str::endsWith($host, $allowed) || $host === $allowed)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
