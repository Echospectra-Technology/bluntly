<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'global', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $identifier = $this->getIdentifier($request);
        $rateLimitKey = $key . ':' . $identifier;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($rateLimitKey, $maxAttempts));

        return $response;
    }

    private function getIdentifier(Request $request): string
    {
        // Use cookie-based anonymous ID if available, fallback to IP
        $anonymousId = $request->cookie('bluntly_anonymous_id');
        return $anonymousId ?: $request->ip();
    }
}